<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_their_notification_as_read(): void
    {
        $user = User::factory()->create();
        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test-notification',
            'data' => ['message' => 'Task assigned'],
        ]);

        $this->actingAs($user)
            ->patch(route('notifications.read', $notification))
            ->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'id' => $notification->id,
        ]);

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
