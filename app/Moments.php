<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Moments extends Model
{
    protected $table ='content_user';
    protected $fillable = [
        'content',
        'phone_num',
        'nickname'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'created_at',
        'updated_at'
    ];
}
