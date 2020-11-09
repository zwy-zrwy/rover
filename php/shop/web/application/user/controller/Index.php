<?php
namespace app\user\controller;
use think\Controller;
class Index extends Controller
{
    public function index()
    {
        return $this->fetch();
    }
    public function edit()
    {
        if(request()->isPost())
        {
            $post = input();
            if(!empty($post['password']) && !empty($post['repassword']))
            {
                if($post['password'] == $post['repassword'])
                {
                    $post['password'] = password_hash($post['password'],PASSWORD_DEFAULT);
                    unset($post['repassword']);
                    $res = db('user')->update($post);
                    if($res)
                    {
                        $this->success('修改成功','index');
                    }
                    else
                    {
                        $this->error('修改失败');
                    }
                }
                else
                {
                    $this->error('两次密码不一致');
                }
            }
            else
            {
                unset($post['password']);
                unset($post['repassword']);
                $res = db('user')->update($post);
                if($res)
                {
                    $this->success('修改成功','index');
                }
                else
                {
                    $this->error('修改失败');
                }
            }
        }
        else
        {
            $id = input('id');
            $user = db('user')->find($id);
            $this->assign('user',$user);
            return $this->fetch();
        }
    }

}
