<?php

namespace App\Http\Controllers;

use App\Lable;
use Illuminate\Support\Facades\Session;
use App\Tasks;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests;
use Auth;
use Validator;

class UserController extends Controller
{

    //注册
    public function register(Request $request){
        $user=\Request::all();
        $validator = Validator::make($request->all(), [
            'phone_num' => 'required|alpha_num',
            'sex'=>'required|digits:1',
            'password' => 'required|alpha_num',
            'task'=>'required',
            'introduce'=>'required'
        ]);

        if ($validator->fails())
            return response()->json(['status' => 422, 'message' => 'Unprocessable Entity'], 422);
        $timestamps=time();
        $phone_num=$request->phone_num;
        $token=$this->myEncrypt($timestamps.$phone_num.'hi-friends');
        $la=$request->lable_id;
        $lable=json_decode($la);


        if(!$lable){
            return response()->json(['status' => 422, 'message' => 'lable empty'], 422);
        }else{
            for($i=0;$i<count($lable);$i++){
                $rea=Lable::create([
                'lable_id'=>$lable[$i],
                'phone_num'=>$request->phone_num
                ]);

            }
            if(!$rea){
                return response()->json(['status' => 422, 'message' => 'lable error'], 422);
            }
        }

        DB::beginTransaction();
        try{
            $task=Tasks::create([
                'task'=>$request->task,
                'phone_num'=>$request->phone_num
            ]);
            $task_id=$task->id;

            $pwd=$request->password;
            $password=$this->myEncrypt($pwd);
            $re=User::create([
                'nickname'=>$request->nickname,
                'phone_num'=>$request->phone_num,
                'password'=>$password,
                'sex'=>$request->sex,
                'task_id'=>$task_id,
                'qq'=>$request->qq,
                'introduce'=>$request->introduce,
                'remember_token'=>$token
            ]);
            DB::commit();
        }catch (\Exception $e) {
            DB::rollBack();
        }
        if(!$re){
            return response()->json(['status' => 400, 'message' => 'Bad Request'], 400);
        }
        $data=([
            'nickname'=>$request->nickname,
            'phone_num'=>$request->phone_num,
            'sex'=>$request->sex,
            'remember_token'=>$token
        ]);
        return response()->json(['status' => 200, 'message' => 'OK','data'=>$data]);

    }
    //信息加密
    private function myEncrypt($pwd){
        $str=md5($pwd."hi-friends");
        return base64_encode($str);
    }
    //登陆
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'phone_num' => 'required|alpha_num',
            'password' => 'required',
        ]);
        if ($validator->fails())
            return response()->json(['status' => 422, 'message' => 'Unprocessable Entity'], 422);

        // 初始化传入的参数
        $phone_num = $request->phone_num;
        $password  = $request->password;
        $pwd = $this->myEncrypt($password);


        $user=$this->getlogin($phone_num,$pwd);
        Session::put($phone_num,$user);
        if($user){
            return response()->json(['status' => 200, 'message' => 'OK', 'data' => $user]);
        }else{
            return response()->json(['status' => 400, 'message' => 'Bad Request']);
        }
    }
    //验证登陆信息
    private  function getlogin($phone_num,$password){
        $user=DB::table('users')->where('phone_num',$phone_num)->where('password',$password)->get();
        $userinfo=$this->getAuth($user);
        return $userinfo;

    }
    //登陆得到用户信息
    private function getAuth($user)
    {
        if($user) {
            $model = ([
                'phone_num' => $user[0]->phone_num,
                'sex' => $user[0]->sex,
                'token' => $user[0]->remember_token,
                'nickname' => $user[0]->nickname
            ]);
        }else{
            $model=null;
        }
        return $model;
    }
    //登出
    public function logout(Request $request)
    {
        $request->session()->forget('0');
        return response()->json(['status' => 200, 'message' => 'ok']);

    }
    //更新用户信息
    public function updateInfo(Request $request){
        //验证是否登陆
        $re= $request->session()->has('0');
        if(!$re){
            return response()->json(['status' => 400, 'message' => 'Bad request'],400);
        }else {
            $validator = Validator::make($request->all(), [
                'sex' => 'required|digits:1',
                'task' => 'required',
                'introduce' => 'required'
            ]);
            if ($validator->fails())
                return response()->json(['status' => 422, 'message' => 'Unprocessable Entity'], 422);
            $phone_num = $request->phone_num;
            $token = $request ->token;
            $rec = $this->isUser($phone_num);
            if(!$rec){
                return response()->json(['status' => 400, 'message' => 'Is not user'],402);
            }
            $user=DB::table('users')->where('phone_num',$phone_num)->where('remember_token',$token)->get();
            if(!$user){
                return response()->json(['status' => 401, 'message' => 'Illegal Update']);
            }else {
                $task = Tasks::create([
                    'task' => $request->task,
                    'phone_num' => $request->phone_num
                ]);
                $task_id = $task->id;
                $user = ([
                    'nickname' => $request->nickname,
                    'sex' => $request->sex,
                    'task_id' => $task_id,
                    'qq' => $request->qq,
                    'introduce' => $request->introduce,
                ]);
            }
            $rea = DB::table('users')->where('phone_num', $phone_num)->update($user);
            if ($rea) {
                return response()->json(['status' => 200, 'message' => 'OK']);
            } else {
                return response()->json(['status' => 400, 'message' => 'Bad request'], 400);
            }
        }
    }
    //获取用户信息
    public function getInfo(Request $request){
        $user=\Request::all();
        //验证数据是否合理
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'phone_num'=>'required|alpha_num'
        ]);
        if ($validator->fails())
            return response()->json(['status' => 422, 'message' => 'Unprocessable Entity'], 422);
        //验证是否是查询本人信息
        $token=$request->token;
        $phone_num=$request->phone_num;
        $user=DB::table('users')->select('nickname','sex','task_id')->where('phone_num',$phone_num)->where('remember_token',$token)->get();
        if(!$user){
            return response()->json(['status' => 401, 'message' => 'Illegal Search']);
        }else{
            $task_id=$user[0]->task_id;
            $nickname=$user[0]->nickname;
            $sex=$user[0]->sex;
            $lable_ida=DB::table('lable_user')->where('phone_num',$phone_num)->get();
            //获取lable_id
            if($lable_ida){
                for($i=0;$i<count($lable_ida);$i++){
                    $lable_id[$i]=$lable_ida[$i]->lable_id;
                    $lable=DB::table('lables')->select('lable')->where('lable_id',$lable_id[$i])->get();
                }
            }else{
                return response()->json(['status' => 402, 'message' => 'Not Found Lable']);
            }
            //获取task
            $task = DB::table('tasks')->where('id',$task_id)->get();
            if(!$task){
                return response()->json(['status' => 402, 'message' => 'Not Found Task']);
            }
            $model=([
                'nickname'=>$nickname,
                'sex'=>$sex,
                'task'=>$task[0]->task,
                'lable'=>$lable
            ]);
            return response()->json(['status' => 200, 'message' => 'OK','data'=>$model]);
        }



    }
    //获取用户头像
    public function getImgs($sex){
        $img=DB::table('imgs')->select('imgUrl')->where('sex',$sex)->get();
        $imgUrl=$img[0]->imgUrl;
        return $imgUrl;
    }
    //判断是否为注册用户
    public function isUser($phone_num){
        $re = DB::table('users')->where('phone_num',$phone_num)->get();
        if($re){
            return 1;
        }else{
            return 0;
        }
    }
    //获取lable列表
    public function getLableList(){
        $list=DB::table('lables')->select('lable_id','lable')->get();
        if(!$list){
            return response()->json(['status' => 404, 'message' => 'Not Found'],404);
        }
        return response()->json(['status' => 200, 'message' => 'OK','lable'=>$list]);
    }
}
