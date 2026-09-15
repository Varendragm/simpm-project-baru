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
        $response = $this->get('/');
        $response->assertRedirect('/login');
    }

    public function test_login_page_renders(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Masuk ke SIMPM');
    }

    public function test_user_can_login_with_correct_credentials(): void
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
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('user.name', 'Sri Handayani');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        User::create([
            'name' => 'Sri Handayani',
            'username' => 'sri.supervisor',
            'role' => 'supervisor',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/login', [
            'username' => 'sri.supervisor',
            'password' => 'salah',
        ]);

        $response->assertStatus(422);
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

        $response = $this->actingAs($user)->get('/app');
        $response->assertStatus(200);
        $response->assertSee('SIMPM', false);
    }

    public function test_guest_cannot_access_app_shell(): void
    {
        $response = $this->get('/app');
        $response->assertRedirect('/login');
    }
}
