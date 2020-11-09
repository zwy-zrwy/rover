<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/27
 * Time: 11:14
 */
namespace app\mobile\controller;
use think\Controller;
class Base  extends Controller
{
    protected function initialize()
    {
        if (empty(session('user_id')))
        {
            $this->redirect('login/index');
        }
    }
}
