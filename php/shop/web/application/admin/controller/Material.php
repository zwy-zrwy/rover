<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/26
 * Time: 18:17
 */
namespace app\admin\controller;
use think\facade\Env;
class Material extends Base
{
    public function index()
    {
         $list = db('material')->paginate(10);
         $this->assign('list',$list);
         return $this->fetch();
    }

    public function add()
    {
        if(request()->isPost())
        {
            $post = input();
            $pic = upload();
            $post['pic'] = str_replace('\\','/',$pic);
            $id = db('material')->insertGetId($post);
            if($id)
            {
                $pic = db('material')->where('id',$id)->value('pic');
                $pic = Env::get('root_path').'/public/uploads/'.$pic;
                $pic = str_replace('\\','/',$pic);
                $media_id = uploadMaterial($post['type'],$pic);
                $res = db('material')->update(['media_id'=>$media_id,'id'=>$id]);
                if($res)
                {
                    $this->success('添加成功','index');
                }
                else
                {
                    $this->error('添加失败');
                }
            }
        }
        else
        {
            return $this->fetch();
        }
    }
    public function del()
    {
        $res = db('material')->delete(input('id'));
        if($res)
        {
            $this->success('删除成功','index');
        }
        else
        {
            $this->error('删除失败');
        }
    }
}