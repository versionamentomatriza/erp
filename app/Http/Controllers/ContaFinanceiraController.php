<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContaFinanceiraController extends Controller
{
    public function index()
    {
        return view('contas-financeiras.index');
    }
}
