<?php

namespace Tests\Feature;

use App\Models\Santri;
use App\Models\User;
use Database\Seeders\RolesAndAdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SantriDetailGuardianTest extends TestCase
{
    use RefreshDatabase;

    public function test_nama_orang_tua_wali_terlihat_di_detail_santri(): void
    {
        $admin = $this->adminTuUser();

        $santri = Santri::query()->create([
            'nis' => '4260001',
            'full_name' => 'Ahmad Fauzi',
            'gender' => 'L',
            'address' => 'Jl. Melati No. 1',
            'status' => 'aktif',
        ]);

        $santri->guardians()->create([
            'relation_type' => 'wali',
            'name' => 'Bapak Fauzi',
            'phone' => '-',
            'is_whatsapp' => false,
            'is_primary' => true,
        ]);

        $this->actingAs($admin)
            ->get(route('santri.show', $santri))
            ->assertOk()
            ->assertSeeText('Nama Orang Tua/Wali')
            ->assertSeeText('Bapak Fauzi');
    }

    public function test_nama_orang_tua_wali_bisa_diedit_saat_edit_santri(): void
    {
        $admin = $this->adminTuUser();

        $santri = Santri::query()->create([
            'nis' => '4260002',
            'full_name' => 'Ali Hasan',
            'gender' => 'L',
            'address' => 'Jl. Kenanga No. 2',
            'status' => 'aktif',
        ]);

        $guardian = $santri->guardians()->create([
            'relation_type' => 'wali',
            'name' => 'Pak Hasan',
            'phone' => '-',
            'is_whatsapp' => false,
            'is_primary' => true,
        ]);

        $this->actingAs($admin)
            ->put(route('santri.update', $santri), [
                'guardian_name' => 'Bu Hasanah',
                'nis' => $santri->nis,
                'full_name' => $santri->full_name,
                'gender' => $santri->gender,
                'birth_place' => $santri->birth_place,
                'birth_date' => $santri->birth_date?->toDateString(),
                'phone' => $santri->phone,
                'address' => $santri->address,
                'entry_date' => $santri->entry_date?->toDateString(),
                'status' => $santri->status,
            ])
            ->assertRedirect(route('santri.index'));

        $this->assertDatabaseHas('santri_guardians', [
            'id' => $guardian->id,
            'name' => 'Bu Hasanah',
        ]);

        $this->actingAs($admin)
            ->get(route('santri.show', $santri))
            ->assertOk()
            ->assertSeeText('Bu Hasanah');
    }

    private function adminTuUser(): User
    {
        $this->seed(RolesAndAdminSeeder::class);

        $user = User::factory()->create();
        $user->assignRole('admin_tu');

        return $user;
    }
}
