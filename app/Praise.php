<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Praise extends Model
{
    protected $table ='content_praise';
    protected $fillable = [
        'content_id',
        'phone_num',
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
