<?php

namespace App\Http\Controllers;

use App\Models\User;
use Carbon\Carbon;

class EmailConfirmationController extends Controller
{
    public function confirm($token)
    {
        $user = User::where('remember_token', $token)->first();

        if (!$user) {
            return redirect()->route('home')->with('flash_error', 'Link de confirmação inválido ou expirado.');
        }

        // Atualiza como verificado
        $user->email_verified_at = Carbon::now();
        $user->remember_token = null; // invalida o token
        $user->save();

        return redirect()->route('home')->with('flash_success', 'E-mail confirmado com sucesso!');
    }
    

}
