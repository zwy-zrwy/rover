<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/11
 * Time: 14:22
 */
namespace app\admin\controller;
class Node extends Base
{
    public function index()
    {
        $this->assign('data',get_children(db('node')->select(),'pid','sub',['status']));
        return $this->fetch();
    }
    public function add()
    {
        if(request()->isPost())
        {
            $post = input();
            $res = db('node')->insert($post);
            if($res)
            {
                $this->success('添加节点成功','admin/node/index');
            }
            else
            {
                $this->erro('添加节点失败');
            }
        }
        else
        {
            $nodes = db('node')->where('status',1)->where('pid',0)->select();
            $this->assign('nodes',$nodes);
            return $this->fetch();
        }
    }

    public function edit()
    {
        if(request()->isPost())
        {
            $post = input();
            $res = db('node')->update($post);
            if($res)
            {
                $this->success('修改成功','admin/node/index');
            }
            else
            {
                $this->error('修改失败');
            }
        }
        else
        {
           $id = input('id');
           $data = db('node')->find($id);
           $this->assign('data',$data);
           return $this->fetch();
        }
    }

    public function del()
    {
        $id = input('id');
        $res = db('node')->delete($id);
        if($res)
        {
            $this->success('删除成功','admin/node/index');
        }
        else
        {
            $this->error('删除失败');
        }
    }
}