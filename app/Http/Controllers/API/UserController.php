<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Validator;
use Exception;
use App\Follows;
use App\Likes;
use App\Comments;
use App\ImagesPost;
use App\ResetPassword;
use Mail;
use App\Mail\mailResetPassword;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cookie;
use GuzzleHttp\Cookie\CookieJar;

class UserController extends Controller
{
    public function login(Request $request) { 
        $validator = Validator::make($request->all(), 
            [
                'username' => 'alpha_dash|max:255',
                'email' => 'required|max:255',
                'password' => 'required',
            ],
            [
                'required' => ':attribute cannot be empty.',
                'alpha_dash' => ':attribute cannot contain spaces.',
            ], 
            [
                'username' => 'Username',
                'email' => 'Email address',
                'password' => 'Password',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 404);
        }

        if(User::where('email', $request->email)->exists()) {
            $user = User::where('email', $request->email)->where('deleted_at', NULL)->first();
            if($user->exists()){
                if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                    $token = Auth::user()->createToken('login')->accessToken;
                    
                    if($user->image_profile != null) {
                        $imagePath = 'public/images/users/'.$user->id.'/image_profile'.'/';
                        if(Storage::disk('local')->exists($imagePath.$user->image_profile)) {
                            $img = base64_encode(Storage::disk('local')->get($imagePath.$user->image_profile));
                            $image_profile = 'data:image/'.pathinfo($user->image_profile)['extension'].';base64,'.$img;
                        } else {
                            $image_profile = '';
                        }
                    } else {
                        $image_profile = '';
                    }
                    $followers = Follows::whereNull('deleted_at')->where('user_id_follow', $user->id)->pluck('user_id');
                    $following = Follows::whereNull('deleted_at')->where('user_id', $user->id)->pluck('user_id_follow');

                    return response()->json([
                        'message' => 'Successful!',
                        'user' => collect($user)->merge(['image_profile' => $image_profile, 'followers' => $followers, 'following' => $following]),
                        'access_token' => $token
                    ], 200);
                } else {
                    return response()->json(['message' => 'Incorrect password.'], 404);
                }
            } else {
                return response()->json(['message' => 'Account has deleted.'], 404);
            }
        } else {
            return response()->json(['message' => 'Email doesn\'t exists.'], 404);
        }
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), 
            [
                'username' => 'required|alpha_dash|unique:users|max:255',
                'email' => 'required|unique:users|max:255',
                'password' => 'required',
            ],
            [
                'required' => ':attribute cannot be empty.',
                'alpha_dash' => ':attribute cannot contain spaces, dots, special characters.',
                'unique' => ':attribute has exists.',
            ], 
            [
                'username' => 'Username',
                'email' => 'Email address',
                'password' => 'Password',
            ]
        );

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 404);
        }

        $user = new User;
        $user->username = trim($request->username);
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        return response()->json(['message' => 'Successful'], 200);
    }

    public function logout(Request $request) {
        if(Auth::user()) {
            $token = $request->user()->token();
            $token->revoke();
            return response()->json(['message' => 'Logout successful.'], 200);
        } else {
            return response()->json(['message' => 'Not logged in.'], 200);
        }
    }

    public function index(Request $request) {
        if(isset($request->id)) {
            $following = Follows::whereNull('deleted_at')->where('user_id', $request->id)->pluck('user_id_follow');
            $users = User::inRandomOrder()->limit(7)->whereNotIn('id', $following)->get();
            return response()->json($users, 200);
        } else {
            return response()->json(['message' => 'error'], 200);
        }
    }

    public function profile(Request $request, $username) {
        $user = User::whereNull('deleted_at')->where('username', $username)->first();
        if($user != null) {
            if($user->image_main != null) {
                $imagePath = 'public/images/users/'.$user->id.'/image_main'.'/';
                if(Storage::disk('local')->exists($imagePath.$user->image_main)) {
                    $img = base64_encode(Storage::disk('local')->get($imagePath.$user->image_main));
                    $image_main = 'data:image/'.pathinfo($user->image_main)['extension'].';base64,'.$img;
                } else {
                    $image_main = null;
                }
            } else {
                $image_main = null;
            }

            if($user->image_profile != null) {
                $imagePath = 'public/images/users/'.$user->id.'/image_profile'.'/';
                if(Storage::disk('local')->exists($imagePath.$user->image_profile)) {
                    $img = base64_encode(Storage::disk('local')->get($imagePath.$user->image_profile));
                    $image_profile = 'data:image/'.pathinfo($user->image_profile)['extension'].';base64,'.$img;
                } else {
                    $image_profile = null;
                }
            } else {
                $image_profile = null;
            }

            $arrPosts = [];
            foreach($user->posts as $post) {
                $likes_post = Likes::whereNull('deleted_at')->where('post_id', $post->id)->pluck('user_id');
                $comments = Comments::whereNull('deleted_at')->where('post_id', $post->id)->orderBy('created_at', 'desc')->get();
                $arrComments = [];
                foreach($comments as $comment) {
                    $likes = $comment->likes;
                    if($likes == null) {
                        $likes = [];
                    }

                    if($comment->reply == null) {
                        $reply = 0;
                    } else {
                        $listReply = [];
                        foreach(array_values($comment->reply) as $replies) {
                            foreach($replies as $reply) {
                                $listReply[] = $reply;
                            }
                        }
                        $reply = count($listReply);
                    }
                    $arrComments[] = collect($comment)->merge(['user' => User::find($comment->user_id), 'likes' => $likes, 'reply' => $reply, 'replies' => [], 'active' => false]);
                }
                $image = ImagesPost::where('post_id', $post->id)->first();
                if($image) {
                    $image = $image->path;
                    $imagePath = 'public/images/posts/'.$user->id.'/';
                    if(Storage::disk('local')->exists($imagePath.$image)) {
                        $img = base64_encode(Storage::disk('local')->get($imagePath.$image));
                        if(pathinfo($image)['extension'] == "pdf") {
                            $path = 'data:application/pdf;base64,'.$img;
                        } else {
                            $path = 'data:image/'.pathinfo($image)['extension'].';base64,'.$img;
                        }
                    } else {
                        $path = "";
                    }
                } else {
                    $path = "";
                }
                $arrPosts[] = collect($post)->merge(['path' => $path, 'likes' => $likes_post, 'comments' => $arrComments]);
            }

            $followers = Follows::whereNull('deleted_at')->where('user_id_follow', $user->id)->pluck('user_id');
            $users_followers = User::whereNull('deleted_at')->whereIn('id', $followers)->get();
            $arrUsersFoffowers = [];
            foreach($users_followers as $user_follow) {
                if($user_follow->image_profile != null) {
                    $imagePath = 'public/images/users/'.$user_follow->id.'/image_profile'.'/';
                    if(Storage::disk('local')->exists($imagePath.$user_follow->image_profile)) {
                        $img = base64_encode(Storage::disk('local')->get($imagePath.$user_follow->image_profile));
                        $imageProfile = 'data:image/'.pathinfo($user_follow->image_profile)['extension'].';base64,'.$img;
                    } else {
                        $imageProfile = '';
                    }
                } else {
                    $imageProfile = '';
                }
                $arrUsersFoffowers[] = collect($user_follow)->merge(['image_profile' => $imageProfile]);                
            }
            
            $following = Follows::whereNull('deleted_at')->where('user_id', $user->id)->pluck('user_id_follow');
            $users_following = User::whereNull('deleted_at')->whereIn('id', $following)->get();
            $arrUsersFoffowing = [];
            foreach($users_following as $user_following) {
                if($user_following->image_profile != null) {
                    $imagePath = 'public/images/users/'.$user_following->id.'/image_profile'.'/';
                    if(Storage::disk('local')->exists($imagePath.$user_following->image_profile)) {
                        $img = base64_encode(Storage::disk('local')->get($imagePath.$user_following->image_profile));
                        $imageProfile = 'data:image/'.pathinfo($user_following->image_profile)['extension'].';base64,'.$img;
                    } else {
                        $imageProfile = '';
                    }
                } else {
                    $imageProfile = '';
                }
                $arrUsersFoffowing[] = collect($user_following)->merge(['image_profile' => $imageProfile]);                
            }

            return response()->json([
                'user' => collect($user)->merge(['image_main' => $image_main, 'image_profile' => $image_profile]),
                'posts' => $arrPosts,
                'followers' => $arrUsersFoffowers,
                'following' => $arrUsersFoffowing,
            ], 200);
        }
        return response()->json(['message' => 'Not Found.'], 404);
    }
    public function profileUpdate(Request $request, $id) {
        $request = $request->data;

        $user = User::find($id);
        if($request['old_password'] != '') {
            if(!Hash::check($request['old_password'], $user->password)) {
                return response()->json(['message' => 'Old password is incorrect.'], 404);
            }
        }

        $validator = Validator::make($request, 
            [
                'username' => 'required|alpha_dash|unique:users,username,'.$id.',id,deleted_at,NULL|max:100',
                'email' => 'required|unique:users,email,'.$id.',id,deleted_at,NULL|max:100',
                'password' => 'nullable|confirmed|min:6'
            ],
            [
                'required' => ':attribute cannot be empty.',
                'alpha_dash' => ':attribute cannot contain spaces, dots, special characters.',
                'unique' => ':attribute has exists.',
                'confirmed' => ':attribute confirmation does not match.'
            ],
            [
                'username' => 'Username',
                'email' => 'Email address',
                'password' => 'Password',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 404);
        }

        $user->name = $request['name'];
        $user->username = trim($request['username']);
        $user->email = $request['email'];
        $user->address = $request['address'];
        $user->birth_date = $request['birth_date'];
        $user->description = $request['description'];
        $user->gender = $request['gender'];
        $user->phone_number = $request['phone_number'];
        if($request['password'] != '') {
            $user->password = bcrypt($request['password']);
        };
        
        $image_main = $request['image_main'];
        if (preg_match('/^data:image\/(\w+);base64,/', $image_main)) {
            //delete
            $imagePath = 'public/images/users/'.$id.'/image_main'.'/';
            if($user->image_main != null) {
                if(Storage::disk('local')->exists($imagePath.$user->image_main)) {
                    Storage::disk('local')->delete($imagePath.$user->image_main);
                }
            }

            $extension = explode('/', mime_content_type($image_main))[1];
            $imageName = time().'.'. $extension;
            $img = Image::make(base64_decode(preg_replace('/^data:image\/(\w+);base64,/', '',$image_main)));
            if ($img->width() >= $img->height() && $img->width() > 2048) {
                $img->resize(2048, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else if($img->height() > $img->width() && $img->height() > 2048){
                $img->resize(null, 2048, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } //resize 2048

            Storage::disk('local')->put($imagePath.$imageName, $img->stream());
            $user->image_main = $imageName;
        }

        $image_profile = $request['image_profile'];
        if (preg_match('/^data:image\/(\w+);base64,/', $image_profile)) {
            //delete
            $imagePath = 'public/images/users/'.$id.'/image_profile'.'/';
            if($user->image_main != null) {
                if(Storage::disk('local')->exists($imagePath.$user->image_profile)) {
                    Storage::disk('local')->delete($imagePath.$user->image_profile);
                }
            }

            $extension = explode('/', mime_content_type($image_profile))[1];
            $imageName = time().'.'. $extension;
            $img = Image::make(base64_decode(preg_replace('/^data:image\/(\w+);base64,/', '',$image_profile)));
            if ($img->width() >= $img->height() && $img->width() > 2048) {
                $img->resize(2048, null, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } else if($img->height() > $img->width() && $img->height() > 2048){
                $img->resize(null, 2048, function ($constraint) {
                    $constraint->aspectRatio();
                });
            } //resize 2048

            Storage::disk('local')->put($imagePath.$imageName, $img->stream());
            $user->image_profile = $imageName;
        }

        $followers = Follows::whereNull('deleted_at')->where('user_id_follow', $id)->pluck('user_id');
        $following = Follows::whereNull('deleted_at')->where('user_id', $id)->pluck('user_id_follow');
        
        $user->save();
        $user = collect($user)->merge(['followers' => $followers, 'following' => $following]);
        return response()->json($user, 200);
    }

    public function follow(Request $request) {
        if(isset($request->user_id) && isset($request->id)) {
            $follow = new Follows;
            $follow->user_id = $request->user_id;
            $follow->user_id_follow = $request->id;
            $follow->save();
            return response()->json(['success' => true], 200);   
        } else {
            return response()->json(['failed' => true], 404);
        }
    }
    public function unfollow(Request $request) {
        if(isset($request->user_id) && isset($request->id)) {
            $unfollow = Follows::where('user_id', $request->user_id)->where('user_id_follow', $request->id)->forceDelete();
            return response()->json(['success' => true], 200);   
        } else {
            return response()->json(['failed' => true], 404);
        }
    }

    public function user() {
        return response()->json(auth()->user());  
    }

    //Reset Password, check validate mail and send email
    public function resetPassword(Request $request) {
        $validator = Validator::make($request->all(), [
        	'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => 'Invalid email'], 404);
        }
        $user = User::where('email', $request->email)->first();
        if($user) {
            $password = Str::random(8);
            $user->password = bcrypt($password);
            $user->save();
            Mail::to($request->email)->send(new mailResetPassword($user->username, $password));
            return response()->json([
                'message' => 'success'
            ], 200);
        }
        return response()->json(['message' => 'Email doesn\'t exists.'], 404);
    }

    // public function resetPassword(Request $request) {
    //     $validator = Validator::make($request->all(), [
    //     	'email' => 'required|email',
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(['message' => 'Invalid email'], 404);
    //     }

    //     $user = User::where('email', $request->email)->first();
    //     if($user) {
    //         do {
    //             $token = Str::random(60);
    //         } while(ResetPassword::where('token', $token)->exists());
            
    //         $passwordReset = ResetPassword::updateOrCreate(
    //             ['email' => $request->email,
    //             'token'=> $token]
    //         );
        
    //         if ($passwordReset) {
    //             $link = url('password/reset/?token=' . $token);
    //             Mail::to($request->email)->send(new mailResetPassword($link, $request->email));
    //             return response()->json([
    //                 'token' => $token,
    //                 'mail_user' => $user->email
    //             ], 200);
    //         }
    //     }
    //     else {            
    //         return response()->json(['message' => 'Email doesn\'t exists.'], 404);
    //     }
    // }

    //check url token
    // public function checkToken(Request $request) {
    //     $token = ResetPassword::where('token', $request->token)->first();
    // 	if($token){
    //         if (Carbon::parse($token->updated_at)->addMinutes(30)->isPast()) {
    //             ResetPassword::where('token', $request->token)->delete();
    //         } else {
    //             dd('ok');
    //         } 
    //     } 
    // 	dd('falied');
    // }

    // //create new password
    // public function updatePassword(Request $request) {
    //     $validator = Validator::make($request->all(), [
    //         'password' => [ 
    //             'confirmed',
    //             'min:8',     
    //             'regex:/[a-z]/',
    //             'regex:/[0-9]/',
    //         ]
    //     ]);
    //     if ($validator->fails()) {
    //         return response()->json(["errors" => $validator->errors()], 404);
    //     } else {
    //         $result = ResetPassword::where('token', $request->token)->first();

    //         User::where('email', $result->email)->where('deleted_at', NULL)->where(function ($query) {
    //                     $query->where('delete_flg',  0)
    //                         ->orWhereNull('delete_flg');
    //                 })->update(['password'=>bcrypt($request->password)]);
            
    //         $role_id = 1;
    //         if(User::where('email', $result->email)->where('deleted_at', NULL)->where(function ($query) {
    //                     $query->where('delete_flg',  0)
    //                         ->orWhereNull('delete_flg');
    //                 })->whereHas('roles', function ($query) {
    //                     $query->where('mst_role.role_id', 2);
    //                 })->exists()){
    //             $role_id = 2;
    //         } else {
    //             $role_id = 3;
    //         }

    //         ResetPassword::where('token', $request->token)->delete();

    //         return response()->json([
    //             "success" => true,
    //             "role_id" => $role_id
    //         ], 200);
    //     }
    // }

    public function search(Request $request) {
        $keyword = $request->keyword;
        if(isset($keyword) && strlen($keyword) > 0){
            $user = User::whereNull('deleted_at')
            ->where(function($query) use ($keyword){
                $query->where('username', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('address', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('phone_number', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('email', 'LIKE', '%' . $keyword . '%')
                        ->orWhere('name', 'LIKE', '%' . $keyword . '%');
            })->select('id', 'username', 'image_profile', 'name')->get();
            return response()->json($user, 200);
        }
        return response()->json(['message' => 'keyword not defined.'], 404);
    }

    public function music(Request $request) {
        // $cookieJar = \GuzzleHttp\Cookie\CookieJar::fromArray([
        //     'zmp3_rqid_lagecy' => 'MHw0OS4xNTYdUngNTMdUngMTEzfG51WeBGx8MTYxNDmUsIC0NDU1NTE4NQ',
        //     'zmp3_rqid' => 'MHw0OS4xNTYdUngNTMdUngMTEzfHYxLjAdUngMjV8MTYxNDmUsIC0NDU1NTI2Mw',
        //     'zmp3_app_version.1' => '1025'
        // ], '.zingmp3.vn');
        // $url = 'https://zingmp3.vn/api/song/get-song-info?id=ZOW0OBU8&ctime=160718421&sig=716b083eea082f38c8eb2ad5aa1023120199bd906a30a6dd533c4987ba473a7eeb0e2b58c5a8d7c69a563bffb4648ad1762fff78298d1c043f0c542d3c92ee68&api_key=38e8643fb0dc04e8d65b99994d3dafff';
        // $client = new \GuzzleHttp\Client();
        // $request = $client->get($url, [
        //     'cookies' => $cookieJar
        // ]);
        // return response()->json(json_decode($request->getBody()->getContents()), 200);

        $response = Http::get('https://mp3.zing.vn/xhr/chart-realtime?songId=0&videoId=0&albumId=0&chart=song&time=-1');
        return response()->json($response->json(), 200);
    }
}