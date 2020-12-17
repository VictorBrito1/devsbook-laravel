<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FeedController extends Controller
{
    private $currentUser;

    /**
     * FeedController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');

        $this->currentUser = auth()->user();
    }
}
