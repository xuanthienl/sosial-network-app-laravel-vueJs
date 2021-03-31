<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImagesPost extends Model
{
    use SoftDeletes;
    protected $table = 'images_posts';
    protected $fillable = [
        'post_id', 'path'
    ];
    public $timestamps = true;
    protected $dates = ['deleted_at'];

    public function post() {
        return $this->belongsTo('App\Posts', 'id');
    }
}
