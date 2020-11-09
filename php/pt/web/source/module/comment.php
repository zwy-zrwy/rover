<?php

/*用户评论*/

if (!defined('IN_AOS'))
{
    die('Hacking attempt');
}

$goods_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
if ($action == 'index')
{
    $sql= "SELECT COUNT(*) from ".$GLOBALS['aos']->table('comment')." where id_value='".$goods_id."'  AND status = 1";
    $count = $GLOBALS['db']->getOne($sql);
    $smarty->assign('count',$count);
    $smarty->assign('goods_id',$goods_id);
    $smarty->display('comment.htm');
}
elseif ($action == 'comment_ajax')
{
    
    $last = !empty($_POST['last'])?intval($_POST['last']):'0';
    $amount = !empty($_POST['amount'])?intval($_POST['amount']):'0';
    $limit = "limit $last,$amount";//每次加载的个数
    $comment_list = get_goods_comment($goods_id,$limit);
    foreach($comment_list['list'] as $val){
        $GLOBALS['smarty']->assign('comment',$val);
        $res['info'][]  = $GLOBALS['smarty']->fetch('inc/comments_list.htm');
    }
    $res['count']=$comment_list['count'];
    die(json_encode($res));
  
}
elseif ($action == 'create')
{
    $user_id = $_SESSION['user_id'];
    if (empty($user_id)) {
        exit();
    }
    $content      = trim($_POST['comment']);
    $comment_rank = intval($_POST['rank']);
    $id_value     = intval($_POST['id_value']);
    $add_time     = gmtime();
    $order_id     = intval($_POST['order_id']);

    $sql = "select comment_id from ".$aos->table('comment')." where user_id = '".$user_id."' and id_value = '".$id_value."' and order_id = '".$order_id."' ";
    $comment_id = $db->getOne($sql);
    if ($comment_id) {
        $res = array(
            'isError' => 1,
            'message' => '已评论过了！'
        );
        echo json_encode($res);
        exit();
    }

    
    $sql = "insert into ".$aos->table('comment')." (`user_id`,`content`,`comment_rank`,`id_value`,`add_time`,`order_id`) values ('$user_id','$content','$comment_rank','$id_value','$add_time','$order_id')";
    $db->query($sql);
    $id = $db->insert_id();
    if ($id) {
        $db->query('update '.$aos->table('order_info').' set `comment` = 1 where `order_id` = "'.$order_id.'"');
        $res = array(
            'isError' => 0
        );
    }
    else{
        $res = array(
            'isError' => 1,
            'message' => '评论失败，请重试！'
        );
    }
    echo json_encode($res);
    exit();
}

/*商品评论*/
function get_goods_comment($goods_id,$limit='')
{
    $sql= "SELECT COUNT(*) from ".$GLOBALS['aos']->table('comment')." where id_value='".$goods_id."'  AND status = 1";
    $count = $GLOBALS['db']->getOne($sql);

    $sql = 'SELECT c.*,u.nickname,u.user_id,og.goods_attr FROM ' . $GLOBALS['aos']->table('comment') ." as c left join "
    .$GLOBALS['aos']->table("users")." as u on u.user_id=c.user_id left join "
    .$GLOBALS['aos']->table("order_goods")." as og on og.order_id=c.order_id ".
            " WHERE c.id_value = ".$goods_id." AND c.status = 1".
            '  ORDER BY c.comment_id DESC '.$limit;
    $list = $GLOBALS['db']->GetAll($sql);
    foreach($list as $idx=>$value)
    {
        $list[$idx]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
        $list[$idx]['headimgurl'] = getAvatar($value['user_id']);
    }
    $res['list'] = $list;
    $res['count'] = $count;
    return $res;
}

?>