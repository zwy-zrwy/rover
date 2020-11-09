<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/14
 * Time: 10:55
 */
namespace app\index\controller;
use think\Controller;
class Category extends Controller
{
    public function index()
    {
        $id = input('id');
        $ids = \getSubIds($id);
        $goods = db('goods')->where('status',1)->where('cid','in',$ids)->paginate(10);
        $this->assign('goods',$goods);
        return $this->fetch();
    }
}