<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostComment;
use App\Models\PostLike;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class FeedController extends Controller
{
    private $currentUser;

    const PER_PAGE = 2;

    /**
     * FeedController constructor.
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
    public function create(Request $request)
    {
        $data = $request->only(['type', 'body', 'photo']);

        $validator = Validator::make($data, [
            'type' => ['required', 'string'],
            'body' => ['string'],
            'photo' => ['mimetypes:image/jpeg,image/jpg,image/png'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $type = $data['type'];
        $body = $data['body'] ?? null;
        $photo = $data['photo'] ?? null;
        $returnArray = [];

        switch ($type) {
            case 'text':
                if (!$body) {
                    $returnArray['errors']['body'] = 'Unsent text.';
                }

                break;
            case 'photo':
                if ($photo) {
                    $filename = md5(time().rand(0, 9999)) . '.jpg';
                    $path = public_path('/media/uploads');

                    Image::make($photo->path())
                        ->resize(800, null, function($constraint) {
                            $constraint->aspectRatio();
                        })
                        ->save("{$path}/{$filename}");

                    $body = $filename;
                } else {
                    $returnArray['errors']['photo'] = 'Unsent photo.';
                }

                break;
            default:
                $returnArray['errors']['type'] = 'Wrong type. Valid are: "text" and "photo"';
                break;
        }

        if (isset($returnArray['errors'])) {
            return response()->json($returnArray, 400);
        }

        $post = new Post();
        $post->id_user = $this->currentUser->id;
        $post->type = $type;
        $post->created_at = date('Y-m-d H:i:s');
        $post->body = $body;
        $post->save();

        return response()->json($post, 201);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function read(Request $request)
    {
        $page = intval($request->get('page', 1));
        $response = [];
        $users = [];

        $relatedUsers = UserRelation::where('user_from', $this->currentUser['id'])->get();

        foreach ($relatedUsers as $relatedUser) {
            $users[] = $relatedUser['user_to'];
        }

        $users[] = $this->currentUser['id'];

        $posts = Post::whereIn('id_user', $users)
            ->orderBy('created_at', 'desc')
            ->offset(($page - 1) * self::PER_PAGE)
            ->limit(self::PER_PAGE)
            ->get();

        $posts = $this->postListToObject($posts, $this->currentUser['id']);

        $totalPosts = Post::whereIn('id_user', $users)->count();
        $pageCount = ceil($totalPosts / self::PER_PAGE);

        $response['posts'] = $posts;
        $response['pageCount'] = $pageCount;
        $response['currentPage'] = $page;

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @param null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function userFeed(Request $request, $id = null)
    {
        $id = $id ?? $this->currentUser->id;
        $page = intval($request->get('page', 1));

        $posts = Post::where('id_user', $id)
            ->orderBy('created_at', 'desc')
            ->offset(($page - 1) * self::PER_PAGE)
            ->limit(self::PER_PAGE)
            ->get();

        $posts = $this->postListToObject($posts, $this->currentUser['id']);

        $totalPosts = Post::where('id_user', $id)->count();
        $pageCount = ceil($totalPosts / self::PER_PAGE);

        $response['posts'] = $posts;
        $response['pageCount'] = $pageCount;
        $response['currentPage'] = $page;

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @param null $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function userPhotos(Request $request, $id = null)
    {
        $id = $id ?? $this->currentUser->id;
        $page = intval($request->get('page', 1));

        $posts = Post::where('id_user', $id)
            ->where('type', 'photo')
            ->orderBy('created_at', 'desc')
            ->offset(($page - 1) * self::PER_PAGE)
            ->limit(self::PER_PAGE)
            ->get();

        $posts = $this->postListToObject($posts, $this->currentUser['id']);

        $totalPosts = Post::where('id_user', $id)->where('type', 'photo')->count();
        $pageCount = ceil($totalPosts / self::PER_PAGE);

        foreach ($posts as $key => $post) {
            $posts[$key]['body'] = url("media/uploads/{$posts[$key]['body']}");
        }

        $response['posts'] = $posts;
        $response['pageCount'] = $pageCount;
        $response['currentPage'] = $page;

        return response()->json($response);
    }

    /**
     * @param $posts
     * @param $currentUserId
     * @return mixed
     */
    private function postListToObject($posts, $currentUserId)
    {
        foreach ($posts as $key => $post) {
            $posts[$key]['mine'] = $post['id_user'] === $currentUserId;
            $posts[$key]['user'] = User::find($post['id_user']);

            $likes = PostLike::where('id_post', $post['id'])->count();
            $isLiked = (bool)PostLike::where('id_post', $post['id'])
                ->where('id_user', $currentUserId)
                ->count();

            $posts[$key]['likeCount'] = $likes;
            $posts[$key]['liked'] = $isLiked;

            $comments = PostComment::where('id_post', $post['id'])->get();

            foreach ($comments as $cKey => $comment) {
                $comments[$cKey]['user'] = User::find($comment['id_user']);
            }

            $posts[$key]['comments'] = $comments;
        }

        return $posts;
    }
}
