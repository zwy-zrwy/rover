<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/13
 * Time: 17:11
 */
namespace app\admin\controller;
class Brand extends Base
{
    public function index()
    {
        $data = db('brand')->paginate(10)->each(function($item, $key){
        if(!is_http($item['pic']))
        {
            $item['pic'] = '/uploads/'.$item['pic'];
        }
        return $item;
        });
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function add()
    {
        if(request()->isPost())
        {
            $post = input();
            if($_FILES['pic']['tmp_name'])
            {
                $post['pic'] = upload();
            }
            $res = db('brand')->insert($post);
            if($res)
            {
                $this->success('添加品牌成功','admin/brand/index');
            }
            else
            {
                $this->error('添加品牌失败');
            }
        }
        else
        {
            return $this->fetch();
        }
    }

    public function edit()
    {
        if(request()->isPost())
        {
            $post = input();
            if($_FILES['pic']['tmp_name'])
            {
                $post['pic'] = uploads();
            }
            $res = db('brand')->update($post);
            if($res)
            {
                $this->success('修改成功','admin/brand/index');
            }
            else
            {
                $this->error('删除失败');
            }
        }
        else
        {
            $id = input('id');
            $info = db('brand')->find($id);
            $this->assign('info',$info);
            return $this->fetch();
        }
    }

    public function del()
    {
        $id = input('id');
        $res = db('brand')->delete($id);
        if($res)
        {
            $this->success('删除成功','admin/brand/index');
        }
        else
        {
            $this->error('删除失败');
        }
    }
}