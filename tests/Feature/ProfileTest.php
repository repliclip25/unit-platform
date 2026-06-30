<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name'  => 'Test User',
                'email' => $user->email, // keep same email to avoid verification reset
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name'  => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_request_account_deletion(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'confirm_delete' => 'DELETE', // platform requires typing DELETE to confirm
            ]);

        // Platform schedules deletion with a 30-day grace period, doesn't hard-delete immediately
        $response->assertRedirect('/');
        $this->assertNotNull($user->fresh()->deletion_requested_at);
    }

    public function test_correct_confirmation_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'confirm_delete' => 'wrong', // not DELETE
            ]);

        // Validation uses the default error bag — no named bag on this form
        $response
            ->assertSessionHasErrors('confirm_delete')
            ->assertRedirect('/profile');

        $this->assertNull($user->fresh()->deletion_requested_at);
    }
}
