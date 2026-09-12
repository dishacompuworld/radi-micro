<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('logs:clear', function() {
    exec('echo "" > ' . storage_path('logs/laravel.log'));
    $this->info('Logs have been cleared');
})->describe('Clear log files');

Artisan::command('mikrotik:udp-logs', function () {
    $port = (int) (\App\Models\Setting::where('key', 'microtik_udp_port')->value('value') ?: 515);
    $logLimit = (int) (\App\Models\Setting::where('key', 'microtik_log_limit')->value('value') ?: 1000);
    if ($port < 1 || $port > 65535) {
        $port = 515;
    }
    if ($logLimit < 1 || $logLimit > 100000) {
        $logLimit = 1000;
    }

    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($socket === false) {
        $this->error('Unable to create UDP socket: ' . socket_strerror(socket_last_error()));
        return 1;
    }

    socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
    socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 1, 'usec' => 0]);

    if (!@socket_bind($socket, '0.0.0.0', $port)) {
        $this->error('Unable to bind UDP port ' . $port . ': ' . socket_strerror(socket_last_error($socket)));
        socket_close($socket);
        return 1;
    }

    $this->info('MikroTik UDP log listener running on 0.0.0.0:' . $port);

    $logDir = storage_path('app/mikrotik-udp-logs');
    if (!is_dir($logDir)) {
        mkdir($logDir, 0775, true);
    }

    $logFile = $logDir . DIRECTORY_SEPARATOR . 'all-servers.log';

    $pidFile = storage_path('app/mikrotik_udp_listener.pid');
    file_put_contents($pidFile, getmypid(), LOCK_EX);

    while (true) {
        $buffer = '';
        $sender = '';
        $senderPort = 0;

        $bytes = @socket_recvfrom($socket, $buffer, 65535, 0, $sender, $senderPort);
        if ($bytes === false) {
            continue;
        }

        $message = trim((string) $buffer);
        if ($message === '') {
            continue;
        }

        $record = json_encode([
            'time' => now()->toDateTimeString(),
            'source' => $sender . ':' . $senderPort,
            'message' => preg_replace('/^<\d+>\s*/', '', $message),
            'raw' => $message,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $lines = is_file($logFile)
            ? file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)
            : [];

        $lines[] = $record;
        if (count($lines) > $logLimit) {
            $lines = array_slice($lines, -$logLimit);
        }

        file_put_contents($logFile, implode(PHP_EOL, $lines) . PHP_EOL, LOCK_EX);
    }
})->describe('Run a background MikroTik UDP syslog listener on the configured port');
