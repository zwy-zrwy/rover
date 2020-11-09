<?php

/*用户类*/

class user
{

    /*------------------------------------------------------ */
    //-- PUBLIC ATTRIBUTEs
    /*------------------------------------------------------ */

    /* 整合对象使用的数据库主机 */
    var $db_host        = '';

    /* 整合对象使用的数据库名 */
    var $db_name        = '';

    /* 整合对象使用的数据库用户名 */
    var $db_user        = '';

    /* 整合对象使用的数据库密码 */
    var $db_pass        = '';

    /* 整合对象数据表前缀 */
    var $prefix         = '';

    /* 数据库所使用编码 */
    var $charset        = '';

    /* 整合对象使用的cookie的domain */
    var $cookie_domain  = '';

    /* 整合对象使用的cookie的path */
    var $cookie_path    = '/';

    /* 整合对象会员表名 */
    var $user_table = '';

    /* 会员ID的字段名 */
    var $field_id       = '';

    /* 会员名称的字段名 */
    var $field_mobile     = '';

    /* 会员性别 */
    var $field_gender = '';

    /* 注册日期的字段名 */
    var $field_reg_date = '';

    var $error          = 0;

    /*------------------------------------------------------ */
    //-- PRIVATE ATTRIBUTEs
    /*------------------------------------------------------ */

    var $db;

    /*------------------------------------------------------ */
    //-- PUBLIC METHODs
    /*------------------------------------------------------ */

