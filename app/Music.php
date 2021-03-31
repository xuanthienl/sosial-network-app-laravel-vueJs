<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Music extends Model
{
    use SoftDeletes;
    protected $table = 'music';
    protected $fillable = [
        'id', 'title', 'artists', 'type', 'playlist_id', 'thumbnail', 'song', 'authors'
    ];
    public $timestamps = true;
    protected $dates = ['deleted_at'];
}
