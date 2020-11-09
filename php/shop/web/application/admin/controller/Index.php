<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/27
 * Time: 8:38
 */

namespace app\admin\controller;
use jwt\Jwt;
class Index extends Base
{
    public function index()
    {
        echo 'admin/Index/index';
    }

    public function user()
    {
        $data = input();
        $jwt = new Jwt;
        $getPayload = $jwt->verifyToken($data['access_token']);
        $user = db('admin')->find($getPayload['sub']);
        $arr =
            [
                'code' => 0,
                'msg' => '',
                'data' => [
                    'username' => $user['username'],
                    'sex' => '男',
                    'role' => 1
                ]
            ];
        return json($arr);
    }
}