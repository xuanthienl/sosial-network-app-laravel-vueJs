<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Follows extends Model
{
    use SoftDeletes;
    protected $table = 'follows';
    protected $fillable = [
        'user_id', 'user_id_follow'
    ];
    public $timestamps = true;
    protected $dates = ['deleted_at'];
}
