<?php
if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}
/* 设置默认地址 */
if ($action == 'set_address')
{
    $result = array('error' => 0, 'message' => '');
    $address_id = intval($_GET['id']);
    if($db->query("UPDATE " . $aos->table('users') . " SET address_id = $address_id  WHERE user_id='$user_id'")){ 
        $result['error'] = 1;
        $result['message'] = 'ok';
        $result['address_id'] = $address_id;
        die(json_encode($result));
    }
    else
    {
        $result['error'] = 0;
        $result['message'] = 'no';
        die(json_encode($result));
    }
}

/* 删除收货地址 */
elseif ($action == 'drop_address')
{
    include_once('source/library/user.php');
    $result = array('error' => 0, 'message' => '');
    $address_id = intval($_GET['id']);
    if (drop_consignee($address_id))
    {
        $result['error'] = 1;
        $result['message'] = 'ok';
        $result['address_id'] = $address_id;
        die(json_encode($result));
    }
    else
    {
        $result['error'] = 0;
        $result['message'] = 'no';
        die(json_encode($result));
    }
}

?>