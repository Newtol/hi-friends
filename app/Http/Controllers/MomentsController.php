<?php

namespace App\Http\Controllers;

use App\Praise;
use Illuminate\Http\Request;
use App\Moments;
use App\Http\Requests;
use App\Http\Controllers\Auth;
use Validator;
use Illuminate\Support\Facades\DB;
class MomentsController extends Controller
{
    //获取匿名
    public function getNickname() {
        $anonymity_id =rand(1, 3);
        $res = DB::table('anonymous')->select('nickname')->where('anonymous_id',$anonymity_id)->get();
        $nickname = $res[0]->nickname;
        return $nickname;

    }
    //发布hi圈
    public function publishContext(Request $request,$phone_num){
//        //验证该用户是否存在
        $rec = $this->isUser($phone_num);
        if(!$rec){
            return response()->json(['status' => 400, 'message' => 'Is not user'],402);
        }
        //验证是否登陆
        $re= $request->session()->has('0');
        if(!$re){
            return response()->json(['status' => 400, 'message' => 'Bad request'],400);
        }else{

            $content=\Request::all();
            //验证数据是否合理
            $validator = Validator::make($request->all(), [
                'anonymity' => 'required',
                'text'=>'required'
            ]);
            if ($validator->fails())
                return response()->json(['status' => 422, 'message' => 'Unprocessable Entity'], 422);

            $anonymity = $request ->anonymity;
            $text =$request ->text;
            //判断是否需要匿名
            if($anonymity=='1'){
                $nickname=$this->getNickname();
            }elseif($anonymity=='2'){
                $res =DB::table('users')->select('nickname')->where('phone_num',$phone_num)->get();
                $nickname=$res[0]->nickname;
            }else{
                return response()->json(['status' => 402, 'message' => 'Anonymity False']);
            }
            $reb = Moments::create([
                'content'=>$text,
                'nickname'=>$nickname,
                'phone_num'=>$phone_num

            ]);
            if($reb){
                return response()->json(['status' => 200, 'message' => 'OK']);
            }else{
                return response()->json(['status' => 401, 'message' => 'Publish False']);
            }
        }

    }
    //点赞hi圈
    public function praiseContext(Request $request){
        $phone_num = $request->phone_num;
        $rec = $this->isUser($phone_num);
        if(!$rec){
            return response()->json(['status' => 400, 'message' => 'Is not user'],402);
        }
        $re= $request->session()->has('0');
        if(!$re){
            return response()->json(['status' => 400, 'message' => 'Bad request'],400);
        }else{
            $validator = Validator::make($request->all(), [
                'text_id' => 'required|alpha_num',
                'phone_num'=>'required|alpha_num'
            ]);
            if ($validator->fails())
                return response()->json(['status' => 422, 'message' => 'Unprocessable Entity'], 422);
            $content_id = $request->text_id;
            $rea=DB::table('content_praise')->where('content_id',$content_id)->where('phone_num',$phone_num)->get();
            if(!$rea) {
                $reb=Praise::create([
                    'content_id' => $content_id,
                    'phone_num' => $phone_num
                ]);
                if($reb){
                    return response()->json(['status' => 200, 'message' => 'OK']);
                }
            }else{
                return response()->json(['status' => 401, 'message' => 'Parise repeatedly'],401);
            }
        }
    }
    //获取hi圈
    public function getContext(){
        $list=DB::table('content_user')->select('nickname','content','created_at','id')->orderBy('created_at', 'desc')->get();
        for($i=0;$i<count($list);$i++){
            $content_id = $list[$i]->id;
            $praise=DB::table('content_praise')->where('content_id',$content_id)->count();
            if(!$praise){
                $praise=0;
            }
            $list[$i]->praiseNum = $praise;

        }
        return response()->json(['status' => 200, 'message' => 'OK','date'=>$list]);
    }
    //获取点赞历史
    public function getPraiseHistory(Request $request){
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
                $list=DB::table('content_praise')->select('content_id','created_at')->orderBy('created_at', 'desc')->get();
                if(!$list){
                    return response()->json(['status' => 200, 'message' => 'OK' ,'date'=>null]);
                }else{
                    for($i=0;$i<count($list);$i++){
                        $content_id=$list[$i]->content_id;
                        $content=DB::table('content_user')->select('content','nickname')->where('id',$content_id)->get();
                        if(!$content){
                            return response()->json(['status' => 402, 'message' => 'Null Content']);
                        }else{
                            $list[$i]->text = $content[0]->content;
                            $list[$i]->nickname = $content[0]->nickname;
                        }
                    }
                    return response()->json(['status' => 200, 'message' => 'OK' ,'date'=>$list]);
                }
            }
        }
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

}
