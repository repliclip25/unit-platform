<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function index()
    {
        $register = DB::table('renewal_register')->where('user_id', auth()->id())->orderByDesc('id')->get();
        return view('dashboard.register', compact('register'));
    }
}
