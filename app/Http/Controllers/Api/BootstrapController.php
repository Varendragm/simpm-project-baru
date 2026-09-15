<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\BuildsBootstrapData;
use App\Http\Controllers\Controller;

class BootstrapController extends Controller
{
    use BuildsBootstrapData;

    public function __invoke()
    {
        return response()->json($this->buildBootstrapData());
    }
}
