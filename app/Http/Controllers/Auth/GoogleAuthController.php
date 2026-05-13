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

    public function callback(): RedirectResponse
    {

        try {
            $user = $this->googleAuthService->authenticate(
                Socialite::driver('google')->stateless()->user()
            );
             Log::info('Google Callback - User Data', [
            'email' => $user->getEmail(),
            'id' => $user->getId(),
            'hd' => request('hd'),
        ]);
        } catch (AuthenticationException $exception) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => $exception->getMessage()]);
        }

        Auth::login($user, remember: true);
        request()->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
