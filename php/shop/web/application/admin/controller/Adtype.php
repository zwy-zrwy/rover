<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/21
 * Time: 15:41
 */
namespace app\admin\controller;
class Adtype extends Base
{
    public function index()
    {
        $data = db('adtype')->select();
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function add()
    {
        if(request()->isPost())
        {
            $post = input();
            $res = db('adtype')->insert($post);
            if($res)
            {
                $this->success('添加成功','index');
            }
            else
            {
                $this->error('添加失败');
            }
        }
        return $this->fetch();
    }

    public function edit()
    {
        if(request()->isPost())
        {
            $post = input();
            $res = db('adtype')->update($post);
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
            $id = input('id');
            $info = db('adtype')->find($id);
            $this->assign('info',$info);
            return $this->fetch();
        }
    }
    public function del()
    {
        $id = input('id');
        $res = db('adtype')->delete($id);
        if($res)
        {
            $this->success('删除成功','index');
        }
        else
        {
            $this->error('修改失败');
        }
    }
}