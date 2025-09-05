<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('isAdminSuper')) {
    function isAdminSuper()
    {
        return Auth::check() && Auth::user()->email === env('MAILADMINSUPER');
    }
}
