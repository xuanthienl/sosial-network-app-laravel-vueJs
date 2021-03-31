<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comments extends Model
{
    use SoftDeletes;
    protected $table = 'comments';
    protected $fillable = [
        'user_id', 'post_id', 'comment', 'likes', 'reply'
    ];
    public $timestamps = true;
    protected $dates = ['deleted_at'];

    protected $casts = [
        'likes' => 'array',
        'reply' => 'array',
    ];

    public function commentPost() {
        return $this->belongsTo('App\Posts', 'id');
    }
}
