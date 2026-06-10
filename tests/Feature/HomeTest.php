<?php

namespace Tests\Feature;

use App\Models\Berkas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/home')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_home_page(): void
    {
        $user = User::factory()->create();

        Berkas::factory()->count(2)->create(['status' => 'Aktif', 'created_by' => $user->id]);
        Berkas::factory()->create(['status' => 'Inaktif', 'created_by' => $user->id]);
        Berkas::factory()->create(['status' => 'Musnah', 'created_by' => $user->id]);

        $this->actingAs($user)
            ->get('/home')
            ->assertOk()
            ->assertSee('Beranda Utama')
            ->assertSee('Berkas Aktif')
            ->assertSee('Berkas Inaktif')
            ->assertSee('Berkas Musnah');
    }
}
