<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\Notification;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_clicking_a_notification_redirects_to_the_notifications_page(): void
    {
        $user = User::factory()->create();

        $user->notify(new class extends Notification {
            public function via($notifiable): array
            {
                return ['database'];
            }

            public function toArray($notifiable): array
            {
                return [
                    'status' => 'Down',
                    'device' => 'Router X',
                    'sensor' => 'Ping',
                    'message' => 'Device offline',
                ];
            }
        });

        $notification = $user->notifications()->first();

        $response = $this
            ->actingAs($user)
            ->from('/dashboard')
            ->get(route('notifications.read', $notification));

        $response->assertRedirect(route('notifications.index', absolute: false));
        $this->assertNotNull($notification->fresh()->read_at);
    }
}
