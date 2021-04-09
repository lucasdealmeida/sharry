<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function return_the_access_token_when_email_and_password_are_right()
    {
        User::factory()->create([
            'email'    => 'john@doe.com',
            'password' => Hash::make('secret'),
        ]);

        $response = $this->post('/api/login', [
            'email'    => 'john@doe.com',
            'password' => 'secret',
        ]);

        $response->assertOk();

        $response->assertJsonStructure(['accessToken', 'plainTextToken']);
    }

    /** @test */
    public function return_error_401_when_email_or_password_is_wrong()
    {
        $this->post('/api/login', [
            'email'    => 'john@doe.com',
            'password' => 'secret',
        ])->assertStatus(401);
    }

    /** @test */
    public function email_field_is_required_to_login()
    {
        $this->post('/api/login', [
            'email'    => '',
            'password' => 'secret',
        ])->assertSessionHasErrors([
            'email' => __('validation.required', ['attribute' => 'email']),
        ]);
    }

    /** @test */
    public function passwrod_field_is_required_to_login()
    {
        $this->post('/api/login', [
            'email'    => 'john@doe.com',
            'password' => '',
        ])->assertSessionHasErrors([
            'password' => __('validation.required', ['attribute' => 'password']),
        ]);
    }
}
