<?php

namespace App\Http\Controllers\Auth;

use App\Events\NewUserRegisterdEvent;
use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\NewUserRegisterdNotification;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        // send notifications to admin
        $admin = Admin::find(1);
        // way 1
        $admin->notify(new NewUserRegisterdNotification($user));
        // way 2
        // Notification::send($admin,new NewUserRegisterdNotification($user));

        // BROADCAST EVENT
        // هيك بكون شغلت الايفنت
        // way 1
        NewUserRegisterdEvent::dispatch($user);
        // way 2
         // Broadcast(new NewUserRegisterdEvent());
        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}
