<?php
/**
 * Created by PhpStorm.
 * User: colonel  Lee
 * Date: 2019/11/19
 * Time: 18:22
 */

namespace app\user\controller;
use think\captcha\Captcha;
use think\Controller;
use MySendMail;
class Login extends Controller
{
    public function index()
    {
        if(request()->isPost())
        {
            $post = input();
            $rule = [
                'username|用户名'=>'require',
                'password|密码'=>'require',
                'code|验证码'=>'require',
            ];
            $res = $this->validate($post,$rule);
            if($res !== true)
            {
                $arr = ['code'=>1,'msg'=>$res];
            }
            else
            {
                if(!captcha_check($post['code']))
                {
                    $arr = ['code'=>1,'msg'=>'验证码错误'];
                }
                else
                {
                    $data = db('user')->where('username',$post['username'])->find();
                    if(empty($data))
                    {
                        $arr = ['code'=>1,'msg'=>'用户名不存在'];
                    }
                    else
                    {
                        if(password_verify($post['password'],$data['password']))
                        {
                            session('user_id',$data['id']);
                            session('user_name',$data['username']);
                            $arr = ['code'=>0,'msg'=>'登录成功'];
                        }
                        else
                        {
                            $arr = ['code'=>1,'msg'=>'密码错误'];
                        }
                    }
                }
            }
            return json($arr);
        }
        else
        {
            return $this->fetch();
        }
    }


    public function register()
    {
        if(request()->isPost())
        {
            $post = input();
            $rule = [
                'username|用户名'=>'require',
                'password|密码'=>'require',
                'repassword|确认密码'=>'require',
                'email|邮箱'=>'require',
                'code'=>'require',
            ];
            $res = $this->validate($post,$rule);
            if($res !== true)
            {
                $arr = ['code'=>1,'msg'=>$res];
            }
            else
            {
                if($post['code'] == $post['recode'])
                {
                    if($post['password'] == $post['repassword'])
                    {
                        $post['password'] = password_hash($post['password'],PASSWORD_DEFAULT);
                        unset($post['repassword']);
                        unset($post['code']);
                        unset($post['recode']);
                        $res = db('user')->insert($post);
                        if($res)
                        {
                            $arr = ['code'=>0,'msg'=>'注册成功'];
                        }
                        else
                        {
                            $arr = ['code'=>1,'msg'=>'系统繁忙，请稍后再试呀'];
                        }
                    }
                    else
                    {
                        $arr = ['code'=>1,'msg'=>'两次密码不一致，憨批吗？'];
                    }
                }
                else
                {
                    $arr = ['code'=>1,'msg'=>'验证码错误'];
                }
            }
            return json($arr);
        }
        else
        {
            return $this->fetch();
        }
    }

    public function email()
    {
        $post = input();
        $code = rand(1000,9999);
        $name = $post['username'];
        $email = $post['email'];
        $mail = new \MySendMail();
        $mail->setServer("smtp.exmail.qq.com", "beiyou@xarlit.cn", "A123456.com");
        $mail->setFrom("beiyou@xarlit.cn");
        $mail->setReceiver($email);//设置收件人
//$mail->setReceiver("XXXXX@XXXXX");
//            $mail->setCc("768645046@qq.com");//抄送
//            $mail->setCc("1439442026@qq.com");//抄送
        /*
        $mail->setCc("290467972@qq.com");
        $mail->setBcc("290467972@qq.com");
        $mail->setBcc("290467972@qq.com");
        $mail->setBcc("290467972@qq.com");
        */
        $mail->setMailInfo("假装我是淘宝官方", "<b>亲爱的 $name 宁正在注册淘宝新账号，验证码： $code ,打死都不要告诉其他的憨批哦！</b>");
        $mail->sendMail();
        $arr = ['code'=>0,'msg'=>'获取验证码成功','recode'=>$code];
        return json($arr);
    }
    //验证码
    public function verify()
    {
        $config = [
            'imageW'=>130,
            'imageH'=>38,
            'fontSize'=>18,
            'useCurve'=>false,
            'useNoise'=>false,
            'fontttf'=>'LCALLIG.TTF',
            'codeSet'=>'123456789',
            'length'=>4
        ];
        $captcha = new Captcha($config);
        return $captcha->entry();
    }

    public function logout()
    {
        session('user_id',null);
        session('user_name',null);
        $this->success('退出成功','login /index');
    }
}