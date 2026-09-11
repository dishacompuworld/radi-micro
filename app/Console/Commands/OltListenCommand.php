<?php

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class OltListenCommand extends Command
{
    protected $signature = 'olt:listen';

    protected $description = 'Listen for OLT UDP logs on port 514';

    public function handle(): int
    {
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

        if ($socket === false) {
            $this->error('Failed to create UDP socket: ' . socket_strerror(socket_last_error()));
            return 1;
        }

        if (@socket_bind($socket, '0.0.0.0', 514) === false) {
            $this->error('Failed to bind UDP port 514: ' . socket_strerror(socket_last_error($socket)));
            socket_close($socket);
            return 1;
        }

        $logFile = storage_path('logs/olt-udp.log');
        $this->info('OLT UDP listener running on 0.0.0.0:514');

        while (true) {
            $buffer = '';
            $sender = '';
            $port = 0;

            $bytes = @socket_recvfrom($socket, $buffer, 65535, 0, $sender, $port);

            if ($bytes === false) {
                continue;
            }

            $payload = trim($buffer);
            if ($payload === '') {
                continue;
            }

            $parsed = $this->parseOltLog($payload);

            $line = sprintf(
                "[%s] %s:%s %s\n",
                now()->format('Y-m-d H:i:s'),
                $sender,
                $port,
                $payload
            );

            @file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
            $this->trimLogFile($logFile, $this->getLogLimit());

            if ($parsed['matched']) {
                $this->info(sprintf(
                    'source=%s device=%s serial=%s ont=%s pon=%s alarm=%s count=%s',
                    $sender,
                    $parsed['device'],
                    $parsed['serial'],
                    $parsed['ont'],
                    $parsed['pon'],
                    $parsed['alarm'],
                    $parsed['count']
                ));
            } else {
                $this->warn('Unparsed OLT log: ' . $payload);
            }
        }
    }

    protected function getLogLimit(): int
    {
        $limit = Setting::where('key', 'olt_log_limit')->value('value');
        $limit = is_numeric($limit) ? (int) $limit : 5000;

        return max(1, $limit);
    }

    protected function trimLogFile(string $logFile, int $maxLines): void
    {
        if (! file_exists($logFile)) {
            return;
        }

        $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false || count($lines) <= $maxLines) {
            return;
        }

        $trimmed = array_slice($lines, -$maxLines);
        $content = implode(PHP_EOL, $trimmed);
        if ($content !== '') {
            $content .= PHP_EOL;
        }

        @file_put_contents($logFile, $content, LOCK_EX);
    }

    protected function parseOltLog(string $payload): array
    {
        $patterns = [
            '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\s+(?P<alarm>.+?)\s+(?P<count>\d+)\s+reported\.?\s*$/i',
            '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+(?:\[[^\]]+\]\s*)?PON\s+(?P<pon>\d+)\s+ONU\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+(?P<alarm>.+?)\s*$/i',
            '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+Signals\s+were\s+lost\s+for\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\.?\s*$/i',
            '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\s+last\s+down\s+causes?\s*:\s*(?P<alarm>.+?)\.?\s*$/i',
            '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\s+was\s+disconnected\.?\s*$/i',
            '/^\s*<\d+>\s*(?P<timestamp>\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(?P<device>\S+)\s+(?P<event>\d+)\s+Warn\s+of\s+dying-gasp\s+for\s+ONU\s+\(SN\s+(?P<serial>[A-Z0-9-]+)\)\s+(?P<ont>\d+)\s+in\s+PON\s+(?P<pon>\d+)\.?\s*$/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $payload, $matches)) {
                $count = isset($matches['count']) ? (int) $matches['count'] : 1;
                $alarm = rtrim(trim($matches['alarm'] ?? ''), '. ');

                if ($alarm === '') {
                    if (stripos($payload, 'dying-gasp') !== false) {
                        $alarm = 'Dying-gasp';
                    } elseif (stripos($payload, 'Signals were lost') !== false) {
                        $alarm = 'Signals were lost';
                    } elseif (stripos($payload, 'was disconnected') !== false) {
                        $alarm = 'ONU was disconnected';
                    } elseif (stripos($payload, 'last down causes') !== false) {
                        $alarm = 'Last down causes';
                    }
                }

                return [
                    'matched' => true,
                    'timestamp' => $matches['timestamp'],
                    'device' => $matches['device'],
                    'event' => $matches['event'],
                    'serial' => $matches['serial'],
                    'ont' => $matches['ont'],
                    'pon' => $matches['pon'],
                    'alarm' => $alarm,
                    'count' => $count,
                    'raw' => $payload,
                ];
            }
        }

        return [
            'matched' => false,
            'raw' => $payload,
        ];
    }
}
