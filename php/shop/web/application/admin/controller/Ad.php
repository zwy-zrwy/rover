<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/21
 * Time: 15:09
 */

namespace app\admin\controller;
class Ad extends Base
{
    public function index()
    {
        $data = db('ad')->alias('a')->field('a.*,ad.name tname')->leftJoin('adtype ad','a.type = ad .id')->where('status',1)->select();
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
            $res = db('ad')->insert($post);
            if($res)
            {
                $this->success('添加广告成功','index');
            }
            else
            {
                $this->error('添加失败');
            }
        }
        else
        {
            $type = db('adtype')->select();
            $this->assign('type',$type);
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
                $post['pic'] = upload();
            }
            $res = db('ad')->update($post);
            if($res)
            {
                $this->success('修改广告成功','index');
            }
            else
            {
                $this->error('修改失败');
            }
        }
        else
        {
            $id = input('id');
            $data  = db('ad')->alias('a')->field('a.*,ad.name tname')->leftJoin('adtype ad','a.type = ad .id')->find($id);
            $type = db('adtype')->select();
            $this->assign('data',$data);
            $this->assign('type',$type);
            return $this->fetch();
        }
    }
    public function del()
    {
        $id = input('id');
        $res = db('ad')->delete($id);
        if($res)
        {
            $this->success('删除广告成功','index');
        }
        else
        {
            $this->error('删除失败');
        }
    }
}