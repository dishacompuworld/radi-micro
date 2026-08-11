<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    public function index()
    {
        $title = 'Settings';
        $appName = Setting::where('key', 'app_name')->value('value') ?: config('app.name');
        $oltIp = Setting::where('key', 'olt_ip')->value('value') ?: '';
        $oltTelnetUsername = Setting::where('key', 'olt_telnet_username')->value('value') ?: '';
        $snmpEnabled = Setting::where('key', 'olt_snmp_enabled')->value('value') ?: '0';

        return view('settings.index', compact('title', 'appName', 'oltIp', 'oltTelnetUsername', 'snmpEnabled'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'olt_ip' => 'nullable|string|max:255',
            'olt_telnet_username' => 'nullable|string|max:255',
            'olt_telnet_password' => 'nullable|string|max:255',
        ]);

        Setting::updateOrCreate(
            ['key' => 'app_name'],
            ['value' => $request->input('app_name')]
        );

        Setting::updateOrCreate(
            ['key' => 'olt_ip'],
            ['value' => $request->input('olt_ip')]
        );

        Setting::updateOrCreate(
            ['key' => 'olt_telnet_username'],
            ['value' => trim($request->input('olt_telnet_username'))]
        );

        if ($request->filled('olt_telnet_password')) {
            Setting::updateOrCreate(
                ['key' => 'olt_telnet_password'],
                ['value' => Crypt::encryptString(trim($request->input('olt_telnet_password')))]
            );
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    public function toggleSnmp(Request $request)
    {
        $request->validate([
            'olt_snmp_enabled' => 'required|boolean',
        ]);

        $enabled = $request->boolean('olt_snmp_enabled');

        $oltIp = trim(Setting::where('key', 'olt_ip')->value('value') ?? '');
        $oltUsername = trim(Setting::where('key', 'olt_telnet_username')->value('value') ?? '');
        $encryptedPassword = Setting::where('key', 'olt_telnet_password')->value('value');

        if (! $oltIp || ! $oltUsername || ! $encryptedPassword) {
            return response()->json([
                'type' => 'error',
                'message' => 'OLT IP and Telnet credentials are required before toggling SNMP.',
            ], 422);
        }

        try {
            $oltPassword = trim(Crypt::decryptString($encryptedPassword));
        } catch (DecryptException $e) {
            Log::error('Failed to decrypt OLT telnet password: ' . $e->getMessage());
            return response()->json([
                'type' => 'error',
                'message' => 'Unable to read saved telnet password.',
            ], 500);
        }

        $snmpCommand = $enabled ? 'snmp-server' : 'no snmp-server';

        try {
            $telnet = fsockopen($oltIp, 23, $errno, $errstr, 10);
            if (! $telnet) {
                Log::error("OLT telnet connection failed: {$errno} - {$errstr}");
                return response()->json(['type' => 'error', 'message' => 'Unable to connect to OLT via telnet.'], 500);
            }

            stream_set_blocking($telnet, true);
            stream_set_timeout($telnet, 10);

            $this->sendTelnetNegotiation($telnet);
            $response = $this->readTelnetResponse($telnet, ['login:', 'username:', 'user:'], 10);
            Log::debug('Telnet initial response raw: ' . bin2hex($response));
            Log::debug('Telnet initial response: ' . trim($response));

            if (stripos($response, 'user:') !== false || stripos($response, 'username:') !== false || stripos($response, 'login:') !== false) {
                Log::debug('Telnet login prompt detected, sending username: ' . $oltUsername);
                fwrite($telnet, trim($oltUsername) . "\r\n");
                fflush($telnet);
                $response .= $this->readTelnetResponse($telnet, ['password:', 'login:', 'username:', 'user:'], 10);
                Log::debug('Telnet after username response raw: ' . bin2hex($response));
                Log::debug('Telnet after username response: ' . trim($response));
            }

            if (stripos($response, 'password:') !== false) {
                Log::debug('Telnet password prompt detected, sending password length: ' . strlen($oltPassword));
                fwrite($telnet, $oltPassword . "\r\n");
                fflush($telnet);
                $response .= $this->readTelnetResponse($telnet, ['>', '#', ']', 'login:', 'username:', 'user:', 'invalid', 'error'], 10);
                Log::debug('Telnet after password response raw: ' . bin2hex($response));
                Log::debug('Telnet after password response: ' . trim($response));
            }

            if ($this->isTelnetAuthFailure($response)) {
                fclose($telnet);
                Log::error('OLT telnet authentication failed: ' . trim($response));

                activity()->causedBy(auth()->user())->useLog('OLT SNMP')->log('SNMP toggle failed: authentication failed on OLT.');

                return response()->json([
                    'type' => 'error',
                    'message' => 'OLT telnet authentication failed. Please verify the username/password for the device.',
                    'output' => $response,
                ], 401);
            }

            if (! $this->hasTelnetPrompt($response, ['>', '#', ']'], false)) {
                fclose($telnet);
                Log::error('OLT telnet did not reach an authenticated command prompt: ' . trim($response));

                activity()->causedBy(auth()->user())->useLog('OLT SNMP')->log('OLT telnet did not reach an authenticated command prompt: ' . trim($response));

                return response()->json([
                    'type' => 'error',
                    'message' => 'OLT telnet session did not reach a usable prompt after login.',
                    'output' => $response,
                ], 500);
            }

            $response .= $this->executeTelnetCommand($telnet, 'enable', ['>', '#', ']', 'login:', 'username:', 'user:', 'invalid', 'error'], 10);
            $response .= $this->executeTelnetCommand($telnet, 'configure', ['>', '#', ']', 'login:', 'username:', 'user:', 'invalid', 'error'], 10);
            $response .= $this->executeTelnetCommand($telnet, $snmpCommand, ['>', '#', ']', 'login:', 'username:', 'user:', 'invalid', 'error'], 10);

            fwrite($telnet, "exit\r\n");
            fflush($telnet);
            $response .= $this->readTelnetResponse($telnet);
            fclose($telnet);

            Log::debug('Telnet command response: ' . trim($response));

            if ($this->hasTelnetError($response)) {
                return response()->json([
                    'type' => 'error',
                    'message' => 'OLT responded with an error when changing SNMP state.',
                    'output' => $response,
                ], 500);
            }

            Setting::updateOrCreate(
                ['key' => 'olt_snmp_enabled'],
                ['value' => $enabled ? '1' : '0']
            );

            $msg = $enabled
                ? 'SNMP enabled successfully on OLT.'
                : 'SNMP disabled successfully on OLT.';

            activity()->causedBy(auth()->user())->useLog('OLT SNMP')->log($msg);

            return response()->json([
                'type' => 'success',
                'message' => $enabled ? 'SNMP enabled successfully on OLT.' : 'SNMP disabled successfully on OLT.',
                'output' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('SNMP telnet toggle failed: '.$e->getMessage());
            activity()->causedBy(auth()->user())->useLog('OLT SNMP')->log('SNMP toggle failed: ' . $e->getMessage());
            return response()->json(['type' => 'error', 'message' => 'Failed to update SNMP service on OLT.'], 500);
        }
    }

    private function sendTelnetNegotiation($telnet): void
    {
        $negotiation = "\xff\xfb\x01\xff\xfb\x03\xff\xfd\x03";
        fwrite($telnet, $negotiation);
        fflush($telnet);
    }

    private function readTelnetResponse($telnet, array $stopPrompts = [], int $timeout = 5): string
    {
        $response = '';
        $start = time();

        while (! feof($telnet) && (time() - $start) < $timeout) {
            $chunk = @fread($telnet, 512);
            if ($chunk === false || $chunk === '') {
                usleep(100000);
                continue;
            }

            $response .= $chunk;
            if (! empty($stopPrompts) && $this->hasTelnetPrompt($response, $stopPrompts, false)) {
                break;
            }
        }

        $converted = @iconv('UTF-8', 'UTF-8//IGNORE', $response);
        if ($converted === false) {
            $converted = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/', '', $response);
        }

        return $converted;
    }

    private function hasTelnetPrompt(string $output, array $prompts = [], bool $includeDefault = true): bool
    {
        $defaultPrompts = ['login:', 'username:', 'password:', '>', '#', ']'];
        $prompts = $includeDefault ? array_merge($defaultPrompts, $prompts) : $prompts;

        $cleanOutput = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]+/', '', $output);
        $cleanLower = strtolower($cleanOutput);

        foreach ($prompts as $prompt) {
            $promptLower = strtolower($prompt);

            if (in_array($promptLower, ['login:', 'username:', 'user:', 'password:'], true)) {
                $pattern = '/(?:^|\r?\n)\s*' . preg_quote($prompt, '/') . '\s*$/im';
                if (preg_match($pattern, $cleanOutput)) {
                    return true;
                }

                if (preg_match('/(?:^|\r?\n)\s*User\s*:\s*$/im', $cleanOutput) && $promptLower === 'user:') {
                    return true;
                }

                continue;
            }

            if (strpos($cleanLower, $promptLower) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isTelnetAuthFailure(string $output): bool
    {
        $failures = [
            'login invalid',
            'login incorrect',
            'authentication failed',
            'invalid username or password',
            'password error',
            'incorrect password',
        ];

        $normalized = strtolower($output);
        foreach ($failures as $failure) {
            if (strpos($normalized, $failure) !== false) {
                return true;
            }
        }

        return false;
    }

    private function executeTelnetCommand($telnet, string $command, array $stopPrompts = [], int $timeout = 5): string
    {
        fwrite($telnet, $command . "\r\n");
        fflush($telnet);

        $response = $this->readTelnetResponse($telnet, $stopPrompts, $timeout);
        Log::debug('Telnet execute command [' . $command . '] response: ' . trim($response));

        return $response;
    }

    private function hasBadCommand(string $output): bool
    {
        $failures = ['bad command', 'unknown command', 'invalid command'];
        $normalized = strtolower($output);

        foreach ($failures as $failure) {
            if (strpos($normalized, $failure) !== false) {
                return true;
            }
        }

        return false;
    }

    private function getSnmpCommandCandidates(bool $enabled): array
    {
        return $enabled ? [
            'snmp-agent',
            'snmp-agent enable',
            'enable snmp',
            'snmp enable',
            'snmp-agent enable service',
        ] : [
            'undo snmp-agent',
            'no snmp-agent',
            'disable snmp',
            'disable snmp-agent',
            'snmp-agent disable',
        ];
    }

    private function executeFirstValidCommand($telnet, array $commands, array $stopPrompts, int $timeout): ?string
    {
        $lastResponse = null;

        foreach ($commands as $command) {
            $lastResponse = $this->executeTelnetCommand($telnet, $command, $stopPrompts, $timeout);

            if (! $this->hasBadCommand($lastResponse) && ! $this->hasTelnetError($lastResponse)) {
                return $lastResponse;
            }

            Log::debug('SNMP candidate command failed: ' . $command . ' - ' . trim($lastResponse));
        }

        return $lastResponse;
    }

    private function hasTelnetError(string $output): bool
    {
        $errors = [
            'invalid input',
            'error',
            'unknown command',
            'permission denied',
            'failed',
            'not found',
            'authentication failed',
            'login incorrect',
        ];

        $normalized = strtolower($output);
        foreach ($errors as $error) {
            if (strpos($normalized, $error) !== false) {
                return true;
            }
        }

        return false;
    }
}
