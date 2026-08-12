<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_page_renders_all_expected_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('settings'));

        $response->assertOk();
        $response->assertSee('App Name');
        $response->assertSee('OLT');
        $response->assertSee('Radius');
        $response->assertSee('PRTG');
        $response->assertSee('WhatsApp');
        $response->assertSee('Find MAC');
        $response->assertSee('Microtik');
        $response->assertSee('Mail');
        $response->assertSee('SNMP_OID_NAMES');
        $response->assertSee('PRTG_URL');
        $response->assertSee('WHATS_APP_URL');
        $response->assertSee('MAIL_MAILER');
    }

    public function test_settings_form_stores_all_grouped_values(): void
    {
        $user = User::factory()->create();

        $payload = [
            'app_name' => 'Radi Micro',
            'olt_ip' => '192.168.1.40',
            'olt_telnet_username' => 'admin',
            'olt_telnet_password' => 'secret',
            'snmp_oid_names' => '1.3.6.1.4.1.11863.6.100.1.7.2.1.3',
            'snmp_oid_powers' => '1.3.6.1.4.1.11863.6.100.1.7.2.1.4',
            'snmp_oid_powers_tr' => '1.3.6.1.4.1.11863.6.100.1.7.2.1.5',
            'min_ont_power' => '-12',
            'snmp_oid_uptime' => '1.3.6.1.2.1.1.3.0',
            'snmp_oid_brand' => '1.3.6.1.4.1.11863.6.100.1.7.1.1.2',
            'snmp_oid_temp' => '1.3.6.1.4.1.11863.6.100.1.7.2.1.10',
            'snmp_oid_eth' => '1.3.6.1.2.1.2.2.1.2',
            'snmp_oid_model' => '1.3.6.1.4.1.11863.6.100.1.7.1.1.1',
            'snmp_oid_dist' => '1.3.6.1.4.1.11863.6.100.1.7.2.1.14',
            'snmp_oid_regist' => '1.3.6.1.4.1.11863.6.100.1.7.2.1.16',
            'snmp_oid_status' => '1.3.6.1.4.1.11863.6.100.1.7.2.1.15',
            'snmp_enabled' => '1',
            'radius_login' => 'radius-user',
            'radius_password' => 'radius-pass',
            'prtg_url' => 'https://prtg.example.com',
            'prtg_api_key' => 'abc123',
            'prtg_all_traffic_graph_id' => '1001',
            'prtg_main_prob_id' => '2001',
            'prtg_mseb' => '3001',
            'prtg_temp' => '4001',
            'whats_app_url' => 'https://wa.example.com/api/send',
            'whats_app_token' => 'wa-token',
            'whatsapp_instance' => 'instance-1',
            'whatsapp_number' => '123456789',
            'macurl' => 'https://mac.example.com/api/mac/',
            'mactoken' => 'hello-mac-token',
            'microtik_interface1' => 'sfp-sfpplus1',
            'microtik_interface2' => 'ether10',
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.example.com',
            'mail_port' => '587',
            'mail_username' => 'noreply@example.com',
            'mail_password' => 'mail-pass',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Radi Micro',
        ];

        $response = $this->actingAs($user)->post(route('settings.update'), $payload);

        $response->assertRedirect();
        $this->assertSame('Radi Micro', Setting::where('key', 'app_name')->value('value'));
        $this->assertSame('192.168.1.40', Setting::where('key', 'olt_ip')->value('value'));
        $this->assertSame('1.3.6.1.4.1.11863.6.100.1.7.2.1.3', Setting::where('key', 'snmp_oid_names')->value('value'));
        $this->assertSame('https://prtg.example.com', Setting::where('key', 'prtg_url')->value('value'));
        $this->assertSame('https://wa.example.com/api/send', Setting::where('key', 'whats_app_url')->value('value'));
        $this->assertSame('smtp', Setting::where('key', 'mail_mailer')->value('value'));
    }
}
