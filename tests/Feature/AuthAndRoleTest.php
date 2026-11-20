<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Menggunakan SQLite :memory: dari phpunit.xml (tidak menyentuh database hPanel)
        Schema::create('tb_users', function (Blueprint $table) {
            $table->increments('id');
            $table->text('username');
            $table->text('password');
            $table->string('status', 1)->default('1');
        });

        Schema::create('tb_mahasiswa', function (Blueprint $table) {
            $table->increments('id');
            $table->text('nim');
            $table->text('nama');
        });
    }

    public function test_halaman_pertama_menampilkan_form_login(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('Masuk ke Akun Anda')
            ->assertSee('Username / NIM');
    }

    public function test_guest_diarahkan_ke_login_saat_mengakses_dashboard(): void
    {
        $this->get('/admin/dashboard')->assertRedirect(route('login'));
        $this->get('/mahasiswa/dashboard')->assertRedirect(route('login'));
    }

    public function test_login_gagal_jika_password_salah(): void
    {
        User::create([
            'username' => 'admin',
            'password' => Hash::make('rahasia123'),
            'status' => '1',
        ]);

        $response = $this->from(route('login'))->post(route('login.post'), [
            'username' => 'admin',
            'password' => 'salah',
        ]);

        $response->assertRedirect(route('login'))
            ->assertSessionHasErrors('username');
        $this->assertGuest();
    }

    public function test_admin_berhasil_login_dan_dibatasi_dari_area_mahasiswa(): void
    {
        User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'status' => '1',
        ]);

        $loginResponse = $this->post(route('login.post'), [
            'username' => 'admin',
            'password' => 'admin123',
        ]);

        $loginResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard Admin');

        $this->get(route('mahasiswa.dashboard'))
            ->assertRedirect(route('admin.dashboard'))
            ->assertSessionHas('error');
    }

    public function test_mahasiswa_login_dengan_nim_terhubung_tb_mahasiswa_dan_dibatasi_dari_area_admin(): void
    {
        Mahasiswa::create([
            'nim' => '2024001',
            'nama' => 'Budi Santoso',
        ]);

        User::create([
            'username' => '2024001',
            'password' => Hash::make('mhs123'),
            'status' => '1',
        ]);

        $loginResponse = $this->post(route('login.post'), [
            'username' => '2024001',
            'password' => 'mhs123',
        ]);

        $loginResponse->assertRedirect(route('mahasiswa.dashboard'));
        $this->assertAuthenticated();

        $this->get(route('mahasiswa.dashboard'))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertSee('2024001');

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('mahasiswa.dashboard'))
            ->assertSessionHas('error');
    }

    public function test_logout_mengembalikan_ke_halaman_login(): void
    {
        $admin = User::create([
            'username' => 'admin',
            'password' => Hash::make('admin123'),
            'status' => '1',
        ]);

        $this->actingAs($admin)
            ->withSession(['user_role' => 'admin'])
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
