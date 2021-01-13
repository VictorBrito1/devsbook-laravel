<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    private $currentUser;

    /**
     * PostController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth:api');

        $this->currentUser = auth()->user();
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function like($id)
    {
        $post = Post::find($id);

        if ($post) {
            $like = PostLike::where('id_post', $id)->where('id_user', $this->currentUser['id'])->first();
            $liked = false;

            if ($like) {
                $like->delete();
            } else {
                $postLike = new PostLike();
                $postLike->id_post = $id;
                $postLike->id_user = $this->currentUser['id'];
                $postLike->created_at = new \DateTime();
                $postLike->save();

                $liked = true;
            }

            $totalLikes = PostLike::where('id_post', $id)->count();

            return response()->json(['liked' => $liked, 'totalLikes' => $totalLikes]);
        }

        return response()->json(['errors' => ['Post does not exist.']], 404);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function comment(Request $request, $id)
    {
        $body = $request->get('body', null);

        $validator = Validator::make(['body' => $body], [
            'body' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $post = Post::find($id);

        if ($post) {
            $postComment = new PostComment();
            $postComment->body = $body;
            $postComment->id_post = $id;
            $postComment->id_user = $this->currentUser['id'];
            $postComment->created_at = new \DateTime();
            $postComment->save();

            $data = [
                'id_user' => $this->currentUser['id'],
                'avatar' => url("media/avatars/{$this->currentUser['avatar']}"),
                'body' => $body,
            ];

            return response()->json($data, 201);
        }

        return response()->json(['errors' => ['Post does not exist.']], 404);
    }
}
