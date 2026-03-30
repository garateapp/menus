<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Factory as SocialiteFactory;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_google_login_rejects_emails_outside_the_allowed_domain(): void
    {
        $this->mockGoogleUser('external@gmail.com', 'google-001', 'External User');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_google_login_creates_a_worker_when_the_email_is_allowed(): void
    {
        $this->mockGoogleUser('worker@greenex.cl', 'google-002', 'Packing Worker');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'worker@greenex.cl',
            'google_id' => 'google-002',
            'is_active' => true,
        ]);
    }

    public function test_google_login_links_google_id_to_an_existing_user_with_the_same_email(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@greenex.cl',
            'google_id' => null,
            'is_active' => true,
        ]);

        $this->mockGoogleUser('existing@greenex.cl', 'google-003', 'Existing User');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticatedAs($user->fresh());
        $this->assertSame('google-003', $user->fresh()->google_id);
    }

    public function test_inactive_users_can_not_log_in_with_google(): void
    {
        User::factory()->create([
            'email' => 'inactive@greenex.cl',
            'google_id' => null,
            'is_active' => false,
        ]);

        $this->mockGoogleUser('inactive@greenex.cl', 'google-004', 'Inactive User');

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    private function mockGoogleUser(string $email, string $id, string $name): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getEmail')->andReturn($email);
        $socialiteUser->shouldReceive('getId')->andReturn($id);
        $socialiteUser->shouldReceive('getName')->andReturn($name);
        $socialiteUser->shouldReceive('getNickname')->andReturn(null);
        $socialiteUser->shouldReceive('getAvatar')->andReturn(null);

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('stateless')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialiteUser);

        $factory = Mockery::mock(SocialiteFactory::class);
        $factory->shouldReceive('driver')->with('google')->andReturn($provider);

        $this->app->instance(SocialiteFactory::class, $factory);
    }
}
