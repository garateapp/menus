<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\GoogleAuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
class GoogleAuthController extends Controller
{
    public function __construct(
        private readonly GoogleAuthService $googleAuthService,
    ) {
    }

    public function redirect(): RedirectResponse
    {
        /** @var RedirectResponse $response */
        $response = Socialite::driver('google')->redirect();

        return $response;
    }

    // public function callback(): RedirectResponse
    // {

    //     try {
    //         $user = $this->googleAuthService->authenticate(
    //             Socialite::driver('google')->stateless()->user()

    //         );

    //     ]);
    //     } catch (AuthenticationException $exception) {
    //         return redirect()
    //             ->route('login')
    //             ->withErrors(['email' => $exception->getMessage()]);
    //     }

    //     Auth::login($user, remember: true);
    //     request()->session()->regenerate();

    //     return redirect()->route('dashboard');
    // }
    public function callback(): RedirectResponse
{
    try {
        // 1️⃣ Obtén el usuario de Google PRIMERO
        $googleUser = Socialite::driver('google')->stateless()->user();

        // 2️⃣ Loguea los datos CRÍTICOS antes de autenticar
        Log::info('Google Callback - Raw User Data', [
            'email' => $googleUser->getEmail(),
            'id' => $googleUser->getId(),
            'name' => $googleUser->getName(),
            'hd_param' => request('hd'),
            'access_token' => substr($googleUser->token, 0, 20).'...', // solo para debug
        ]);

        // 3️⃣ Ahora sí, autentica con tu servicio
        $user = $this->googleAuthService->authenticate($googleUser);

    } catch (AuthenticationException $exception) {
        // 4️⃣ Loguea el error REAL para verlo en storage/logs/laravel.log
        Log::error('Google Auth Failed', [
            'message' => $exception->getMessage(),
            'email' => request('code') ? 'code_present' : 'no_code',
            'hd' => request('hd'),
        ]);

        return redirect()
            ->route('login')
            ->withErrors(['email' => $exception->getMessage()]);
    }

    Auth::login($user, remember: true);
    request()->session()->regenerate();

    return redirect()->route('dashboard');
}
}
