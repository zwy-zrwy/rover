<!DOCTYPE html>
<html id="aos">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="Cache-Control" content="no-cache,no-store,must-revalidate"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="0"/>
<title><?php echo $this->_var['shop_name']; ?></title>
<link rel="shortcut icon" href="favicon.ico">
<link href="<?php echo $this->_var['template_path']; ?>css/common.css?v=<?php echo $this->_var['aos_version']; ?>" rel="stylesheet" />
<link href="<?php echo $this->_var['template_path']; ?>css/swiper.min.css?v=<?php echo $this->_var['aos_version']; ?>" rel="stylesheet" />
<link href="<?php echo $this->_var['template_path']; ?>css/index.css?v=<?php echo $this->_var['aos_version']; ?>" rel="stylesheet" />
<script src="<?php echo $this->_var['template_path']; ?>js/jquery.min.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<script src="<?php echo $this->_var['template_path']; ?>js/common.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<script src="<?php echo $this->_var['template_path']; ?>js/swiper.min.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<script src="<?php echo $this->_var['template_path']; ?>js/dropload.min.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<script src="<?php echo $this->_var['template_path']; ?>js/jquery.lazyload.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<script src="<?php echo $this->_var['template_path']; ?>js/aotime_day.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
</head>
<body id="home">
<?php echo $this->fetch('inc/header.htm'); ?>
<section id="container" class="container pdb" style="display:none">
  <?php 
$k = array (
  'name' => 'ads',
  'id' => '1',
  'num' => '10',
);
echo $this->_echash . $k['name'] . '|' . serialize($k) . $this->_echash;
?>
  <div class="search" onclick="search()">
    <font>搜索商品</font><i class="iconfont icon-search"></i>
  </div>
  <?php if ($this->_var['menu_list']): ?>
  <nav class="quick-nav">
    <?php $_from = $this->_var['menu_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'menu');if (count($_from)):
    foreach ($_from AS $this->_var['menu']):
?>
    <a href="<?php echo $this->_var['menu']['menu_url']; ?>">
      <img src="<?php echo $this->_var['menu']['menu_img']; ?>" alt="<?php echo $this->_var['menu']['menu_name']; ?>">
      <p><?php echo $this->_var['menu']['menu_name']; ?></p>
    </a>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </nav>
  <?php endif; ?>
  <?php if ($this->_var['seckill_goods']): ?>
  <div class="seckill-goods">
    <h3><span>限时秒杀</span></h3>
    <ul>
      <?php $_from = $this->_var['seckill_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'seckill');if (count($_from)):
    foreach ($_from AS $this->_var['seckill']):
?>
      <li>
        <a href="<?php echo $this->_var['seckill']['url']; ?>">
          <img class="lazy" data-original="<?php echo $this->_var['seckill']['goods_img']; ?>" src="uploads/images/no_picture.jpg">
          <p class="time">
            还剩<span class="aotime" data="<?php echo $this->_var['seckill']['seck_end_time']; ?>"></span></p>
          <p class="name"><?php echo $this->_var['seckill']['goods_name']; ?></p>
        </a>
        <p class="foot">
          <span class="price"><?php echo $this->_var['seckill']['seck_price']; ?></span>
          <span class="nub"><?php echo $this->_var['seckill']['sales']; ?>人已买</span>
        </p>
      </li>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </ul>
  </div>
  <?php endif; ?>

  <div class="tuan-list">
      <ul  class="J_tuan_list">
        <?php $_from = $this->_var['lottery_goods']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'lottery');if (count($_from)):
    foreach ($_from AS $this->_var['lottery']):
?>
        <li class="tuan-item">
          <div class="goods-image">
            <a href="<?php echo $this->_var['lottery']['url']; ?>"><img class="lazy" data-original="<?php echo $this->_var['lottery']['tuan_img']; ?>" src="uploads/images/no_tuan_picture.jpg"></a>
            <span>已团<?php echo $this->_var['lottery']['sales']; ?>件</span>
          </div>
          <p class="goods-name"><a href="<?php echo $this->_var['lottery']['url']; ?>"><?php echo $this->_var['lottery']['goods_name']; ?></a></p>
          <div class="detail">
            <div class="left-side">
              <span class="tuan-num"><?php echo $this->_var['lottery']['lottery_tuan_num']; ?>人团</span>
              <span class="sale-price"><?php echo $this->_var['lottery']['lottery_price']; ?></span>
              <label class="label">抽奖</label>
            </div>
            <div class="enter-button"><a href="<?php echo $this->_var['lottery']['url']; ?>">去抽奖</a></div>
          </div>
        </li>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
      </ul>
  </div>
  <?php echo $this->fetch('inc/searchbar.htm'); ?>
</section>
<div class="go-top"><span>顶部</span></div>
<footer>
    <ul class="footer fixed">
      <li class="home"><a href="index.php"><i class="iconfont">&#xe61d;</i>首页</a></li>
    <li class="rank"><a href="index.php?c=rank"><i class="iconfont">&#xe61a;</i>热榜</a></li>
    <li class="catalog"><a href="index.php?c=category"><i class="iconfont">&#xe605;</i>分类</a></li>
    <li class="user"><a href="index.php?c=user"><i class="iconfont">&#xe633;</i>我的</a></li>
  </ul>
</footer>
<script>
  window.onload = function() {
    $('#loading').css('display','none'); 
    $('#container').css('display','');
    var swiper = new Swiper('.swiper-container', {
      pagination: '.swiper-pagination',
      paginationClickable: true, //分页器
      loop: true, //开启循环
      autoplay: 2500,
      autoplayDisableOnInteraction: false //用户操作swiper之后，是否禁止autoplay
    });
    $("img.lazy").lazyload({effect: "fadeIn"});
    setInterval("order_time_ajax()",5000); 
  }
  function search()
  {
    $(".search-view").toggle();
  }
</script>
<script>
$(function(){
    var tab1LoadEnd = false;
    var num = 0;
    var page= 0;
    var dropload = $('.container').dropload({
    scrollArea : window,
    loadDownFn : function(me){
        // 加载菜单一的数据
            page++;
            var counter = page,last = num,amount = 5;
            $.ajax({
                type: 'POST',
                data: {last,amount,page},
                url: 'index.php?c=index&a=index_goods',
                dataType: 'json',
                success: function(data){
                    var result = '';
                    num = amount * counter;
                    last = num;
                    if(typeof(data.info)!="undefined"){
                          for(i = 0; i < data.info.length; i++){
                              result +=data.info[i];  
                          }
                        }
                    // 为了测试，延迟1秒加载
                    setTimeout(function(){
                        $('.J_tuan_list').append(result);
                        $("img.lazy_"+page).lazyload({effect: "fadeIn"});
                        if(last >= data.count){
                            // 数据加载完
                            tab1LoadEnd = true;
                            // 锁定
                            me.lock();
                            // 无数据
                            me.noData();
                        }
                        // 每次数据加载完，必须重置
                        me.resetload();
                    },1);

                },
                error: function(xhr, type){
                    //layer.open({content: 'Ajax error!',skin: 'msg',time: 2});
                    window.location.reload();
                    // 即使加载出错，也得重置
                    me.resetload();
                }
            });
        }
    })
})
</script>
<?php echo $this->fetch('inc/wx_config.htm'); ?>
<?php if ($this->_var['stats_code']): ?><?php echo $this->_var['stats_code']; ?><?php endif; ?>
</body>
</html>
