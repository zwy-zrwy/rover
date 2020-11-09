<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/11
 * Time: 13:18
 */

namespace app\admin\controller;
class Role extends Base
{
    public function index()
    {
        $this->assign('data',db('role')->select());
        return $this->fetch();
    }
    public function add()
    {
        if(request()->isPost())
        {

            $post = input();
            $post['nodes'] = implode(',',$post['nodes']);
            $rule = [
                'name|角色名'=>'require',
            ];
            $result = $this->validate($post,$rule);
            if($result !== true)
            {
                $this->error($result);
            }
            else
            {
                $res = db('role')->insert($post);
                if($res)
                {
                    $this->success('添加角色成功','index');
                }
                else
                {
                    $this->error('添加角色失败');
                }
            }
        }
        else
        {
            $nodes = db('node')->where('status',1)->select();
            $nodes = get_children($nodes,'pid','sub',['status']);
            $this->assign('nodes',$nodes);
            return $this->fetch();
        }
    }

    public function edit()
    {
        if(request()->isPost())
        {
            $post = input();
            $post['nodes'] = implode(',',$post['nodes']);
            $rule = [
                'name|角色名'=>'require',
            ];
            $result = $this->validate($post,$rule);
            if($result !== true)
            {
                $this->error($result);
            }
            else
            {
                $res = db('role')->update($post);
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
            $info = db('role')->find($id);
            $nodes = db('node')->where('status',1)->select();
            $nodes = get_children($nodes);
            $this->assign(['nodes'=>$nodes,'info'=>$info]);
            return $this->fetch();
        }
    }

    public function del()
    {
        $id = input('id');
        $res = db('role')->delete($id);
        if($res)
        {
            $this->success('删除成功','admin/role/index');
        }
        else
        {
            $this->error('删除失败');
        }
    }
}