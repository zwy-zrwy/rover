<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/14
 * Time: 17:17
 */

namespace app\index\controller;
use think\Controller;
class Brand extends Controller
{
    public function index()
    {
        $data = db('brand')->where('status',1)->select();
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function info()
    {
        $id = input('id');
        $data = db('goods')->where('status',1)->where('brand_id',$id)->select();
        $this->assign('data',$data);
        return $this->fetch();
    }
}