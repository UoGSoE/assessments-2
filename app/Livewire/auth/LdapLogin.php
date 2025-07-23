<?php

namespace App\Livewire\Auth;

use App\Models\LoginLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class LdapLogin extends Component
{
    public $username = '';

    public $password = '';

    public $remember = false;

    public $error = '';

    public function render()
    {
        return view('livewire.auth.ldap-login');
    }

    public function isStaff($username)
    {
        return preg_match('/^[a-zA-Z]+[0-9]+[a-zA-Z]+$/', $username);
    }

    public function login()
    {
        $this->validate([
            'username' => ['required', 'regex:/^[a-zA-Z]+[0-9]+[a-zA-Z]+$|[0-9]{7}[a-zA-Z]/'],
            'password' => 'required',
        ]);

        $this->error = '';

        // Try LDAP authentication (if enabled)
        $ldapUser = null;
        if (config('ldap.enabled')) {
            if (! \Ldap::authenticate($this->username, $this->password)) {
                $this->error = 'Invalid username or password';
                $this->password = '';

                return;
            }

            $ldapUser = \Ldap::findUser($this->username);
            if (! $ldapUser) {
                $this->error = 'Invalid username or password';
                $this->password = '';

                return;
            }
        }

        // Create or update the user in our database

        $localUser = User::where('username', $this->username)->first();
        if (! $localUser && ! $ldapUser) {
            $this->error = 'Invalid username or password';
            $this->password = '';
            info('Not using LDAP and no local user found: '.$this->username);

            return;
        }

        if ($ldapUser) {
            // if we have an LDAP user, update or create the local user with the current LDAP details (name changes etc)
            $localUser = User::updateOrCreate(
                ['username' => $this->username],
                [
                    'email' => $ldapUser->email,
                    'password' => bcrypt(Str::random(32)),
                    'surname' => $ldapUser->surname,
                    'forenames' => $ldapUser->forenames,
                    'is_admin' => false,
                    'is_staff' => $this->isStaff($this->username),
                ]
            );
        }

        if ($localUser && ! $ldapUser) {
            if (! Auth::attempt([
                'username' => $this->username,
                'password' => $this->password,
            ])) {
                $this->error = 'Invalid username or password';
                $this->password = '';

                return;
            }
        }

        Auth::login($localUser, $this->remember);
        if ($localUser->is_admin) {
            $userType = 'admin';
        } elseif ($localUser->is_staff) {
            $userType = 'staff';
        } else {
            $userType = 'student';
        }
        LoginLog::create(['user_id' => $localUser->id, 'user_type' => $userType]);

        return redirect()->route('home');
    }
}
