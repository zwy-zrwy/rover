<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/11
 * Time: 18:05
 */

namespace app\admin\controller;


class Admin extends Base
{
    public function index()
    {
        $data = db('admin')->alias('a')->field('a.*,r.name rname')->leftJoin('role r','a.role_id=r.id')->select();
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function add()
    {
        if(request()->isPost())
        {
            $post = input();
            if($post['password'] == $post['repassword'])
            {
                $post['password'] = password_hash($post['password'],PASSWORD_DEFAULT);
                unset($post['repassword']);
                $res = db('admin')->insert($post);
                if($res)
                {
                    $this->success('添加管理员成功','admin/admin/index');
                }
                else
                {
                    $this->error('添加管理员失败');
                }
            }
            else
            {
                $this->error('两次密码输入不一致');
            }
        }
        else
        {
            $role = db('role')->select();
            $nodes = db('node')->select();
            $this->assign(['role'=>$role,'nodes'=>$nodes]);
            return $this->fetch();
        }
    }

    public function edit()
    {
        if(request()->isPost())
        {
            $post = input();
            $user = db('admin')->where('id',input('id'))->find();
            if(password_verify($post['password'],$user['password']))
            {
                if($post['password1'] == $post['password2'])
                {
                    if( !empty($post['password1']) && !empty($post['password1']))
                    {
                        $post['password1'] = password_hash($post['password1'],PASSWORD_DEFAULT);
                        $data = [
                            'id' =>input('id'),
                            'username'=>$post['username'],
                            'password'=>$post['password1'],
                            'role_id'=>$post['role_id'],
                            'status'=>$post['status']
                            ];

                        $res = db('admin')->update($data);
                        if($res)
                        {
                            $this->success('修改成功','admin/admin/index');
                        }
                        else
                        {
                            $this->error('修改失败');
                        }
                    }
                    else
                    {
                        $data = [
                            'id' =>input('id'),
                            'username'=>$post['username'],
                            'role_id'=>$post['role_id'],
                            'status'=>$post['status']
                        ];
                        $res = db('admin')->update($data);
                        if($res)
                        {
                            $this->success('修改成功','admin/admin/index');
                        }
                        else
                        {
                            $this->error('修改失败');
                        }
                    }
                }
                else
                {
                    $this->error('修改密码两次输入不一致');
                }
            }
            else
            {
                $this->error('cc原密码输入不正确');
            }
        }
        else
        {
            $role = db('role')->select();
            $nodes = db('node')->select();
            $id = input('id');
            $user = db('admin')->find($id);
            $this->assign(['role'=>$role,'nodes'=>$nodes,'info'=>$user]);
            return $this->fetch();
        }
    }

    public function del()
    {
        $res = db('admin')->delete(input('id'));
        if($res)
        {
            $this->success('删除成功','admin/admin/index');
        }
        else
        {
            $this->error('删除失败');
        }
    }
}