<?php

use App\Livewire\Auth\LdapLogin;
use App\Livewire\Home;
use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\actingAs;
use function Pest\Livewire\livewire;


describe('LDAP Login Page', function () {
    it('can be rendered', function () {
        livewire(LdapLogin::class)
            ->assertSee('Student Assessment Calendar')
            ->assertSee('Sign in with your university credentials')
            ->assertSee('Username')
            ->assertSee('Password')
            ->assertSee('Remember me')
            ->assertSee('Sign in');
    });

    it('shows validation errors for empty fields', function () {
        livewire(LdapLogin::class)
            ->set('username', '')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['username', 'password']);
    });
});



it('logs in with valid local user credentials', function () {
        $user = User::factory()->create([
            'username' => 'cms32y',
            'password' => 'secret',
        ]);

        livewire(LdapLogin::class)
            ->set('username', 'cms32y')
            ->set('password', 'secret')
            ->call('login')
            ->assertHasNoErrors(['username', 'password'])
            ->assertRedirect(Home::class);

        $this->assertAuthenticated();
        $this->assertAuthenticatedAs($user);
    });

    

    it('creates login log entry for successful local login', function () {
        $user = User::factory()->create([
            'username' => 'cms32y',
            'password' => 'secret',
            'is_staff' => true,
        ]);

        livewire(LdapLogin::class)
            ->set('username', 'cms32y')
            ->set('password', 'secret')
            ->call('login');

        $this->assertDatabaseHas('login_logs', [
            'user_id' => $user->id,
            'user_type' => 'staff',
        ]);
    });


describe('Admin User Login', function () {
    it('creates correct login log for admin user', function () {
        $admin = User::factory()->create([
            'username' => 'admin1x',
            'password' => 'secret',
            'is_admin' => true,
        ]);

        livewire(LdapLogin::class)
            ->set('username', 'admin1x')
            ->set('password', 'secret')
            ->call('login');

        $this->assertDatabaseHas('login_logs', [
            'user_id' => $admin->id,
            'user_type' => 'admin',
        ]);
    });
});