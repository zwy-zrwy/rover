<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/12/2
 * Time: 9:10
 */
namespace app\mobile\controller;
use think\Controller;
use jssdk\Jssdk;
class Share extends Controller
{
    public function index()
    {
        $conf = db('wxconfig')->find(1);
        $jssdk = new Jssdk($conf['appid'], $conf['appsecret']);
        $signPackage = $jssdk->GetSignPackage();
        $this->assign('res',$signPackage);
        return $this->fetch();
    }
}