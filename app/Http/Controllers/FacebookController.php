<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class FacebookController extends Controller
{
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function facebookSignIn()
    {
        $user = Socialite::driver('facebook')->user();
        $existingUser = User::where('facebook_id', $user->id)->first();

        if ($existingUser) {
            Auth::login($existingUser);
            return redirect('/dashboard');
        } else {
            $profilePicContents = file_get_contents($user->getAvatar().'&access_token='.$user->token);
            Storage::disk('public')->put('users/'.$user->id.'.jpg', $profilePicContents);

            $newUser = User::factory()->create([
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => 'users/'.$user->id.'.jpg',
                'facebook_id' => $user->id,
                'email_verified_at' => now(),
                'password' => bcrypt(Str::random(32)),
                'username' => Str::slug($user->name, ''),
            ]);

            Auth::login($newUser);
            return redirect('/dashboard');
        }
    }
}
