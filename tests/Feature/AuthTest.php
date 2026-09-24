<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect('/login');
    }

    public function test_login_page_renders(): void
    {
        $this->get('/login')->assertStatus(200)->assertSee('Masuk ke SIMPM');
    }

    public function test_user_can_login_with_correct_credentials_and_role(): void
    {
        $user = User::create([
            'name' => 'Sri Handayani',
            'username' => 'sri.supervisor',
            'role' => 'supervisor',
            'sub_label' => 'Supervisor · Produksi',
            'avatar' => 'SH',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/login', [
            'username' => 'sri.supervisor',
            'password' => 'password',
            'role' => 'supervisor',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.name', 'Sri Handayani')
            ->assertJsonPath('user.role', 'supervisor');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_when_role_does_not_match_account(): void
    {
        User::create([
            'name' => 'Sri Handayani',
            'username' => 'sri.supervisor',
            'role' => 'supervisor',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/login', [
            'username' => 'sri.supervisor',
            'password' => 'password',
            'role' => 'teknisi',
        ])->assertStatus(422);

        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::create([
            'name' => 'Sri Handayani',
            'username' => 'sri.supervisor',
            'role' => 'supervisor',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/login', [
            'username' => 'sri.supervisor',
            'password' => 'salah',
            'role' => 'supervisor',
        ])->assertStatus(422);

        $this->assertGuest();
    }

    public function test_authenticated_user_can_access_app_shell(): void
    {
        $user = User::create([
            'name' => 'Sri Handayani',
            'username' => 'sri.supervisor',
            'role' => 'supervisor',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)->get('/app')->assertStatus(200)->assertSee('SIMPM', false);
    }

    public function test_guest_cannot_access_app_shell(): void
    {
        $this->get('/app')->assertRedirect('/login');
    }
}
