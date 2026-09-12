<?php

namespace Tests\Feature;

use App\Http\Controllers\NewMicrotikController;
use App\Models\Server;
use Illuminate\Http\Request;
use Tests\TestCase;

class UdpLogFetchTest extends TestCase
{
    public function test_it_fetches_udp_logs_from_a_bound_socket_and_writes_a_server_named_file(): void
    {
        $server = Server::create([
            'name' => 'Main Router',
            'mip' => '127.0.0.1',
            'shortname' => 'main-router-' . uniqid(),
            'username' => 'admin',
            'password' => 'pass',
            'enable' => true,
        ]);

        $controller = app(NewMicrotikController::class);

        $response = $controller->fetchUdpLogs(Request::create('/microtik/log/udp', 'GET', [
            'sserver' => $server->id,
            'host' => '127.0.0.1',
            'port' => 0,
            'timeout' => 0.2,
            'max_packets' => 5,
        ]));

        $this->assertSame(200, $response->getStatusCode());

        $payload = json_decode($response->getContent(), true);

        $this->assertTrue($payload['success'] ?? false);
        $this->assertArrayHasKey('logs', $payload);
        $this->assertArrayHasKey('log_file', $payload);
        $this->assertStringContainsString('main-router', basename($payload['log_file']));
        $this->assertFileExists($payload['log_file']);
    }
}
