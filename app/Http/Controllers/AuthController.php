<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\User;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UseValidateRequest;
use App\Http\Requests\UpdateUser;
use App\Http\Resources\Post as PostResource;
use App\Post;
use App\Http\Requests\PostRequest;
use App\PostFile;


class AuthController extends Controller
{
    /**
     * Create user
     *
     * @param  [string] name
     * @param  [string] email
     * @param  [string] password
     * @param  [string] password_confirmation
     * @return [string] message
     */
    public function signup(UseValidateRequest $request)
    {
        // $request->validate([
        //     'name' => 'required|string',
        //     'email' => 'required|string|email|unique:users',
        //     'password' => 'required|string|confirmed'
        // ]);
        // if ($request->hasFile('avatar')) {
        //     $path = $request->file('avatar')->store('avatars');
        // } else {
        //     $path = Storage::url('avatar.jpg');
        // }
        // $request['pictur']=$path;
        $user = new User([
            'full_name' => $request->full_name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'username' => $request->username,
            'email' => $request->email,
            'role' => $request->role,
            'password' => bcrypt($request->password),
        ]);
        $user->save();
        $tokenResult = $user->createToken('Personal Access Token');
        // $token = $tokenResult->token;
        return response()->json([
            'message' => 'Successfully created user!',
            'access_token' => $tokenResult->accessToken,
            'role' => $user->role,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     * @param  [boolean] remember_me
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);
        $credentials = request(['email', 'password']);
        if (!Auth::attempt($credentials))
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        $user = $request->user();
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;
        $token->save();
        return response()->json([
            'access_token' => $tokenResult->accessToken,
            'role' => $user->role,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }
    public function update(UpdateUser $request)
    {
        $url = null;
        $user = User::where('id', $request->id)->first();
        if ($request->hasFile('pictur')) {
            $path = $request->file('pictur')->store('public');
            $url = Storage::url($path);
        }
        $user->first_name = $request->first_name;
        $user->full_name = $request->full_name;
        $user->last_name = $request->last_name;
        $user->username = $request->username;
        if ($url != null) {
            $user->pictur = $url;
        }
        $user->save();

        return
            response()->json(['user' => $user, 'message' => "user updated successfully"], 200);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {

        return response()->json([
            'user' => $request->user()
        ]);
    }
    public function getSpecificUser(Request $request){
        $user = User::where('id', $request->id)->first();
        return response()->json([
            'user' => $user
        ]);
    }
    public function currentUsrPosts(Request $request)
    {
        $userId = $request->userId;
        $posts = Post::where('user_id', $userId)->orderBy('created_at', 'desc')->paginate(10);
        return PostResource::collection($posts);
    }

    public function currentUsrFeaturedPosts(Request $request)
    {
        $userId = $request->userId;
        $posts = Post::where('user_id', $userId)->where('isFeatured',true)->orderBy('created_at', 'desc')->take(3)->get();
        return PostResource::collection($posts);
    }

    public function writer(Request $request)
    {
        // username , image , description
        // accept request and send back all writer data
        // writer data includes { posts , events , books }
    }
}
