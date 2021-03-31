<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ResetPassword extends Model
{
    use SoftDeletes;
    protected $table = 'password_resets';
    protected $fillable = [
        'email', 'token'
    ];
    public $timestamps = true;
    protected $dates = ['deleted_at'];
}
