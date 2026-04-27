<?php

namespace Tests\Feature;

use App\Models\Santri;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SantriAutoNisTest extends TestCase
{
    use RefreshDatabase;

    public function test_nis_otomatis_dibuat_saat_menambah_santri_baru(): void
    {
        $admin = $this->adminTuUser();

        $response = $this
            ->actingAs($admin)
            ->post(route('santri.store'), [
                'guardian_name' => 'Bapak Fauzi',
                'full_name' => 'Ahmad Fauzi',
                'gender' => 'L',
                'address' => 'Jl. Melati No. 1',
                'entry_date' => '2026-04-23',
                'status' => 'aktif',
            ]);

        $response->assertRedirect(route('santri.index'));

        $this->assertDatabaseHas('santris', [
            'full_name' => 'Ahmad Fauzi',
            'nis' => '4260001',
        ]);

        $santri = Santri::query()->where('full_name', 'Ahmad Fauzi')->firstOrFail();

        $this->assertDatabaseHas('santri_guardians', [
            'santri_id' => $santri->id,
            'relation_type' => 'wali',
            'name' => 'Bapak Fauzi',
            'is_primary' => true,
        ]);
    }

    public function test_nis_otomatis_bertambah_berdasarkan_urutan_terakhir(): void
    {
        $admin = $this->adminTuUser();

        Santri::query()->create([
            'nis' => '6260009',
            'full_name' => 'Santri Lama',
            'gender' => 'L',
            'address' => 'Alamat Lama',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->post(route('santri.store'), [
                'guardian_name' => 'Ibu Baru',
                'full_name' => 'Santri Baru',
                'gender' => 'P',
                'address' => 'Alamat Baru',
                'entry_date' => '2026-06-01',
                'status' => 'aktif',
            ])
            ->assertRedirect(route('santri.index'));

        $this->assertDatabaseHas('santris', [
            'full_name' => 'Santri Baru',
            'nis' => '6260010',
        ]);
    }

    public function test_nis_otomatis_kembali_ke_0001_untuk_bulan_berbeda(): void
    {
        $admin = $this->adminTuUser();

        Santri::query()->create([
            'nis' => '4260099',
            'full_name' => 'Santri Angkatan 2026',
            'gender' => 'L',
            'address' => 'Alamat 2026',
            'status' => 'aktif',
        ]);

        $this->actingAs($admin)
            ->post(route('santri.store'), [
                'guardian_name' => 'Ibu Angkatan 2027',
                'full_name' => 'Santri Angkatan 2027',
                'gender' => 'P',
                'address' => 'Alamat 2027',
                'entry_date' => '2026-05-05',
                'status' => 'aktif',
            ])
            ->assertRedirect(route('santri.index'));

        $this->assertDatabaseHas('santris', [
            'full_name' => 'Santri Angkatan 2027',
            'nis' => '5260001',
        ]);
    }

    private function adminTuUser(): User
    {
        $this->seed(RolesAndAdminSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('admin_tu');

        return $user;
    }
}
