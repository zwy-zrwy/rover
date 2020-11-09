<?php
/*商品详情*/
if (!defined('IN_AOS'))
{
  die('Hacking attempt');
}
include_once(ROOT_PATH . 'source/library/order.php');
$goods_id = isset($_REQUEST['id'])  ? intval($_REQUEST['id']) : 0;
$cache_id = sprintf('%X', crc32('goods-'.$goods_id));
if (!$smarty->is_cached('goods.htm', $cache_id))
{
  $goods = get_goods_info($goods_id);
  if ($goods === false || empty($goods['is_on_sale']))
  {
    aos_header("Location: ./index.php\n");
    exit;
  }
  else
  {
    assign_template();
    $smarty->assign('sales', get_cum_sales($goods['goods_id']));
    $smarty->assign('goods',              $goods);

    $smarty->assign('goods_id',           $goods['goods_id']);

    if(!empty($goods['goods_label'])){
      $smarty->assign('goods_label',      get_label_list($goods['goods_label']));
    }

    $goods_desc = str_replace('<img src="','<img class="lazy" src="uploads/images/no_tuan_picture.jpg" data-original="',$goods['goods_desc']);
    $smarty->assign('goods_desc',$goods_desc); 
    $smarty->assign('goods_sku', get_sku_list($goods['goods_id']));
    $smarty->assign('album', get_goods_album($goods['goods_id']));
    $smarty->assign('rand_goods', rand_goods($goods['goods_id']));
    $tuan_price_list = get_tuan_price_list($goods['goods_id']);
    $tuan_price_num = count($tuan_price_list);
    $tuan_num = array_column($tuan_price_list,'number');
    $tuan_price = array_column($tuan_price_list,'price');
    $smarty->assign('tuan_price_list',$tuan_price_list);
    $smarty->assign('tuan_price_num',$tuan_price_num);
    $smarty->assign('min_tuan_num',min($tuan_num));
    $smarty->assign('max_tuan_num',max($tuan_num));
    $smarty->assign('tuan_price',max($tuan_price));
    $smarty->assign('tuan_price_formated',price_format(max($tuan_price)));
    $smarty->assign('difference',min($tuan_num)-1);
    $smarty->assign('comments',get_goods_comment($goods['goods_id']));
    $share['title'] = mb_substr($goods['goods_name'], 0,30,'utf-8');
    $share['desc'] = mb_substr(str_replace("\r\n","",$goods['goods_brief']), 0,50,'utf-8');

    $imgUrl = get_share_img($goods['goods_id']);
    $share['imgUrl'] = $aos->url().$imgUrl;
    $share['link'] = AOS_HTTP .$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    $smarty->assign('share',       $share);
  }
}


/* 更新点击次数 */
$db->query('UPDATE ' . $aos->table('goods') . " SET click_count = click_count + 1 WHERE goods_id = '$goods[goods_id]'");
$smarty->assign('now_time',  gmtime());           // 当前系统时间
$smarty->display('goods.htm',$cache_id);

/*商品评论*/
function get_goods_comment($goods_id)
{
    $sql= "SELECT COUNT(*) from ".$GLOBALS['aos']->table('comment')." where id_value='".$goods_id."' AND status = 1";
    $count = $GLOBALS['db']->getOne($sql);

    $number  = !empty($GLOBALS['_CFG']['comments_number']) ? $GLOBALS['_CFG']['comments_number'] : 2;
    $sql = 'SELECT c.*,u.nickname,u.user_id,og.goods_attr FROM ' . $GLOBALS['aos']->table('comment') ." as c left join "
    .$GLOBALS['aos']->table("users")." as u on u.user_id=c.user_id left join "
    .$GLOBALS['aos']->table("order_goods")." as og on og.order_id=c.order_id ".
            " WHERE c.id_value = ".$goods_id." AND c.status = 1 AND is_top = 1".
            ' ORDER BY c.comment_id DESC LIMIT '.$number;
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