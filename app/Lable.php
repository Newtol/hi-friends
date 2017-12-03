<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lable extends Model
{
    protected $table ='lable_user';
    protected $fillable = [
        'lable_id',
        'phone_num'
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
