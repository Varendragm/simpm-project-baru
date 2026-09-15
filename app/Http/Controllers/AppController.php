<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\BuildsBootstrapData;

class AppController extends Controller
{
    use BuildsBootstrapData;

    public function index()
    {
        $bootstrap = $this->buildBootstrapData();

        return view('layouts.app', compact('bootstrap'));
    }
}
