<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/18
 * Time: 10:26
 */

namespace app\admin\controller;


class Sttr extends Base
{
    public function index()
    {
        $data = db('sttr')->where('status',1)->select();
        $this->assign('data',$data);
        return $this->fetch();
    }

    public function add()
    {

    }

    public function edit()
    {

    }

    public function del()
    {

    }
}