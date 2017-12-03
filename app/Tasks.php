<?php
/**
 * Created by PhpStorm.
 * User: ASUS
 * Date: 2017/11/23
 * Time: 0:45
 */

namespace App;


use App\Http\Middleware\Authenticate;
use Illuminate\Database\Eloquent\Model;

class Tasks extends Model
{
    protected $table ='tasks';
    protected $fillable = [
        'task',
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