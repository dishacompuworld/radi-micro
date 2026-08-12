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
    private array $encryptedSettingKeys = [
        'olt_telnet_password',
        'radius_password',
        'whats_app_token',
        'mactoken',
        'mail_password',
    ];

    public function index()
    {
        $title = 'Settings';

        $settings = [
            'app_name' => $this->getSettingValue('app_name', config('app.name', 'Radi Micro')),
            'olt_ip' => $this->getSettingValue('olt_ip'),
            'olt_telnet_username' => $this->getSettingValue('olt_telnet_username'),
            'olt_telnet_password' => $this->getSettingValue('olt_telnet_password'),
            'snmp_oid_names' => $this->getSettingValue('snmp_oid_names'),
            'snmp_oid_powers' => $this->getSettingValue('snmp_oid_powers'),
            'snmp_oid_powers_tr' => $this->getSettingValue('snmp_oid_powers_tr'),
            'min_ont_power' => $this->getSettingValue('min_ont_power'),
            'snmp_oid_uptime' => $this->getSettingValue('snmp_oid_uptime'),
            'snmp_oid_brand' => $this->getSettingValue('snmp_oid_brand'),
            'snmp_oid_temp' => $this->getSettingValue('snmp_oid_temp'),
            'snmp_oid_eth' => $this->getSettingValue('snmp_oid_eth'),
            'snmp_oid_model' => $this->getSettingValue('snmp_oid_model'),
            'snmp_oid_dist' => $this->getSettingValue('snmp_oid_dist'),
            'snmp_oid_regist' => $this->getSettingValue('snmp_oid_regist'),
            'snmp_oid_status' => $this->getSettingValue('snmp_oid_status'),
            'olt_snmp_enabled' => Setting::where('key', 'olt_snmp_enabled')->value('value') ?: '0',
            'radius_login' => $this->getSettingValue('radius_login'),
            'radius_password' => $this->getSettingValue('radius_password'),
            'prtg_url' => $this->getSettingValue('prtg_url'),
            'prtg_api_key' => $this->getSettingValue('prtg_api_key'),
            'prtg_all_traffic_graph_id' => $this->getSettingValue('prtg_all_traffic_graph_id'),
            'prtg_main_prob_id' => $this->getSettingValue('prtg_main_prob_id'),
            'prtg_mseb' => $this->getSettingValue('prtg_mseb'),
            'prtg_temp' => $this->getSettingValue('prtg_temp'),
            'whats_app_url' => $this->getSettingValue('whats_app_url'),
            'whats_app_token' => $this->getSettingValue('whats_app_token'),
            'whatsapp_instance' => $this->getSettingValue('whatsapp_instance'),
            'whatsapp_number' => $this->getSettingValue('whatsapp_number'),
            'macurl' => $this->getSettingValue('macurl'),
            'mactoken' => $this->getSettingValue('mactoken'),
            'microtik_interface1' => $this->getSettingValue('microtik_interface1', 'sfp-sfpplus1'),
            'microtik_interface2' => $this->getSettingValue('microtik_interface2', 'ether10'),
            'mail_mailer' => $this->getSettingValue('mail_mailer', config('mail.default', 'log')),
            'mail_host' => $this->getSettingValue('mail_host', config('mail.mailers.smtp.host', '127.0.0.1')),
            'mail_port' => $this->getSettingValue('mail_port', (string) config('mail.mailers.smtp.port', 2525)),
            'mail_username' => $this->getSettingValue('mail_username', config('mail.mailers.smtp.username', '')),
            'mail_password' => $this->getSettingValue('mail_password', config('mail.mailers.smtp.password', '')),
            'mail_encryption' => $this->getSettingValue('mail_encryption', config('mail.mailers.smtp.encryption', 'tls')),
            'mail_from_address' => $this->getSettingValue('mail_from_address', config('mail.from.address', 'hello@example.com')),
            'mail_from_name' => $this->getSettingValue('mail_from_name', config('mail.from.name', config('app.name', 'Radi Micro'))),
        ];

        return view('settings.index', compact('title', 'settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name' => 'nullable|string|max:255',
            'olt_ip' => 'nullable|string|max:255',
            'olt_telnet_username' => 'nullable|string|max:255',
            'olt_telnet_password' => 'nullable|string|max:255',
            'snmp_oid_names' => 'nullable|string|max:255',
            'snmp_oid_powers' => 'nullable|string|max:255',
            'snmp_oid_powers_tr' => 'nullable|string|max:255',
            'min_ont_power' => 'nullable|string|max:50',
            'snmp_oid_uptime' => 'nullable|string|max:255',
            'snmp_oid_brand' => 'nullable|string|max:255',
            'snmp_oid_temp' => 'nullable|string|max:255',
            'snmp_oid_eth' => 'nullable|string|max:255',
            'snmp_oid_model' => 'nullable|string|max:255',
            'snmp_oid_dist' => 'nullable|string|max:255',
            'snmp_oid_regist' => 'nullable|string|max:255',
            'snmp_oid_status' => 'nullable|string|max:255',
            'radius_login' => 'nullable|string|max:255',
            'radius_password' => 'nullable|string|max:255',
            'prtg_url' => 'nullable|url|max:255',
            'prtg_api_key' => 'nullable|string|max:255',
            'prtg_all_traffic_graph_id' => 'nullable|string|max:255',
            'prtg_main_prob_id' => 'nullable|string|max:255',
            'prtg_mseb' => 'nullable|string|max:255',
            'prtg_temp' => 'nullable|string|max:255',
            'whats_app_url' => 'nullable|url|max:255',
            'whats_app_token' => 'nullable|string|max:255',
            'whatsapp_instance' => 'nullable|string|max:255',
            'whatsapp_number' => 'nullable|string|max:255',
            'macurl' => 'nullable|url|max:255',
            'mactoken' => 'nullable|string|max:255',
            'microtik_interface1' => 'nullable|string|max:255',
            'microtik_interface2' => 'nullable|string|max:255',
            'mail_mailer' => 'nullable|string|max:255',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|numeric|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|max:50',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        foreach ($this->settingFieldDefinitions() as $fieldKey => $fieldInfo) {
            if (! $request->has($fieldKey)) {
                continue;
            }

            $value = $request->input($fieldKey, '');

            if (is_string($value)) {
                $value = trim($value);
            }

            if ($fieldKey === 'olt_snmp_enabled') {
                $persistKey = 'olt_snmp_enabled';
                $persistValue = $request->boolean('olt_snmp_enabled') ? '1' : '0';
            } else {
                $persistKey = $fieldKey;
                $persistValue = $value;
            }

            if (in_array($persistKey, $this->encryptedSettingKeys, true) && $request->filled($fieldKey)) {
                $persistValue = Crypt::encryptString((string) $value);
            }

            Setting::updateOrCreate(
                ['key' => $persistKey],
                ['value' => $persistValue]
            );
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    private function getSettingValue(string $key, ?string $default = ''): ?string
    {
        $storedValue = Setting::where('key', $key)->value('value');

        if ($storedValue === null || $storedValue === '') {
            return $default;
        }

        if (in_array($key, $this->encryptedSettingKeys, true)) {
            try {
                return Crypt::decryptString($storedValue);
            } catch (DecryptException $e) {
                Log::warning('Failed to decrypt setting: ' . $key, ['error' => $e->getMessage()]);
                return $default;
            }
        }

        return (string) $storedValue;
    }

    private function settingFieldDefinitions(): array
    {
        return [
            'app_name' => ['group' => 'main'],
            'olt_ip' => ['group' => 'olt'],
            'olt_telnet_username' => ['group' => 'olt'],
            'olt_telnet_password' => ['group' => 'olt'],
            'snmp_oid_names' => ['group' => 'olt'],
            'snmp_oid_powers' => ['group' => 'olt'],
            'snmp_oid_powers_tr' => ['group' => 'olt'],
            'min_ont_power' => ['group' => 'olt'],
            'snmp_oid_uptime' => ['group' => 'olt'],
            'snmp_oid_brand' => ['group' => 'olt'],
            'snmp_oid_temp' => ['group' => 'olt'],
            'snmp_oid_eth' => ['group' => 'olt'],
            'snmp_oid_model' => ['group' => 'olt'],
            'snmp_oid_dist' => ['group' => 'olt'],
            'snmp_oid_regist' => ['group' => 'olt'],
            'snmp_oid_status' => ['group' => 'olt'],
            'olt_snmp_enabled' => ['group' => 'olt'],
            'radius_login' => ['group' => 'radius'],
            'radius_password' => ['group' => 'radius'],
            'prtg_url' => ['group' => 'prtg'],
            'prtg_api_key' => ['group' => 'prtg'],
            'prtg_all_traffic_graph_id' => ['group' => 'prtg'],
            'prtg_main_prob_id' => ['group' => 'prtg'],
            'prtg_mseb' => ['group' => 'prtg'],
            'prtg_temp' => ['group' => 'prtg'],
            'whats_app_url' => ['group' => 'whatsapp'],
            'whats_app_token' => ['group' => 'whatsapp'],
            'whatsapp_instance' => ['group' => 'whatsapp'],
            'whatsapp_number' => ['group' => 'whatsapp'],
            'macurl' => ['group' => 'find_mac'],
            'mactoken' => ['group' => 'find_mac'],
            'microtik_interface1' => ['group' => 'microtik'],
            'microtik_interface2' => ['group' => 'microtik'],
            'mail_mailer' => ['group' => 'mail'],
            'mail_host' => ['group' => 'mail'],
            'mail_port' => ['group' => 'mail'],
            'mail_username' => ['group' => 'mail'],
            'mail_password' => ['group' => 'mail'],
            'mail_encryption' => ['group' => 'mail'],
            'mail_from_address' => ['group' => 'mail'],
            'mail_from_name' => ['group' => 'mail'],
        ];
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
            stream_set_timeout($telnet, 15);

            $this->sendTelnetNegotiation($telnet);
            fwrite($telnet, "\r\n");
            fflush($telnet);

            $response = $this->readTelnetResponse($telnet, ['login:', 'username:', 'user:', 'password:'], 15);
            Log::debug('Telnet initial response raw: ' . bin2hex($response));
            Log::debug('Telnet initial response: ' . trim($response));

            if (trim($response) === '') {
                fclose($telnet);
                Log::error('OLT telnet socket connected but no login banner/prompt was received from ' . $oltIp . ':23. Check that telnet is enabled and reachable.');

                return response()->json([
                    'type' => 'error',
                    'message' => 'OLT telnet is reachable but did not return a login banner. Please verify telnet is enabled on the OLT and the IP/port is correct.',
                    'output' => $response,
                ], 500);
            }

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
