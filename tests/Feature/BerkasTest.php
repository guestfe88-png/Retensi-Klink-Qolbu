<?php

namespace Tests\Feature;

use App\Models\Berkas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BerkasTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_admin_can_access_user_management(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk();
    }

    public function test_petugas_cannot_access_user_management(): void
    {
        $petugas = User::factory()->create(['role' => 'petugas']);

        $this->actingAs($petugas)
            ->get(route('users.index'))
            ->assertForbidden();
    }

    public function test_berkas_detail_page_loads(): void
    {
        $user = User::factory()->create();
        $berkas = Berkas::factory()->create(['created_by' => $user->id]);

        $this->actingAs($user)
            ->get(route('berkas.show', $berkas))
            ->assertOk();
    }
}
