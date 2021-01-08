<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

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
        $data = $request->only(['name', 'email', 'password', 'password_confirmation', 'birth_date', 'city', 'work']);

        $validator = Validator::make($data, [
            'name' => ['string', 'min:5', 'max:100'],
            'email' => ['string', 'email'],
            'birth_date' => ['date'],
            'city' => ['string', 'max:100'],
            'work' => ['string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $name = $data['name'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';
        $password_confirmation = $data['password_confirmation'] ?? '';
        $birth_date = $data['birth_date'] ?? '';
        $city = $data['city'] ?? '';
        $work = $data['work'] ?? '';

        $user = $this->currentUser;
        $errors = [];

        if ($email && $email !== $user->email) {
            $validatorEmail = Validator::make(['email' => $email], ['email' => ['unique:users']]);

            if ($validatorEmail->fails()) {
                $errors[] = $validatorEmail->errors();
            }
        }

        if ($password) {
            $validatorPassword = Validator::make([
                'password' => $password, 'password_confirmation' => $password_confirmation
            ], ['password' => ['string', 'min:6', 'confirmed']]);

            if ($validatorPassword->fails()) {
                $errors[] = $validatorPassword->errors();
            }
        }
        if ($errors) {
            return response()->json(['errors' => $errors], 400);
        }

        if ($password) {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $user->password = $hash;
        }

        if ($email) {
            $user->email = $email;
        }

        if ($name) {
            $user->name = $name;
        }

        if ($birth_date) {
            $user->birth_date = $birth_date;
        }

        if ($city) {
            $user->city = $city;
        }

        if ($work) {
            $user->work = $work;
        }

        $user->save();
        return response()->json($user);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateAvatar(Request $request)
    {
        $avatar = $request->file('avatar');

        $validator = Validator::make(['avatar' => $avatar], [
            'avatar' => ['required', 'mimetypes:image/jpeg,image/jpg,image/png'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        } else {
            $filename = md5(time().rand(0, 9999)) . '.jpg';
            $path = public_path('/media/avatars');

            Image::make($avatar->path())
                ->fit(200, 200)
                ->save("{$path}/{$filename}");

            $user = User::find($this->currentUser['id']);
            $user->avatar = $filename;
            $user->save();

            return response()->json(['url' => url("/media/avatars/{$filename}")]);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCover(Request $request)
    {
        $cover = $request->file('cover');

        $validator = Validator::make(['cover' => $cover], [
            'cover' => ['required', 'mimetypes:image/jpeg,image/jpg,image/png'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()]);
        } else {
            $filename = md5(time().rand(0, 9999)) . '.jpg';
            $path = public_path('/media/covers');

            Image::make($cover->path())
                ->fit(850, 310)
                ->save("{$path}/{$filename}");

            $user = User::find($this->currentUser['id']);
            $user->cover = $filename;
            $user->save();

            return response()->json(['url' => url("/media/covers/{$filename}")]);
        }
    }
}
