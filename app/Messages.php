<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Messages extends Model
{
    use SoftDeletes;
    protected $table = 'messages';
    protected $fillable = [
        'from', 'to', 'text', 'read'
    ];
    public $timestamps = true;
    protected $dates = ['deleted_at'];

    public function fromContact() {
        return $this->hasOne(User::class, 'id', 'from');
    }
}
