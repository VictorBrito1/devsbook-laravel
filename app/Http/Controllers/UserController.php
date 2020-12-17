<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    private $currentUser;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');

        $this->currentUser = auth()->user();
    }

    public function create(Request $request)
    {

    }

    public function update(Request $request)
    {

    }

    public function updateAvatar(Request $request)
    {

    }
}
