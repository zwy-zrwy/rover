<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/11
 * Time: 10:53
 */

namespace app\admin\controller;
use think\Controller;
class Base extends Controller
{
    protected function initialize()
    {
        //session判断登录状态
//        if(empty(session('admin_id')))
//        {
//            $this->error('未登录','admin/login/index');
//        }
        //权限
//        $nodes = db('role')->where('id',session('role_id'))->value('nodes');
//        $cons = db('node')->where('id','in',$nodes)->column('con');
        $con = request()->controller();
//        $cons[] = 'Index';
//        if(!in_array($con,$cons) && session('admin_id') != 1)
//        {
//            $this->error('无此权限');
//        }
        //菜单
        $menu = db('node')->where('status',1)->where('pid',0)->select();
        foreach($menu as $key=>$val)
        {
             $sub= db('node')->where('status',1)->where('pid',$val['id'])->select();
             foreach($sub as $k=>$v)
             {
                 $sub[$k]['con'] = $v['con'].'/index';

             }
            $menu[$key]['sub'] = $sub;
        }
        $this->assign(['menu'=>$menu,'con'=>$con.'/index']);
    }
}