<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Spatie\Permission\Models\Role;

class GoogleAuthService
{
    public function authenticate(SocialiteUser $googleUser): User
    {
        $email = Str::lower((string) $googleUser->getEmail());
        $allowedDomain = Str::lower((string) config('services.google.allowed_domain'));

        if ($email === '' || ! Str::endsWith($email, '@'.$allowedDomain)) {
            throw new AuthenticationException('Solo se permiten correos corporativos.');
        }

        return DB::transaction(function () use ($googleUser, $email): User {
            $user = User::query()->where('email', $email)->first();

            if ($user && ! $user->isActive()) {
                throw new AuthenticationException('Tu cuenta está inactiva.');
            }

            if (! $user) {
                Role::findOrCreate('Worker', 'web');

                $user = User::query()->create([
                    'name' => $googleUser->getName() ?: 'Trabajador Greenex',
                    'email' => $email,
                    'username' => Str::before($email, '@'),
                    'password' => null,
                    'google_id' => (string) $googleUser->getId(),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]);

                $user->assignRole('Worker');

                return $user;
            }

            if (! $user->google_id) {
                $user->forceFill([
                    'google_id' => (string) $googleUser->getId(),
                    'email_verified_at' => $user->email_verified_at ?: now(),
                ])->save();
            }

            return $user;
        });
    }
}
