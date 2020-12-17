<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * AuthController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'unauthorized']]);
    }

    public function login(Request $request)
    {

    }

    public function logout(Request $request)
    {

    }

    public function refresh(Request $request)
    {

    }
}
