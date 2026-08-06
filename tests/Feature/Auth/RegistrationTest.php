<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        // New registrations default to the Memory Hub (not the AVA wizard,
        // not the dashboard) — training memory is zero-commitment and the
        // platform's easy route to collect investment before asking for the
        // harder "deploy a worker" decision — unless they arrived via an
        // intended /hire/ava/* URL, which redirect()->intended() honors instead.
        $response->assertRedirect(route('hire.memory', absolute: false));
    }
}
