<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/11
 * Time: 18:04
 */

namespace app\admin\controller;
class Category extends Base
{
    public function index()
    {
        $data =db('category')->alias('c')->field('c.*,ca.name as cname')->leftJoin('category ca','c.pid = ca.id')->select();
        $data = get_children($data,'pid','sub',['status','cname','pic']);
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function add()
    {
        if(request()->isPost())
        {
            $post = input();
            if(!empty($_FILES['pic']['tmp_name']))
            {
                $post['pic'] = upload();
            }
            $res = db('category')->insert($post);
            if($res)
            {
                $this->success('添加成功','admin/category/index');
            }
            else
            {
                $this->error('添加失败');
            }
        }
        else
        {
            $data = db('category')->select();
            $data = get_children($data,'pid','sub',['pic','pid','status']);
            $this->assign('data',$data);
            return $this->fetch();
        }
    }

    public function edit()
    {
        if(request()->isPost())
        {
            $post = input();
            if(!empty($_FILES['pic']['tmp_name']))
            {
                $post['pic'] = upload();
            }
            $res = db('category')->update($post);
            if($res)
            {
                $this->success('修改成功','admin/category/index');
            }
            else
            {
                $this->error('修改失败');
            }
        }
        else
        {
            $id = input('id');
            $info = db('category')->find($id);
            $data = db('category')->select();
            $data = get_children($data,'pid','sub',['pic','pid','status']);
            $this->assign(['data'=>$data,'info'=>$info]);
            return $this->fetch();
        }
    }

    public function del()
    {
        $id = input('id');
        $res = db('category')->delete($id);
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