<?php

namespace App\Http\Controllers;

use App\Models\User;
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

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $text = $request->get('text', null);

        if ($text) {
            $data = [];
            $users = User::where('name', 'like', "%$text%")->get();

            foreach ($users as $user) {
                $data['users'][] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'avatar' => $user['avatar'],
                ];
            }

            return response()->json($data);
        }

        return response()->json(['errors' => ['text' => ['The text field is required.']]], 400);
    }
}
