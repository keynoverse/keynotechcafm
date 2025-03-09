<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email'
                    ],
                    'token'
                ]
            ]);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Invalid credentials'
            ]);
    }

    /** @test */
    public function user_can_logout()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Successfully logged out'
            ]);
    }

    /** @test */
    public function user_can_get_current_user_info()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email
                ]
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/auth/user');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    /** @test */
    public function user_can_refresh_token()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'token'
                ]
            ]);
    }

    /** @test */
    public function user_can_request_password_reset()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        $response = $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password reset link sent to your email'
            ]);
    }

    /** @test */
    public function user_can_reset_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com'
        ]);

        // First request password reset
        $this->postJson('/api/auth/forgot-password', [
            'email' => 'test@example.com'
        ]);

        // Get the token from the notification
        $token = \DB::table('password_resets')
            ->where('email', 'test@example.com')
            ->first()->token;

        $response = $this->postJson('/api/auth/reset-password', [
            'email' => 'test@example.com',
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password has been reset successfully'
            ]);

        // Verify the new password works
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /** @test */
    public function user_can_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123')
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/change-password', [
            'current_password' => 'oldpassword123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password changed successfully'
            ]);

        // Verify the new password works
        $this->assertTrue(Hash::check('newpassword123', $user->fresh()->password));
    }

    /** @test */
    public function user_cannot_change_password_with_incorrect_current_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword123')
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/auth/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    /** @test */
    public function user_can_update_profile()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->putJson('/api/auth/profile', [
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'name' => 'Updated Name',
                    'email' => 'updated@example.com'
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com'
        ]);
    }
}
