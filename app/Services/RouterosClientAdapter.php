<?php

namespace App\Services;

use RouterOS\Client;
use RouterOS\Config;
use RouterOS\Query;
use Illuminate\Support\Facades\Log;

class RouterosClientAdapter
{
    public bool $debug = false;
    private ?Client $client = null;
    private ?string $lastCommand = null;
    private array $lastParams = [];

    public function connect(string $ip, string $user, string $pass): bool
    {
        $config = (new Config())
            ->set('host', $ip)
            ->set('user', $user)
            ->set('pass', $pass)
            ->set('port', 8728)
            ->set('timeout', 30)
            ->set('socket_timeout', 60)
            ->set('ssl', false);

        $this->client = new Client($config);
        return true;
    }

    public function comm(string $com, array $arr = [])
    {
        if (!$this->client) {
            return [];
        }

        $q = $this->client->query($com);
        foreach ($arr as $k => $v) {
            $key = ltrim((string)$k, "=?");
            if (method_exists($q, 'equal')) {
                $val = $v === true ? '' : $v;
                $q->equal($key, $val);
            }
        }

        $attempts = 0;
        $maxAttempts = 3;
        while ($attempts < $maxAttempts) {
            try {
                $result = $q->read();
                return $result;
            } catch (\RouterOS\Exceptions\StreamException $e) {
                $attempts++;
                Log::warning("RouterOS StreamException in comm (attempt {$attempts}): " . $e->getMessage());
                if ($attempts >= $maxAttempts) {
                    Log::error('RouterOS StreamException in comm: ' . $e->getMessage());
                    return [];
                }
                sleep(1);
            } catch (\Throwable $e) {
                Log::error('RouterOS Exception in comm: ' . $e->getMessage());
                return [];
            }
        }
        return [];
    }

    public function write(string $command, $param2 = true)
    {
        if ($command) {
            if ($command[0] === '=') {
                $parts = explode('=', ltrim($command, '='), 2);
                if (count($parts) === 2) {
                    $this->lastParams[$parts[0]] = $parts[1];
                }
            } else {
                $this->lastCommand = $command;
            }
            return true;
        }
        return false;
    }

    public function read($parse = true)
    {
        if (!$this->client || !$this->lastCommand) {
            return [];
        }

        $q = $this->client->query($this->lastCommand);
        foreach ($this->lastParams as $k => $v) {
            if (method_exists($q, 'equal')) {
                $q->equal($k, $v);
            }
        }

        $attempts = 0;
        $maxAttempts = 3;
        $result = [];
        while ($attempts < $maxAttempts) {
            try {
                $result = $q->read();
                break;
            } catch (\RouterOS\Exceptions\StreamException $e) {
                $attempts++;
                Log::warning("RouterOS StreamException in read (attempt {$attempts}): " . $e->getMessage());
                if ($attempts >= $maxAttempts) {
                    Log::error('RouterOS StreamException in read: ' . $e->getMessage());
                    $result = [];
                    break;
                }
                sleep(1);
            } catch (\Throwable $e) {
                Log::error('RouterOS Exception in read: ' . $e->getMessage());
                $result = [];
                break;
            }
        }

        $this->lastCommand = null;
        $this->lastParams = [];

        return $result;
    }

    public function parseResponse($response)
    {
        return $response;
    }

    public function disconnect()
    {
        if ($this->client && method_exists($this->client, 'disconnect')) {
            try { $this->client->disconnect(); } catch (\Throwable $e) { /* ignore */ }
        }
        $this->client = null;
    }
}
