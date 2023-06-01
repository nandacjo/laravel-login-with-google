<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialLogin;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialLoginController extends Controller
{
    //redirectToProvider = redirect ke halaman login
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    //handleProviderCallback
    public function handleProviderCallback($provider)
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();
        } catch (Exception $e) {
            return redirect()->back();
        }

        $authUser = $this->findOrCreateUser($user, $provider);
        Auth()->login($authUser, true);

        return redirect()->route('dashboard');
    }

    //findOrCreateUser
    public function findOrCreateUser($socialUser, $provider)
    {
        //check provider di table social login
        $socialLogin = SocialLogin::where('provider_id', $socialUser)->getId();
        if ($socialLogin) {
            return $socialLogin->user();
        } else {
            //masukin ke db table user
            $user = User::where('email', $socialUser->getEmail())->first();
            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => ''

                ]);
            }

            $user->socialLogins()->create([
                'provider' => $socialUser->getId(),
                'provider_name' => $provider
            ]);

            return $user;
        }
    }
}