<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function index()
    {
        $register = DB::table('renewal_register')->where('user_id', auth()->id())->orderByDesc('id')->get();

        $shell = \App\Platform\Services\WorkerShellService::build(auth()->id(), '');
        extract($shell); // workerCatalog, registryRows, registryRow, profileImg, coverImg, tokenTotal
        $firstName = explode(' ', trim(auth()->user()->name))[0];

        return view('dashboard.register', compact(
            'register', 'workerCatalog', 'tokenTotal', 'firstName'
        ));
    }
}
