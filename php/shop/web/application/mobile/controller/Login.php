<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/27
 * Time: 16:40
 */
namespace app\mobile\controller;
use think\Controller;
class Login extends Controller
{
    protected function initialize()
    {
        $this->wxconfig = db('wxconfig')->find(1);
        $this->curUrl = getWebUrl().request()->url();
        $this->code = input('code');
    }

    public function index()
    {
        if(empty($this->code))
        {
            $code_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->wxconfig['appid'].'&redirect_uri='.urlencode($this->curUrl).'&response_type=code&scope=snsapi_base&state=php40#wechat_redirect';
            $this->redirect($code_url);
        }
        else
        {
            $result = authAccessToken($this->wxconfig['appid'], $this->wxconfig['appsecret'], $this->code);
            $user = db('user')->where('openid',$result['openid'])->find();
            if($user)
            {
                session('user_id',$user['id']);
                session('username',$user['nickname']);
                $this->success('登录成功','index/index');
            }
            else
            {
                $this->redirect('bind');
            }
        }
    }

    public function bind()
    {
        if(request()->isPost()){
            $post = input();
            $user = db('user')->where('username',$post['username'])->find();
            if($user)
            {
                if(password_verify($post['password'],$user['password']))
                {
                    $data = [
                        'openid'=>$post['openid'],
                        'nickname'=>$post['nickname'],
                        'sex'=>$post['sex'],
                        'city'=>$post['city'],
                        'province'=>$post['province'],
                        'headimgurl'=>$post['headimgurl'],
                        'id'=>$user['id']
                    ];
                    $res = db('user')->update($data);
                    session('user_id',$user['id']);
                    session('username',$data['nickname']);
                    $this->redirect('index/index');
                }
                else
                {
                    $this->error('密码错误');
                }
            }
            else
            {
                $this->error('用户名不存在');
            }
        }
        else
        {
            if(empty($this->code))
            {
                $code_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->wxconfig['appid'].'&redirect_uri='.urlencode($this->curUrl).'&response_type=code&scope=snsapi_userinfo&state=php40#wechat_redirect';
                $this->redirect($code_url);
            }
            else
            {
                $result = authAccessToken($this->wxconfig['appid'], $this->wxconfig['appsecret'], $this->code);
                $id = db('user')->where('openid',$result['openid'])->value('id');
                $arr = authUser($result['access_token'],$result['openid']);
                $this->assign('wxinfo',$arr);
                return $this->fetch();
            }
            return $this->fetch();
        }

    }
    public function reg()
    {
        if(request()->isPost())
        {
            $post = input();
            if($post['password'] == $post['password1'])
            {
                $post['password']  = password_hash($post['password'],PASSWORD_DEFAULT);
                $data = [
                    'username'=>$post['username'],
                    'password'=>$post['password'],
                    'openid'=>$post['openid'],
                    'nickname'=>$post['nickname'],
                    'sex'=>$post['sex'],
                    'city'=>$post['city'],
                    'province'=>$post['province'],
                    'headimgurl'=>$post['headimgurl'],
                ];
                $id = db('user')->insertGetId($data);
                session('user_id',$id);
                session('username',$data['nickname']);
                $this->redirect('index/index');
            }
            else
            {
                $this->error('密码不一致');
            }
        }
        else
        {
            if(empty($this->code))
            {
                $code_url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid='.$this->wxconfig['appid'].'&redirect_uri='.urlencode($this->curUrl).'&response_type=code&scope=snsapi_userinfo&state=php40#wechat_redirect';
                $this->redirect($code_url);
            }
            else
            {
                $result = authAccessToken($this->wxconfig['appid'], $this->wxconfig['appsecret'], $this->code);
                $arr = authUser($result['access_token'],$result['openid']);
                $this->assign('wxinfo',$arr);
                return $this->fetch();
            }
        }
    }
}