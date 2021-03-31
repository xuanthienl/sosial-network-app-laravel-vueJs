<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\User;
use App\Posts;
use App\ImagesPost;
use App\Likes;
use App\Comments;

class PostsController extends Controller
{
    public function index() {
        $posts = Posts::inRandomOrder()->limit(50)->paginate(50);
        $arr = [];
        foreach($posts as $post) {
            $user = User::where('id', $post->user_id)->first();
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
                $imagePath = 'public/images/posts/'.$post->user_id.'/';
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

            $arr[] = collect($post)->merge(['username' => $user->username, 'image_profile' => $image_profile, 'path' => $path, 'likes' => $likes_post, 'comments' => $arrComments]);
        }
        return response()->json([
            'success' => true,
            'posts' => $arr
        ], 200);
    }

    public function store(Request $request) {
        if($request->content == '' && $request->image == '') {
            return response()->json([
                'message' => 'Bài đăng này không có nội dung !',
            ], 404);
        }
        $post = new Posts;
        $post->user_id = $request->user_id;
        $post->content = $request->content;
        if($post->save()) {
            if($request->image != '' || $request->image != null) {
                //delete
                $images = ImagesPost::where('post_id', $post->id)->get();
                $imagePath = 'public/images/posts/'.$request->user_id.'/';
                foreach($images as $image) {
                    if(Storage::disk('local')->exists($imagePath.$image->path)) {
                        Storage::disk('local')->delete($imagePath.$image->path);
                    }
                }

                //update
                $images_post = new ImagesPost;
                $images_post->post_id = $post->id;
                if (preg_match('/^data:image\/(\w+);base64,/', $request->image)) {
                    $extension = explode('/', mime_content_type($request->image))[1];
                    $imageName = time().'.'. $extension;
                    $img = Image::make(base64_decode(preg_replace('/^data:image\/(\w+);base64,/', '',$request->image)));
                    if ($img->width() >= $img->height() && $img->width() > 2048) {
                        $img->resize(2048, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else if($img->height() > $img->width() && $img->height() > 2048){
                        $img->resize(null, 2048, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } //resize 2048

                    $imagePath = 'public/images/posts/'.$request->user_id.'/';
                    Storage::disk('local')->put($imagePath.$imageName, $img->stream());
                    $images_post->path = $imageName;
                    $images_post->save();
                }
            }
        }
        return response()->json([
            'success' => true,
            'id' => $post->id
        ], 200);
    }

    public function update(Request $request, $id) {
        if($request->content == null && $request->image == null) {
            Posts::destroy($id);
            return response()->json([
                'deleted' => true,
            ], 200);
        }
        $post = Posts::find($id);
        $post->content = $request->content;
        $post->save();

        if($request->image != null) {
            $images_post = ImagesPost::whereNull('deleted_at')->where('post_id', $id)->first();
            if($images_post != null) {
                //delete
                $images = ImagesPost::where('post_id', $id)->get();
                $imagePath = 'public/images/posts/'.$post->user_id.'/';
                foreach($images as $image) {
                    if(Storage::disk('local')->exists($imagePath.$image->path)) {
                        Storage::disk('local')->delete($imagePath.$image->path);
                    }
                }
                //update
                if (preg_match('/^data:image\/(\w+);base64,/', $request->image)) {
                    $extension = explode('/', mime_content_type($request->image))[1];
                    $imageName = time().'.'. $extension;
                    $img = Image::make(base64_decode(preg_replace('/^data:image\/(\w+);base64,/', '',$request->image)));
                    if ($img->width() >= $img->height() && $img->width() > 2048) {
                        $img->resize(2048, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else if($img->height() > $img->width() && $img->height() > 2048){
                        $img->resize(null, 2048, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } //resize 2048
    
                    $imagePath = 'public/images/posts/'.$post->user_id.'/';
                    Storage::disk('local')->put($imagePath.$imageName, $img->stream());
                    $images_post->path = $imageName;
                    $images_post->save();
                } else {
                    $images_post->forceDelete();
                }
            } else {
                if (preg_match('/^data:image\/(\w+);base64,/', $request->image)) {
                    $images_post = new ImagesPost;
                    $images_post->post_id = $id;

                    $extension = explode('/', mime_content_type($request->image))[1];
                    $imageName = time().'.'. $extension;
                    $img = Image::make(base64_decode(preg_replace('/^data:image\/(\w+);base64,/', '',$request->image)));
                    if ($img->width() >= $img->height() && $img->width() > 2048) {
                        $img->resize(2048, null, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } else if($img->height() > $img->width() && $img->height() > 2048){
                        $img->resize(null, 2048, function ($constraint) {
                            $constraint->aspectRatio();
                        });
                    } //resize 2048

                    $imagePath = 'public/images/posts/'.$post->user_id.'/';
                    Storage::disk('local')->put($imagePath.$imageName, $img->stream());

                    $images_post->path = $imageName;
                    $images_post->save();
                }
            }
        } else {
            $images_post = ImagesPost::whereNull('deleted_at')->where('post_id', $id)->first();
            if($images_post != null) {
                //delete
                $images = ImagesPost::where('post_id', $id)->get();
                $imagePath = 'public/images/posts/'.$post->user_id.'/';
                foreach($images as $image) {
                    if(Storage::disk('local')->exists($imagePath.$image->path)) {
                        Storage::disk('local')->delete($imagePath.$image->path);
                    }
                }
                $images_post->forceDelete();
            }
        }
        
        return response()->json([
            'success' => true,
        ], 200);
    }

    public function show($id) {
        $post = Posts::find($id);
        if($post != null) {
            $user = User::where('id', $post->user_id)->first();
            $likes_post = Likes::whereNull('deleted_at')->where('post_id', $post->id)->pluck('user_id');
            $comments = Comments::whereNull('deleted_at')->where('post_id', $post->id)->select('id','user_id', 'comment', 'likes', 'reply', 'created_at')->orderBy('created_at', 'desc')->get();
            $arrComments = [];
            foreach($comments as $comment) {
                $likes = $comment->likes;
                if($likes == null) {
                    $likes = [];
                }
                if($comment->reply == null) {
                    $reply = 0;
                } else {
                    $arr = [];
                    foreach(array_values($comment->reply) as $replies) {
                        foreach($replies as $reply) {
                            $arr[] = $reply;
                        }
                    }
                    $reply = count($arr);
                }
                $arrComments[] = collect($comment)->merge(['user' => User::find($comment->user_id), 'likes' => $likes, 'reply' => $reply, 'replies' => [], 'active' => false]);
            }
            $image = ImagesPost::where('post_id', $post->id)->first();
            if($image) {
                $image = $image->path;
                $imagePath = 'public/images/posts/'.$post->user_id.'/';
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

            $post = collect($post)->merge(['username' => $user->username, 'path' => $path, 'likes' => $likes_post, 'comments' => $arrComments, 'image_profile' => $image_profile]);
            return response()->json($post, 200);
        }
        return response()->json(['error' => 'not Found'],404);
    }

    public function destroy(Request $request, Posts $posts) {
        $images = ImagesPost::whereNull('deleted_at')->where('post_id', $request->id)->get();
        $imagePath = 'public/images/posts/'.Posts::find($request->id)->user_id.'/';
        foreach($images as $image) {
            if(Storage::disk('local')->exists($imagePath.$image->path)) {
                Storage::disk('local')->delete($imagePath.$image->path);
                $image->delete();
            }
        }
        Comments::whereNull('deleted_at')->where('post_id', $request->id)->delete();
        Likes::whereNull('deleted_at')->where('post_id', $request->id)->delete();

        Posts::destroy($request->id);
        return response()->json([
            'success' => true,
        ], 200);
    }

    public function likes(Request $request) {
        if(isset($request->user_id) && isset($request->post_id)) {
            $like = Likes::whereNull('deleted_at')->where('user_id', $request->user_id)->where('post_id', $request->post_id)->first();
            if($like != null) {
                $like->forceDelete();
                return response()->json(['dislikes' => true], 200);
            }
            $likes = new Likes;
            $likes->user_id = $request->user_id;
            $likes->post_id = $request->post_id;
            $likes->save();
            return response()->json(['likes' => true], 200);
        } else {
            return response()->json(['failed' => true], 404);
        }
    }
    public function comment(Request $request) {
        if(isset($request->user_id) && isset($request->post_id) && isset($request->comment)) {
            $comment = new Comments;
            $comment->user_id = $request->user_id;
            $comment->post_id = $request->post_id;
            $comment->comment = $request->comment;
            $comment->save();
            return response()->json([
                'user' => User::find($request->user_id),
                'comment_id' => $comment->id
            ], 200);
        } else {
            return response()->json(['failed' => true], 404);
        }
    }
    public function deleteComment(Request $request) {
        if($request->id != '' || $request->id != null) {
            $comment = Comments::find($request->id)->forceDelete();
            return response()->json(['success' => true], 200);
        } else {
            return response()->json(['failed' => true], 404);
        }
    }
    public function likesComment(Request $request, $id) {
        $comment = Comments::find($id);
        $options = $comment->likes; //đăt $options = $comment->likes
        if(is_array($options) && in_array($request->id, $options)) {
            $auth_id = $request->id;
            $newArr = array_filter($options, function ($likes) use ($auth_id) {
                if($likes != $auth_id) {
                    return $likes;
                }
            });
            $comment->likes = array_values($newArr);
            $comment->save();
            return response()->json(['dislikes' => true], 200);
        } else {
            $options[] = $request->id; //inport vào mảng
            $comment->likes = array_values($options); //gán lại giá trị cho $comment->likes
            $comment->save();
            return response()->json(['likes' => true], 200);
        }
    }
    public function replyComment(Request $request, $id) {
        $comment = Comments::find($id);
        $options = $comment->reply;
        if(!empty($options) && array_key_exists($request->id, $options)) {
            $arr = $options[$request->id];
            $arr[] = $request->text;
            $options[$request->id] = $arr;
        } else {
            $options[$request->id] = (array)$request->text;
        }
        $comment->reply = $options;
        $comment->save();
        return response()->json($comment, 200);
    }
    public function getReply(Request $request, $id) {
        $comment = Comments::find($id);
        if($comment->reply != null) {
            $arrReply = [];
            $arrKey = array_keys($comment->reply);
            $arrValue = array_values($comment->reply);
            $keys = array_keys($arrValue);
            foreach($keys as $key) {
                foreach($arrValue[$key] as $reply) {
                    $user_id_reply = $arrKey[$key];
                    $arrReply[] =  [
                        'user' => User::find($user_id_reply),
                        'text' => $reply
                    ];
                }
            }
            return response()->json($arrReply, 200);
        }
        return response()->json('empty', 200);
    }
}
