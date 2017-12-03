<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});
//登陆
Route::post('/user/login', 'UserController@login');
//注销登陆
Route::post('/user/logout', 'UserController@logout');
//注册
Route::post('/user/register', 'UserController@register');
//获取用户信息
Route::post('/user/info', 'UserController@getInfo');
//更新用户信息
Route::post('/user/update', 'UserController@updateInfo');
//获取hi圈
Route::get('/user/hi/get', 'MomentsController@getContext');
//获取头像
Route::get('/user/image/{sex}', 'UserController@getImgs');
//发布hi圈
Route::post('/user/hi/publish/{phone_num}', 'MomentsController@publishContext');
//点赞hi圈
Route::post('/user/hi/praise', 'MomentsController@praiseContext');
//获取点赞历史
Route::post('/user/hi/praise/history', 'MomentsController@getPraiseHistory');
//获取lable列表
Route::get('user/lable/get','UserController@getLableList');
//随机匹配
Route::get('/user/friends/rank/{phone_num}', 'MatchController@getFrirendByRankdom');
//筛选匹配
Route::post('/user/friends/select/{phone_num}', 'MatchController@getFriendsBySelect');
//回传匹配结果
Route::post('user/friends/result/{phone_num}', 'MatchController@getResult');
//获取邀约历史
Route::post('user/friends/history', 'MatchController@getFriendsHistory');

