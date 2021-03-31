<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Likes extends Model
{
    use SoftDeletes;
    protected $table = 'likes';
    protected $fillable = [
        'user_id', 'post_id'
    ];
    public $timestamps = true;
    protected $dates = ['deleted_at'];

    public function likePost() {
        return $this->belongsTo('App\Posts', 'id');
    }
}
