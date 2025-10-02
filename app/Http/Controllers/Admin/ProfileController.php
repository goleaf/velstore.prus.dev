<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        abort_unless($user, 403);

        $profile = [
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ];

        $security = [
            'two_factor_enabled' => ! empty($user->two_factor_secret ?? null),
            'email_verified_at' => $user->email_verified_at,
        ];

        return view('admin.profile.show', [
            'user' => $user,
            'profile' => $profile,
            'security' => $security,
        ]);
    }
}
