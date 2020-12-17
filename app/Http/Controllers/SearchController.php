<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    private $currentUser;

    /**
     * SearchController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');

        $this->currentUser = auth()->user();
    }
}
