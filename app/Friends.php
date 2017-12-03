<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Friends extends Model
{
    protected $table ='friends';
    protected $fillable = [
        'phone_num1',
        'phone_num2',
        'result'
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
