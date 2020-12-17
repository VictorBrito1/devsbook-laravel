<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    private $currentUser;

    /**
     * UserController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create']]);

        $this->currentUser = auth()->user();
    }

    /**
     * @param Request $request
     * @return string[]
     */
    public function create(Request $request)
    {
        $data = $request->only(['name', 'email', 'password', 'password_confirmation', 'birth_date']);

        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'min:5', 'max:100'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
            'birth_date' => ['required', 'date'],
        ]);

        $array = [];

        if ($validator->fails()) {
            $array['errors'] = $validator->errors();
        } else {
            $email = $data['email'];
            $password = $data['password'];

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $user = new User();
            $user->name = $data['name'];
            $user->email = $email;
            $user->password = $hash;
            $user->birth_date = $data['birth_date'];
            $user->save();

            $token = auth()->attempt([
                'email' => $email,
                'password' => $password
            ]);

            if (!$token) {
                $array['errors'][] = 'Ocorreu um erro';
                return $array;
            }

            $array['token'] = $token;
        }

        return $array;
    }

    public function update(Request $request)
    {

    }

    public function updateAvatar(Request $request)
    {

    }
}
