<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/26
 * Time: 10:18
 */
namespace app\admin\controller;
class Wxconfig extends Base
{
    public function index()
    {
        if(request()->isPost())
        {
            $post = input();
            $res = db('wxconfig')->update($post);
            if($res)
            {
                $this->success('修改成功');
            }
            else
            {
                $this->error('修改失败');
            }
        }
        else{
            $info = db('wxconfig')->find(1);
            $this->assign('info',$info);
            return $this->fetch();
        }
    }
}