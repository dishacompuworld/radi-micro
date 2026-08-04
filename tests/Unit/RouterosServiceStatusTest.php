<?php

namespace Tests\Unit;

use App\Support\RouterosServiceStatus;
use PHPUnit\Framework\TestCase;

class RouterosServiceStatusTest extends TestCase
{
    public function test_it_normalizes_toggle_values(): void
    {
        $this->assertTrue(RouterosServiceStatus::isEnabled(true));
        $this->assertTrue(RouterosServiceStatus::isEnabled('true'));
        $this->assertTrue(RouterosServiceStatus::isEnabled('1'));
        $this->assertTrue(RouterosServiceStatus::isEnabled('yes'));
        $this->assertFalse(RouterosServiceStatus::isEnabled(false));
        $this->assertFalse(RouterosServiceStatus::isEnabled('false'));
        $this->assertFalse(RouterosServiceStatus::isEnabled('0'));
    }

    public function test_it_reads_service_status_by_name_instead_of_array_position(): void
    {
        $services = [
            ['name' => 'ssh', 'disabled' => 'true'],
            ['name' => 'www', 'disabled' => 'false'],
            ['name' => 'www-ssl', 'disabled' => 'no'],
            ['name' => 'winbox', 'disabled' => 'yes'],
        ];

        $this->assertSame('disabled', RouterosServiceStatus::getServiceStatus($services, 'ssh'));
        $this->assertSame('enabled', RouterosServiceStatus::getServiceStatus($services, 'www'));
        $this->assertSame('enabled', RouterosServiceStatus::getServiceStatus($services, 'www-ssl'));
        $this->assertSame('disabled', RouterosServiceStatus::getServiceStatus($services, 'winbox'));
    }

    public function test_it_returns_disabled_for_missing_service(): void
    {
        $services = [
            ['name' => 'telnet', 'disabled' => 'false'],
        ];

        $this->assertSame('disabled', RouterosServiceStatus::getServiceStatus($services, 'ssh'));
    }
}
