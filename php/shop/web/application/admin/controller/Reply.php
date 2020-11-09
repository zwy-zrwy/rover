<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/26
 * Time: 17:21
 */
namespace app\admin\controller;
class Reply extends Base
{
    public function index()
    {
        $data = db('reply')->paginate(10);
        $this->assign('data',$data);
        return $this->fetch();
    }
}