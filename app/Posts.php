<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Posts extends Model
{
    use SoftDeletes;
    protected $table = 'posts';
    protected $fillable = [
        'user_id', 'content', 'status'
    ];
    public $timestamps = true;
    protected $dates = ['deleted_at'];

    public function user() {
        return $this->belongsTo('App\User', 'id');
    }
    public function images_post() {
        return $this->hasMany('App\ImagesPost', 'post_id');
    }
    public function commentsPost() {
        return $this->hasMany('App\Comments', 'post_id');
    }
    public function likesPost() {
        return $this->hasMany('App\Likes', 'post_id');
    }
}
