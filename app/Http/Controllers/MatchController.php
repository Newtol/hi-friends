<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Moments;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use App\friends;
use Validator;

class MatchController extends Controller
{
    //随机匹配
    public function getFrirendByRankdom(Request $request,$phone_num){
//       $rec = $this->isUser($phone_num);
//       if(!$rec){
//           return response()->json(['status' => 400, 'message' => 'Is not user'],402);
//       }
//       //验证是否登陆
//       $re= $request->session()->has('0');
//       if(!$re){
//           return response()->json(['status' => 400, 'message' => 'Bad request'],400);
//       }else{
           $n=0;
           do{
               do {
                   //从数据库里随机一个用户
                   $re = DB::select('SELECT * FROM users WHERE id >= ((SELECT MAX(id) FROM users)-(SELECT MIN(id) FROM users)) * RAND() + (SELECT MIN(id) FROM users)  LIMIT 1
                    ');
                    $n++;
                   //超过10次，默认匹配失败
                   if($n>10){
                       return response()->json(['status' => 200, 'message' => 'OK','date'=>null]);
                   }
               }while($phone_num==($re[0]->phone_num));
               $rea=DB::table('friends')->where('phone_num1',$phone_num)->where('phone_num2',$re[0]->phone_num)->get();
           }while($rea);
           $task_id=$re[0]->task_id;
           $task=DB::table('tasks')->select('task')->where('id',$task_id)->get();
           $model=([
               'nickname'=>$re[0]->nickname,
               'phone_num'=>$re[0]->phone_num,
               'introduce'=>$re[0]->introduce,
               'task'=>$task[0]->task
           ]);

           $res=Friends::create([
               'phone_num1'=>$phone_num,
               'phone_num2'=>$re[0]->phone_num,
               'result'=>'0'
           ]);

           return response()->json(['status' => 200, 'message' => 'OK','date'=>$model]);
//       }
   }
    //验证是否为注册用户
    public function isUser($phone_num){
           $re = DB::table('users')->where('phone_num',$phone_num)->get();
           if($re){
               return 1;
           }else{
               return 0;
           }
       }
    //筛选匹配
    public function getFriendsBySelect(Request $request,$phone_num){
        //验证该用户是否存在
        $rec = $this->isUser($phone_num);
        if(!$rec){
            return response()->json(['status' => 400, 'message' => 'Is not user'],402);
        }
        //验证是否登陆
        $re= $request->session()->has('0');
        if(!$re){
            return response()->json(['status' => 400, 'message' => 'Bad request'],400);
        }else {

            $content = \Request::all();
            //验证数据是否合理
            $validator = Validator::make($request->all(), [
                'sex'=>'required|digits:1',
                'lable_id' => 'required'
            ]);
            if ($validator->fails())
                return response()->json(['status' => 422, 'message' => 'Unprocessable Entity'], 422);
            $lable=json_decode($request->lable_id);
            $person=DB::table('lable_user')->whereIn('lable_id',$lable)->groupby('phone_num')->get();
            $len=count($person);
            if($len==0){
                $phone_num1=$person[0]->phone_num;
            }else{
                $n=0;
                do{
                    do {
                        //从查询到的数据中随机一条
                        $i = mt_rand(0, $len-1);
                        $phone_num1 = $person[$i]->phone_num;
                        //如果运行10次还未匹配到就认为没有数据
                        $n++;
                        if($n>10){
                            return response()->json(['status' => 200, 'message' => 'OK','date'=>null]);
                    }
                    }while($phone_num==$phone_num1);
                    //判断两个用户之前是否匹配过
                    $rea=DB::table('friends')->where('phone_num1',$phone_num)->where('phone_num2',$phone_num1)->get();
                }while($rea);
                //查询用户信息
                $re=DB::table('users')->where('phone_num',$phone_num1)->get();
                if(!$re){
                    return response()->json(['status' => 401, 'message' => 'Bad user']);
                }
                $task_id=$re[0]->task_id;
                $task=DB::table('tasks')->select('task')->where('id',$task_id)->get();
                $model=([
                    'nickname'=>$re[0]->nickname,
                    'phone_num'=>$re[0]->phone_num,
                    'introduce'=>$re[0]->introduce,
                    'task'=>$task[0]->task
                ]);
                //插入朋友组
                $res=Friends::create([
                    'phone_num1'=>$phone_num,
                    'phone_num2'=>$phone_num1,
                    'result'=>'0'
                ]);
                return response()->json(['status' => 200, 'message' => 'OK','date'=>$model]);
            }


        }
    }
    //回传匹配结果
    public function getResult(Request$request,$phone_num1){
        //验证该用户是否存在
        $rec = $this->isUser($phone_num1);
        if(!$rec){
            return response()->json(['status' => 400, 'message' => 'Is not user'],402);
        }
        //验证是否登陆
        $re= $request->session()->has('0');
        if(!$re){
            return response()->json(['status' => 400, 'message' => 'Bad request'],400);
        }else {

            $content = \Request::all();
            //验证数据是否合理
            $validator = Validator::make($request->all(), [
                'phone_num2'=>'required',
                'result' =>'required|digits:1 '
            ]);
            if ($validator->fails())
                return response()->json(['status' => 422, 'message' => 'Unprocessable Entity'], 422);
            $phone_num2=$request->phone_num2;
            $result=$request->result;
            DB::table('friends')->where('phone_num1',$phone_num1)->where('phone_num2',$phone_num2)->update(['result'=>$result]);
            if($result==1){
                $model = DB::table('users')->select('nickname','qq')->where('phone_num',$phone_num2)->get();
                return response()->json(['status' => 400, 'message' => 'OK','date'=>$model]);
            }}
    }
    //获取匹配历史
    public function getFriendsHistory(Request $request){
        //验证该用户是否存在
        $phone_num = $request->phone_num;
        $rec = $this->isUser($phone_num);
        if(!$rec){
            return response()->json(['status' => 400, 'message' => 'Is not user'],402);
        }
        //验证是否登陆
        $re= $request->session()->has('0');
        if(!$re){
            return response()->json(['status' => 400, 'message' => 'Bad request'],400);
        }else{
            $validator = Validator::make($request->all(), [
                'token' => 'required',
                'phone_num'=>'required|alpha_num'
            ]);
            if ($validator->fails())
                return response()->json(['status' => 422, 'message' => 'Unprocessable Entity'], 422);

            $token=$request->token;
            $user=DB::table('users')->where('phone_num',$phone_num)->where('remember_token',$token)->get();
            if(!$user){
                return response()->json(['status' => 401, 'message' => 'Illegal Search']);
            }else{
                $list1=DB::table('friends')->select('phone_num2','created_at')->where('phone_num1',$phone_num)->where('result','1')->get();
                $list2=DB::table('friends')->select('phone_num1','created_at')->where('phone_num2',$phone_num)->where('result','1')->get();
                for($i=0;$i<count($list1);$i++){
                    $list[$i]=([
                        'phone'=>$list1[$i]->phone_num2,
                        'date'=>$list1[$i]->created_at
                    ]);
                    for($j=0;$j<count($list2);$j++){
                        $list[$i+$j+1]=([
                            'phone'=>$list2[$j]->phone_num1,
                            'date'=>$list2[$j]->created_at
                        ]);
                    }
                }
                return response()->json(['status' => 200, 'message' => 'OK','date'=>$list]);
            }
        }
    }


}
