<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('__isMaster')) {
    function __isMaster()
    {
        return Auth::check() && Auth::user()->master === 2;
    }
}

if (!function_exists('__isPartialSuperAdmin')) {
    function __isPartialSuperAdmin()
    {
        return Auth::check() && Auth::user()->master === 1;
    }
}
