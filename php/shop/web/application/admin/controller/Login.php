<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/11
 * Time: 8:55
 */

namespace app\admin\controller;
use think\Controller;
use think\captcha\Captcha;
use jwt\Jwt;
class Login extends Controller
{
    public function index()
    {
        if(request()->isPost())
        {
            $post = input();
            $user = db('admin')->where('username',$post['username'])->find();
            if(empty($user))
            {
                $arr = ['code'=>1,'msg'=>'用户名不存在'];
            }
            else
            {
                if(password_verify($post['password'],$user['password']))
                {
                    $payload=array('sub'=>$user['id'],'name'=>$user['username'],'iat'=>time());
                    $jwt=new Jwt;
                    $token=$jwt->getToken($payload);
                    $data['access_token'] = $token;
                    $arr = ['code'=>0,'msg'=>'登录成功','data'=>$data];
                }
                else
                {
                    $arr = ['code'=>1,'msg'=>'密码错误'];
                }
            }
            return json($arr);
        }
        else
        {
            $this->view->engine->layout(false);
            return $this->fetch();
        }
    }
    //验证码
    public function verify()
    {
        $config = [
            'imageW'=>130,
            'imageH'=>38,
            'fontSize'=>18,
            'useCurve'=>false,
            'useNoise'=>false,
            'fontttf'=>'LCALLIG.TTF',
            'codeSet'=>'123456789',
            'length'=>4
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }
    public function logout()
    {
        session('admin_id',null);
        session('admin_name',null);
        session('role_id',null);
        $this->success('退出成功','admin/login/index');
    }
}