    /**
     * 会员数据整合插件类的构造函数
     *
     * @access      public
     * @param       string  $db_host    数据库主机
     * @param       string  $db_name    数据库名
     * @param       string  $db_user    数据库用户名
     * @param       string  $db_pass    数据库密码
     * @return      void
     */
    function user($cfg)
    {
        $this->charset = isset($cfg['db_charset']) ? $cfg['db_charset'] : 'UTF8';
        $this->prefix = isset($cfg['prefix']) ? $cfg['prefix'] : '';
        $this->db_name = isset($cfg['db_name']) ? $cfg['db_name'] : '';
        $this->cookie_domain = isset($cfg['cookie_domain']) ? $cfg['cookie_domain'] : '';
        $this->cookie_path = isset($cfg['cookie_path']) ? $cfg['cookie_path'] : '/';
        $this->need_sync = true;

        $quiet = empty($cfg['quiet']) ? 0 : 1;

        /* 初始化数据库 */
        if (empty($cfg['db_host']))
        {
            $this->db_name = $GLOBALS['aos']->db_name;
            $this->prefix = $GLOBALS['aos']->prefix;
            $this->db = $GLOBALS['db'];
        }
        else
        {
            if (empty($cfg['is_latin1']))
            {
                $this->db = new cls_mysql($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name'], $this->charset, NULL,  $quiet);
            }
            else
            {
                $this->db = new cls_mysql($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $cfg['db_name'], 'latin1', NULL, $quiet) ;
            }
        }

        if (!is_resource($this->db->link_id))
        {
            $this->error = 1; //数据库地址帐号
        }
        else
        {
            $this->error = $this->db->errno();
        }




        $this->user_table = 'users';
        $this->field_id = 'user_id';
        $this->ec_salt = 'ec_salt';
        $this->field_mobile = 'mobile';
        $this->field_gender = 'sex';
        $this->field_reg_date = 'reg_time';
        $this->is_aoshop = 1;
    }

    /**
     *  用户登录函数
     *
     * @access  public
     * @param   string  $username
     * @param   string  $password
     *
     * @return void
     */
    function login($mobile, $password, $remember = null)
    {
        if ($this->check_user($mobile, $password) > 0)
        {
            $this->set_session($mobile);
            $this->set_cookie($mobile, $remember);
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     *
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function logout ()
    {
        $this->set_cookie(); //清除cookie
        $this->set_session(); //清除session
    }

    /**
     *  编辑用户信息($password, $gender)
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function edit_user($cfg)
    {
        if (empty($cfg['mobile']))
        {
            return false;
        }
        else
        {
            $cfg['post_mobile'] = $cfg['mobile'];

        }

        $values = array();
        if (!empty($cfg['password']) && empty($cfg['md5password']))
        {
            $cfg['md5password'] = md5($cfg['password']);
        }
        if ((!empty($cfg['md5password'])) && $this->field_pass != 'NULL')
        {
            $values[] = $this->field_pass . "='" . $this->compile_password(array('md5password'=>$cfg['md5password'])) . "'";
        }

        if ($values)
        {
            $sql = "UPDATE " . $this->table($this->user_table).
                   " SET " . implode(', ', $values).
                   " WHERE " . $this->field_mobile . "='" . $cfg['post_mobile'] . "' LIMIT 1";

            $this->db->query($sql);
        }

        return true;
    }

    /**
     * 删除用户
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function remove_user($id)
    {
        $post_id = $id;

        if ($this->need_sync || (isset($this->is_aoshop) && $this->is_aoshop))
        {
            /* 如果需要同步或是aoshop插件执行这部分代码 */
            $sql = "SELECT user_id FROM "  . $GLOBALS['aos']->table('users') . " WHERE ";
            $sql .= (is_array($post_id)) ? db_create_in($post_id, 'user_id') : "user_id='". $post_id . "' LIMIT 1";
            $col = $GLOBALS['db']->getCol($sql);

            if ($col)
            {
                $sql = "UPDATE " . $GLOBALS['aos']->table('users') . " SET parent_id = 0 WHERE " . db_create_in($col, 'parent_id'); //将删除用户的下级的parent_id 改为0
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('users') . " WHERE " . db_create_in($col, 'user_id'); //删除用户
                $GLOBALS['db']->query($sql);
                /* 删除用户订单 */
                $sql = "SELECT order_id FROM " . $GLOBALS['aos']->table('order_info') . " WHERE " . db_create_in($col, 'user_id');
                $GLOBALS['db']->query($sql);
                $col_order_id = $GLOBALS['db']->getCol($sql);
                if ($col_order_id)
                {
                    $sql = "DELETE FROM " . $GLOBALS['aos']->table('order_info') . " WHERE " . db_create_in($col_order_id, 'order_id');
                    $GLOBALS['db']->query($sql);
                    $sql = "DELETE FROM " . $GLOBALS['aos']->table('order_goods') . " WHERE " . db_create_in($col_order_id, 'order_id');
                    $GLOBALS['db']->query($sql);
                }

                $sql = "DELETE FROM " . $GLOBALS['aos']->table('booking_goods') . " WHERE " . db_create_in($col, 'user_id'); //删除用户
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('collect_goods') . " WHERE " . db_create_in($col, 'user_id'); //删除会员收藏商品
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('feedback') . " WHERE " . db_create_in($col, 'user_id'); //删除用户留言
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('user_address') . " WHERE " . db_create_in($col, 'user_id'); //删除用户地址
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('user_bonus') . " WHERE " . db_create_in($col, 'user_id'); //删除用户优惠券
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('user_account') . " WHERE " . db_create_in($col, 'user_id'); //删除用户帐号金额
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('tag') . " WHERE " . db_create_in($col, 'user_id'); //删除用户标记
                $GLOBALS['db']->query($sql);
                $sql = "DELETE FROM " . $GLOBALS['aos']->table('account_log') . " WHERE " . db_create_in($col, 'user_id'); //删除用户日志
                $GLOBALS['db']->query($sql);
            }
        }

        if (isset($this->aoshop) && $this->aoshop)
        {
            /* 如果是aoshop插件直接退出 */
            return;
        }

        $sql = "DELETE FROM " . $this->table($this->user_table) . " WHERE ";
        if (is_array($post_id))
        {
            $sql .= db_create_in($post_id, $this->field_mobile);
        }
        else
        {
            $sql .= $this->field_mobile . "='" . $post_id . "' LIMIT 1";
        }

        $this->db->query($sql);
    }

    /**
     *  获取指定用户的信息
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function get_profile_by_name($mobile)
    {
        $post_username = $username;

        $sql = "SELECT " . $this->field_id . " AS user_id," . $this->field_mobile . " AS mobile," .
                    $this->field_gender ." AS sex,".
                    $this->field_reg_date . " AS reg_time, ".
                    $this->field_pass . " AS password ".
               " FROM " . $this->table($this->user_table) .
               " WHERE " .$this->field_mobile . "='$post_mobile'";
        $row = $this->db->getRow($sql);

        return $row;
    }

    /**
     *  获取指定用户的信息
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function get_profile_by_id($id)
    {
        $sql = "SELECT " . $this->field_id . " AS user_id," . $this->field_mobile . " AS mobile," .
                    $this->field_gender ." AS sex,".
                    $this->field_reg_date . " AS reg_time, ".
                    $this->field_pass . " AS password ".
               " FROM " . $this->table($this->user_table) .
               " WHERE " .$this->field_id . "='$id'";
        $row = $this->db->getRow($sql);

        return $row;
    }

    /**
     *  根据登录状态设置cookie
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function get_cookie()
    {
        $id = $this->check_cookie();
        if ($id)
        {
            $this->set_session($id);
            return true;
        }
        else
        {
            return false;
        }
    }

    /**
     *  检查指定用户是否存在及密码是否正确(重载基类check_user函数，支持zc加密方法)
     *
     * @access  public
     * @param   string  $username   用户名
     *
     * @return  int
     */
    function check_user($mobile, $password = null)
    {
        $post_mobile = $mobile;

        if ($password === null)
        {
            $sql = "SELECT " . $this->field_id .
                   " FROM " . $this->table($this->user_table).
                   " WHERE " . $this->field_mobile . "='" . $post_mobile . "'";

            return $this->db->getOne($sql);
        }
        else
        {
            $sql = "SELECT user_id, password, salt,ec_salt " .
                   " FROM " . $this->table($this->user_table).
                   " WHERE mobile='$post_mobile'";
            $row = $this->db->getRow($sql);
            $ao_salt=$row['ec_salt'];
            if (empty($row))
            {
                return 0;
            }

            if (empty($row['salt']))
            {
                if ($row['password'] != $this->compile_password(array('password'=>$password,'ec_salt'=>$ao_salt)))
                {
                    return 0;
                }
                else
                {
                    if(empty($ao_salt))
                    {
                        $ao_salt=rand(1,9999);
                        $new_password=md5(md5($password).$ao_salt);
                        $sql = "UPDATE ".$this->table($this->user_table)."SET password= '" .$new_password."',ec_salt='".$ao_salt."'".
                   " WHERE mobile='$post_mobile'";
                         $this->db->query($sql);

                    }
                    return $row['user_id'];
                }
            }
            else
            {
                /* 如果salt存在，使用salt方式加密验证，验证通过洗白用户密码 */
                $encrypt_type = substr($row['salt'], 0, 1);
                $encrypt_salt = substr($row['salt'], 1);

                /* 计算加密后密码 */
                $encrypt_password = '';
                switch ($encrypt_type)
                {
                    case 1 :
                        $encrypt_password = md5($encrypt_salt.$password);
                        break;
                    /* 如果还有其他加密方式添加到这里  */
                    //case other :
                    //  ----------------------------------
                    //  break;
                    case 2 :
                        $encrypt_password = md5(md5($password).$encrypt_salt);
                        break;

                    default:
                        $encrypt_password = '';

                }

                if ($row['password'] != $encrypt_password)
                {
                    return 0;
                }

                $sql = "UPDATE " . $this->table($this->user_table) .
                       " SET password = '".  $this->compile_password(array('password'=>$password)) . "', salt=''".
                       " WHERE user_id = '$row[user_id]'";
                $this->db->query($sql);

                return $row['user_id'];
            }
        }
    }

    /**
     *  检查cookie是正确，返回用户名
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function check_cookie()
    {
        return '';
    }
    
    //设置cookie
    function set_cookie($user_id='')
    {
        if (empty($user_id))
        {
            // 摧毁cookie
            $time = time() - 3600;
            setcookie("AOS[user_id]",  '', $time, $this->cookie_path);   
            setcookie("AOS[mobile]", '', $time, $this->cookie_path);
        }
        else
        {
            // 设置cookie 
            $time = time() + 3600 * 24 * 15;
            setcookie("AOS[user_id]", $user_id, $time, $this->cookie_path, $this->cookie_domain);
            $sql = "SELECT user_id FROM " . $GLOBALS['aos']->table('users') . " WHERE user_id='$user_id'";
            $row = $GLOBALS['db']->getOne($sql);
            if ($row)
            {
                setcookie("AOS[user_id]", $row['user_id'], $time, $this->cookie_path, $this->cookie_domain);
            }
        }
    }

    //设置指定用户SESSION
    function set_session ($user_id='')
    {
        if (empty($user_id))
        {
            $GLOBALS['sess']->destroy_session();
        }
        else
        {
            $sql = "SELECT user_id FROM " . $GLOBALS['aos']->table('users') . " WHERE user_id='$user_id'";
            $row = $GLOBALS['db']->getOne($sql);
            if ($row)
            {
                $_SESSION['user_id']   = $row['user_id'];
            }
        }
    }

    /**
     * 在给定的表名前加上数据库名以及前缀
     *
     * @access  private
     * @param   string      $str    表名
     *
     * @return void
     */
    function table($str)
    {
        return '`' .$this->db_name. '`.`'.$this->prefix.$str.'`';
    }

    /**
     *  编译密码函数
     *
     * @access  public
     * @param   array   $cfg 包含参数为 $password, $md5password, $salt, $type
     *
     * @return void
     */
    function compile_password ($cfg)
    {
       if (isset($cfg['password']))
       {
            $cfg['md5password'] = md5($cfg['password']);
       }
       if (empty($cfg['type']))
       {
            $cfg['type'] = 1;
       }

       switch ($cfg['type'])
       {
           case 1 :
                if(!empty($cfg['ec_salt']))
               {
                   return md5($cfg['md5password'].$cfg['ec_salt']);
               }
               else
               {
                    return $cfg['md5password'];
               }

           case 2 :
               if (empty($cfg['salt']))
               {
                    $cfg['salt'] = '';
               }

               return md5($cfg['salt'] . $cfg['md5password']);

           case 3 :
               if (empty($cfg['salt']))
               {
                    $cfg['salt'] = '';
               }

               return md5($cfg['md5password'] . $cfg['salt']);

           default:
               return '';
       }
    }

    function get_user_info($mobile)
    {
        return $this->get_profile_by_mobile($mobile);
    }


    /**
     * 检查有无重名用户，有则返回重名用户
     *
     * @access  public
     * @param
     *
     * @return void
     */
    function test_conflict ($user_list)
    {
        if (empty($user_list))
        {
            return array();
        }


        $sql = "SELECT " . $this->field_mobile . " FROM " . $this->table($this->user_table) . " WHERE " . db_create_in($user_list, $this->field_mobile);
        $user_list = $this->db->getCol($sql);

        return $user_list;
    }
}
