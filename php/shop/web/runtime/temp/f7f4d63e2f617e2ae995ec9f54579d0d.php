<?php /*a:1:{s:57:"D:\SVN\zhouweiyao\application\index\view\index\index.html";i:1574231869;}*/ ?>

<!doctype html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>首页</title>
    <link rel="stylesheet" type="text/css" href="/static/css/base.css" />
    <link rel="stylesheet" type="text/css" href="/static/css/style.css" />
    <link rel="stylesheet" href="/static/css/slide.css" />
</head>

<body>
<div class="top_banner">
    <div class="module w1200">
        <a href="javascript:">
            <img src="/static/images/active.jpg" />
            <i class="icon-close"><img src="/static/images/close.png"/></i>
        </a>
    </div>
</div>
<div class="site-nav" id="site-nav">
    <div class="w w1200">
        <div class="fl">
            <div class="city-choice" id="city-choice" data-ectype="dorpdown">
                <div class="dsc-choie dsc-cm" ectype="dsc-choie">
                    <img src="/static/images/place.png" class="place" />
                    <input type="text" value="北京" class="search" />
                </div>

            </div>
            <div class="txt-info" id="ECS_MEMBERZONE">
                <?php if(app('session')->get('user_name')): ?><a href="<?php echo url('user/index/index'); ?>" class="link-regist"><?php echo htmlentities(app('session')->get('user_name')); ?></a>
                <a href="<?php echo url('user/login/logout'); ?>" class="link-login red">退出登录</a>
                <?php else: ?>
                <a href="<?php echo url('user/login/index'); ?>" class="link-login red">请登录</a>
                <?php endif; ?>
            </div>
        </div>
        <ul class="quick-menu fr">
            <li>
                <div class="dt">
                    <a href="<?php echo url('flow/index'); ?>">我的订单</a>
                </div>
            </li>
            <li class="spacer"></li>
            <li>
                <div class="dt">
                    <a href="#">我的浏览</a>
                </div>
            </li>
            <li class="spacer"></li>
            <li>
                <div class="dt">
                    <a href="#">我的收藏</a>
                </div>
            </li>
            <li class="spacer"></li>
            <li>
                <div class="dt">
                    <a href="#">客户服务</a>
                </div>
            </li>
            <li class="spacer"></li>
            <li class="li_dorpdown" data-ectype="dorpdown">
                <div class="dt dsc-cm">网站导航</div>
                <div class="dd dorpdown-layer" style="z-index: 10000000;">
                    <dl class="fore1">
                        <dt>特色主题</dt>
                        <dd>
                            <div class="item">
                                <a href="#" target="_blank">家用电器</a>
                            </div>
                            <div class="item">
                                <a href="#" target="_blank">手机数码</a>
                            </div>
                            <div class="item">
                                <a href="#" target="_blank">电脑办公</a>
                            </div>
                        </dd>
                    </dl>
                    <dl class="fore2">
                        <dt>促销活动</dt>
                        <dd>
                            <div class="item">
                                <a href="#">拍卖活动</a>
                            </div>
                            <div class="item">
                                <a href="#">共创商品</a>
                            </div>
                            <div class="item">
                                <a href="#">优惠活动</a>
                            </div>
                            <div class="item">
                                <a href="#">批发市场</a>
                            </div>
                            <div class="item">
                                <a href="#">超值礼包</a>
                            </div>
                            <div class="item">
                                <a href="#">优惠券</a>
                            </div>
                        </dd>
                    </dl>
                </div>
            </li>
        </ul>
    </div>
</div>
<div class="clear"></div>
<div class="header">
    <div class="header_container">
        <div class="logo fl">
            <img src="/static/images/logo.png" />
            <a href="#"><img src="/static/images/ecsc-join.gif" /></a>
        </div>
        <div class="dsc-search">
            <div class="form">
                <form class="search-form">
                    <input name="keywords" type="text" id="keyword" value="" class="search-text" style="color: rgb(153, 153, 153);">
                    <button type="submit" class="button button-goods">搜商品</button>
                    <button type="submit" class="button button-store">搜店铺</button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="clear"></div>
<div class="nav" ectype="dscNav">
    <div class="w w1200">
        <div class="categorys ">
            <div class="categorys-type">
                <a href="categoryall.php" target="_blank">全部商品分类</a>
            </div>
            <div class="categorys-tab-content">

                <div class="categorys-items" id="cata-nav">
                    <?php if(is_array($cate) || $cate instanceof \think\Collection || $cate instanceof \think\Paginator): $i = 0; $__LIST__ = $cate;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                    <div class="categorys-item">
                        <div class="item item-content">
                            <div class="categorys-title">
                                <strong>
                                    <a href="<?php echo url('category/index',['id'=>$vo['id']]); ?>" target="_blank"><?php echo htmlentities($vo['name']); ?></a>
                                </strong>
                                <span>
                                    <?php if(is_array($vo['sub']) || $vo['sub'] instanceof \think\Collection || $vo['sub'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['sub'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?>
                                            <a href="<?php echo url('category/index',['id'=>$sub['id']]); ?>" target="_blank"><?php echo htmlentities($sub['name']); ?></a>
                                    <?php endforeach; endif; else: echo "" ;endif; ?>
                                </span>
                            </div>
                        </div>
                        <div class="categorys-items-layer">
                            <div class="cate-layer-con clearfix">
                                <div class="cate-layer-left">
                                    <div class="cate_detail">
                                            <?php if(is_array($vo['sub']) || $vo['sub'] instanceof \think\Collection || $vo['sub'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['sub'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?>
                                        <dl class="dl_fore1">
                                            <dt>
                                                <a href="<?php echo url('category/index',['id'=>$sub['id']]); ?>" target="_blank"><?php echo htmlentities($sub['name']); ?></a>
                                            </dt>
                                            <dd>
                                                <?php if(is_array($sub['sub']) || $sub['sub'] instanceof \think\Collection || $sub['sub'] instanceof \think\Paginator): $i = 0; $__LIST__ = $sub['sub'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$child): $mod = ($i % 2 );++$i;?>
                                                <a href="<?php echo url('category/index',['id'=>$child['id']]); ?>" target="_blank"><?php echo htmlentities($child['name']); ?></a>
                                                <?php endforeach; endif; else: echo "" ;endif; ?>
                                            </dd>
                                        </dl>
                                        <?php endforeach; endif; else: echo "" ;endif; ?>
                                    </div>
                                </div>
                                <div class="cate-layer-rihgt">
                                    <h3>猜你喜欢</h3>
                                    <div class="cate-layer-right-slide">
                                        <img src="/static/images/elec1.jpg" />
                                    </div>
                                    <div class="cate-layer-right-slide">
                                        <img src="/static/images/elec1.jpg" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <?php endforeach; endif; else: echo "" ;endif; ?>
                </div>
            </div>
        </div>
        <div class="nav-main" id="nav">
            <ul class="navitems">
                <li>
                    <a href="<?php echo url('index/index'); ?>" class="curr">首页</a>
                </li>
                <li>
                    <a href="#">食品特产</a>
                </li>
                <li>
                    <a href="#">服装城</a>
                </li>
                <li>
                    <a href="#">大家电</a>
                </li>
                <li>
                    <a href="#">鞋靴箱包</a>
                </li>
                <li>
                    <a href="<?php echo url('brand/index'); ?>">品牌专区</a>
                </li>
                <li>
                    <a href="#">聚划算</a>
                </li>
                <li>
                    <a href="#">积分商城</a>
                </li>
                <li>
                    <a href="#">预售</a>
                </li>
                <li>
                    <a href="#">店铺街</a>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="slide" id="lun2" style="">
    <div class="carouse" style="min-width: 1200px;position:relative;margin-top: 0px;">
        <div class="slideItem">
            <a href="#" target="_blank"><img class="banner-img" src="/static/images/banner4.jpg" style="height: 500px;"></a>
        </div>
        <div class="slideItem">
            <a href="#" target="_blank"><img class="banner-img" src="/static/images/banner5.jpg" style="height: 500px;"></a>
        </div>
        <div class="slideItem">
            <a href="#" target="_blank"><img class="banner-img" src="/static/images/banner6.jpg" style="height: 500px;"></a>
        </div>
        <!--<a class="carousel-control left Next"></a>
        <a class="carousel-control right Previous"></a>-->
    </div>
    <!-- 轮播底部圆点 -->
    <div class="dotList"></div>
</div>
<div class="shop_new">
    <div class="sy_c">
        <div class="sy_gg">
            <div class="shop_new_logo">
                <img src="/static/images/new1.png" />
                <img src="/static/images/new3.png" />
            </div>
            <div class="ranklist">
                <ul>
                    <li>
                        <a href="#">小米：58秒售罄！魅族：抱歉，我30秒</a>
                        <a href="#"><span>点我查看详情</span></a>
                    </li>
                    <li>
                        <a href="#">穿这样的裙子会显高，你穿过几种？</a>
                        <a href="#"><span>点我查看详情</span></a>
                    </li>
                    <li>
                        <a href="#">蓄电池专场下单立减100元</a>
                        <a href="#"><span>点我查看详情</span></a>
                    </li>
                    <li>
                        <a href="#">五星双人自助低至299元</a>
                        <a href="#"><span>点我查看详情</span></a>
                    </li>
                    <li>
                        <a href="#">天府大件运营中心开仓公告</a>
                        <a href="#"><span>点我查看详情</span></a>
                    </li>
                </ul>
            </div>
            <div class="new_more">
                <a href="#"><img src="/static/images/new_more.png" /></a>
            </div>
        </div>
    </div>
</div>
<div class="seckill-channel" id="h-seckill">
    <div class="box-hd clearfix">
        <i class="box_hd_arrow"></i>
        <i class="box_hd_dec"><img src="/static/images/box_hd_arrow.png"/></i>
        <i class="box-hd-icon"></i>
        <div class="sk-time-cd">
            <div class="sk-cd-tit">距结束</div>
            <div class="cd-time fl" ectype="time" data-time="2017-12-18 10:00:00">
                <div class="days hide">00</div>
                <span class="split hide">天</span>
                <div class="hours">00</div>
                <span class="split">时</span>
                <div class="minutes">00</div>
                <span class="split">分</span>
                <div class="seconds">00</div>
                <span class="split">秒</span>
            </div>
        </div>
        <div class="sk-more">
            <a href="seckill.php" target="_blank">更多秒杀</a> <i class="arrow"></i></div>
    </div>
    <div class="box-bd clearfix slideTxtBox">
        <div class="tempWrap hd">
            <ul>
                <li class="opacity_img clone">
                    <div class="p-img">
                        <a href="#" target="_blank"><img src="/static/images/miaosha0.jpg"></a>
                    </div>
                    <div class="p-name">
                        <a href="#" target="_blank" title="HLA/海澜之家撞色长袖T恤春季热卖圆领修身拼接T恤男 简约圆领 微弹修身 撞色拼接 触感柔软">HLA/海澜之家撞色长袖T恤春季热卖圆领修身拼接T恤男 简约圆领 微弹修身 撞色拼接 触感柔软</a>
                    </div>
                    <div class="p-over">
                        <div class="p-info">
                            <div class="p-price">
                                <span class="shop-price"><em>¥</em>356.00</span>
                                <span class="original-price"><em>¥</em>117.60</span>
                            </div>
                        </div>
                        <div class="p-btn">
                            <a href="#" target="_blank">立即抢购</a>
                        </div>
                    </div>
                </li>
                <li class="opacity_img">
                    <div class="p-img">
                        <a href="#" target="_blank"><img src="/static/images/miaosha0.jpg"></a>
                    </div>
                    <div class="p-name">
                        <a href="#" target="_blank" title="特大号加厚塑料收纳箱整理箱有盖收纳盒衣服被子置物周转储物箱子">特大号加厚塑料收纳箱整理箱有盖收纳盒衣服被子置物周转储物箱子</a>
                    </div>
                    <div class="p-over">
                        <div class="p-info">
                            <div class="p-price">
                                <span class="shop-price"><em>¥</em>1423.00</span>
                                <span class="original-price"><em>¥</em>240.00</span>
                            </div>
                        </div>
                        <div class="p-btn">
                            <a href="#" target="_blank">立即抢购</a>
                        </div>
                    </div>
                </li>
                <li class="opacity_img">
                    <div class="p-img">
                        <a href="#" target="_blank"><img src="/static/images/miaosha0.jpg"></a>
                    </div>
                    <div class="p-name">
                        <a href="#" target="_blank" title="74超薄非球面镜片高度近视眼镜片近视镜片防蓝光配眼镜镜片加工 套餐价低至359元 6款镜架任您选">74超薄非球面镜片高度近视眼镜片近视镜片防蓝光配眼镜镜片加工 套餐价低至359元 6款镜架任您选</a>
                    </div>
                    <div class="p-over">
                        <div class="p-info">
                            <div class="p-price">
                                <span class="shop-price"><em>¥</em>356.00</span>
                                <span class="original-price"><em>¥</em>478.79</span>
                            </div>
                        </div>
                        <div class="p-btn">
                            <a href="#" target="_blank">立即抢购</a>
                        </div>
                    </div>
                </li>
                <li class="opacity_img">
                    <div class="p-img">
                        <a href="#" target="_blank"><img src="/static/images/miaosha0.jpg"></a>
                    </div>
                    <div class="p-name">
                        <a href="#" target="_blank" title="南极人法兰绒毛毯加厚秋单人双人珊瑚绒毯子双层冬季被子盖毯 加厚保暖 不掉毛 柔软面料 亲肤透气">南极人法兰绒毛毯加厚秋单人双人珊瑚绒毯子双层冬季被子盖毯 加厚保暖 不掉毛 柔软面料 亲肤透气</a>
                    </div>
                    <div class="p-over">
                        <div class="p-info">
                            <div class="p-price">
                                <span class="shop-price"><em>¥</em>1641.00</span>
                                <span class="original-price"><em>¥</em>180.00</span>
                            </div>
                        </div>
                        <div class="p-btn">
                            <a href="#" target="_blank">立即抢购</a>
                        </div>
                    </div>
                </li>
                <li class="opacity_img">
                    <div class="p-img">
                        <a href="#" target="_blank"><img src="/static/images/miaosha0.jpg"></a>
                    </div>
                    <div class="p-name">
                        <a href="#" target="_blank" title="李军塑料防滑衣架衣挂撑子裤架衣服架成人晾衣架晒衣架子儿童衣架 成人衣架普通 款38cm 50个只要 21元">李军塑料防滑衣架衣挂撑子裤架衣服架成人晾衣架晒衣架子儿童衣架 成人衣架普通 款38cm 50个只要 21元</a>
                    </div>
                    <div class="p-over">
                        <div class="p-info">
                            <div class="p-price">
                                <span class="shop-price"><em>¥</em>2423.00</span>
                                <span class="original-price"><em>¥</em>25.20</span>
                            </div>
                        </div>
                        <div class="p-btn">
                            <a href="#" target="_blank">立即抢购</a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="need-channel clearfix" id="h-need">
    <div class="channel-column" style="background:url(/static/images/channel_bg1.jpg) no-repeat;">
        <div class="column-title">
            <h3>优质新品</h3>
            <p>专注生活美学</p>
        </div>
        <div class="column-img"><img src="/static/images/channel.png"></div>
        <a href="#" target="_blank" class="column-btn">去看看</a>
    </div>
    <div class="channel-column" style="background:url(/static/images/channel_bg2.jpg)  no-repeat;">
        <div class="column-title">
            <h3>品牌精选</h3>
            <p>潮牌尖货 初春换新</p>
        </div>
        <div class="column-img"><img src="/static/images/channel.png"></div>
        <a href="#" target="_blank" class="column-btn">去看看</a>
    </div>
    <div class="channel-column" style="background:url(/static/images/channel_bg3.jpg) no-repeat;">
        <div class="column-title">
            <h3>潮流女装</h3>
            <p>春装流行款抢购</p>
        </div>
        <div class="column-img"><img src="/static/images/channel.png"></div>
        <a href="#" target="_blank" class="column-btn">去看看</a>
    </div>
    <div class="channel-column" style="background:url(/static/images/channel_bg4.jpg) no-repeat;">
        <div class="column-title">
            <h3>人气美鞋</h3>
            <p>新外貌“鞋”会</p>
        </div>
        <div class="column-img"><img src="/static/images/channel.png"></div>
        <a href="#" target="_blank" class="column-btn">去看看</a>
    </div>
    <div class="channel-column" style="background:url(/static/images/channel_bg5.jpg) no-repeat;">
        <div class="column-title">
            <h3>护肤彩妆</h3>
            <p>春妆必买清单 低至3折</p>
        </div>
        <div class="column-img"><img src="/static/images/channel.png"></div>
        <a href="#" target="_blank" class="column-btn">去看看</a>
    </div>
</div>

<div class="discount">
    <div class="dis_con">
        <a href="#"><img src="/static/images/discount1.png" /></a>
    </div>
</div>
<div class="w1200_container">
    <div class="enjoy_title">
        <h3>享品质 享生活</h3>
    </div>
    <div  class="enjoy_content">
        <div class="enjoy_con">
            <a href="#">
                <div class="enjoy_bg">
                    <div class="enjoy_info">
                        <div class="enjoy1_title">
                            <h4>新品首发</h4>
                            <p>荣耀系列 今日特惠</p>
                        </div>
                    </div>
                    <img src="/static/images/enjoy1.png" class="enter1_enjoy" />
                </div>
            </a>
        </div>
        <div class="enjoy_con">
            <a href="#">
                <div class="enjoy_bg">
                    <div class="enjoy_info" style="background: #DBCF6E;">
                        <div class="enjoy1_title">
                            <h4>会逛</h4>
                            <p>厨具超级品牌日</p>
                        </div>
                    </div>
                    <img src="/static/images/enjoy2.jpg" class="enter1_enjoy" />
                </div>
            </a>
        </div>
        <div class="enjoy_con">
            <a href="#">
                <div class="enjoy_bg">
                    <div class="enjoy_info" style="background: #534b5d;">
                        <div class="enjoy1_title">
                            <h4>奢侈大牌</h4>
                            <p>尽享品质生活</p>
                        </div>
                    </div>
                    <img src="/static/images/enjoy3.jpg" class="enter1_enjoy" />
                </div>
            </a>
        </div>
        <div class="enjoy_con">
            <a href="#">
                <div class="enjoy_bg">
                    <div class="enjoy_info" style="background: #3b838c;">
                        <div class="enjoy1_title">
                            <h4>智能生活</h4>
                            <p>智能潮货，嗨购不停</p>
                        </div>
                    </div>
                    <img src="/static/images/enjoy4.jpg" class="enter1_enjoy" />
                </div>
            </a>
        </div>
        <div class="enjoy_con">
            <a href="#">
                <div class="enjoy_bg">
                    <div class="enjoy_info" style="background:#d58717;">
                        <div class="enjoy1_title">
                            <h4>京东超市</h4>
                            <p>精选超值好货 天天特价</p>
                        </div>
                    </div>
                    <img src="/static/images/enjoy5.jpg" class="enter1_enjoy" />
                </div>
            </a>
        </div>
        <div class="enjoy_con">
            <a href="#">
                <div class="enjoy_bg">
                    <div class="enjoy_info" style="background: #7e5944">
                        <div class="enjoy1_title">
                            <h4>白条商城</h4>
                            <p>最高12期免息</p>
                        </div>
                    </div>
                    <img src="/static/images/enjoy6.jpg" class="enter1_enjoy" />
                </div>
            </a>
        </div>
    </div>
    <div class="clear"></div>
    <!--达人专区-->
    <div class="enjoy_title">
        <h3>达人专区</h3>
    </div>
    <div class="mastor_con">
        <div class="mastor_part" style="background: url(/static/images/vip1.jpg); center center no-repeat">
            <div class="master_main">
                <div class="mastor_title">
                    <h3>纯棉质品</h3>
                    <span>把好货带回家</span>
                </div>
                <a href="#" class="master_btn" target="_blank" style="color:#7bd1f6;;">去见识</a>
                <div class="master_img">
                    <a href="#" target="_blank">
                        <img src="/static/images/vip_shop1.png">
                    </a>
                </div>
            </div>
        </div>
        <div class="mastor_part" style="background: url(/static/images/vip2.jpg); center center no-repeat">
            <div class="master_main">
                <div class="mastor_title">
                    <h3>团购热卖</h3>
                    <span>都是好货</span>
                </div>
                <a href="#" class="master_btn" target="_blank" style="color:#f75674;">去见识</a>
                <div class="master_img">
                    <a href="#" target="_blank">
                        <img src="/static/images/vip_shop2.png">
                    </a>
                </div>
            </div>
        </div>
        <div class="mastor_part" style="background: url(/static/images/vip3.jpg); center center no-repeat">
            <div class="master_main">
                <div class="mastor_title">
                    <h3>团购热卖</h3>
                    <span>每一款都是好货</span>
                </div>
                <a href="#" class="master_btn" target="_blank" style="color:#ff889e;">去见识</a>
                <div class="master_img">
                    <a href="#" target="_blank">
                        <img src="/static/images/vip_shop3.png">
                    </a>
                </div>
            </div>
        </div>
        <div class="mastor_part" style="background: url(/static/images/vip4.jpg); center center no-repeat">
            <div class="master_main">
                <div class="mastor_title">
                    <h3>舒适童鞋</h3>
                    <span>帮宝宝走路</span>
                </div>
                <a href="#" class="master_btn" target="_blank" style="color:#cd51eb;">去见识</a>
                <div class="master_img">
                    <a href="#" target="_blank">
                        <img src="/static/images/vip_shop4.png">
                    </a>
                </div>
            </div>
        </div>
        <div class="mastor_part" style="background: url(/static/images/vip5.jpg); center center no-repeat">
            <div class="master_main">
                <div class="mastor_title">
                    <h3>舒适运动鞋</h3>
                    <span>品牌直降</span>
                </div>
                <a href="#" class="master_btn" target="_blank" style="color:#43dd72;">去见识</a>
                <div class="master_img">
                    <a href="#" target="_blank">
                        <img src="/static/images/vip_shop5.png">
                    </a>
                </div>
            </div>
        </div>
    </div>
    <div class="clear"></div>
    <div class="enjoy_title">
        <h3>店铺推荐</h3>
    </div>
    <div class="good_shop_con">
        <div class="good_shop">
            <a href="#" target="_blank">
                <div class="shop_img">
                    <img src="/static/images/good_shop1.png" />
                </div>
                <div class="shop_logo">
                    <div class="s_logo">
                        <img src="/static/images/shop_logo.png" />
                    </div>
                    <div class="s_title">
                        <p>美宝莲</p>
                        <p>纽约高街潮妆</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="good_shop">
            <a href="#" target="_blank">
                <div class="shop_img">
                    <img src="/static/images/good_shop2.png" />
                </div>
                <div class="shop_logo">
                    <div class="s_logo">
                        <img src="/static/images/shop_logo1.png" />
                    </div>
                    <div class="s_title">
                        <p>三只松鼠</p>
                        <p>就是这个味</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="good_shop">
            <a href="#" target="_blank">
                <div class="shop_img">
                    <img src="/static/images/good_shop3.png" />
                </div>
                <div class="shop_logo">
                    <div class="s_logo">
                        <img src="/static/images/shop_logo2.png" />
                    </div>
                    <div class="s_title">
                        <p>绿联旗舰店</p>
                        <p>给生活多点精彩</p>
                    </div>
                </div>
            </a>
        </div>
        <div class="good_shop">
            <a href="#" target="_blank">
                <div class="shop_img">
                    <img src="/static/images/good_shop4.png" />
                </div>
                <div class="shop_logo">
                    <div class="s_logo">
                        <img src="/static/images/shop_logo3.png" />
                    </div>
                    <div class="s_title">
                        <p>韩都衣舍</p>
                        <p>满249减50</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    <div class="clear"></div>
    <div class="enjoy_title">
        <h3>品牌推荐</h3>
    </div>
    <div class="brand_con">
        <div class="brand_left">
            <a href="#" target="_blank">
                <img src="/static/images/brand.jpg" />
            </a>
        </div>
        <div class="brand_right" id="recommend_brands">
            <ul>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (1).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count0">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (2).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count1">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (3).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (4).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (5).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (6).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (7).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (8).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (9).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (10).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (11).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (12).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (13).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (14).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (15).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (16).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (17).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
                <li>
                    <div class="brand_img">
                        <a href="#" target="_blank">
                            <img src="/static/images/brand (18).jpg" />
                        </a>
                    </div>
                    <div class="brand_mash">
                        <div class="coupon">
                            <a href="#" target="_blank">
                                关注人数<br>
                                <div id="count2">0</div>
                            </a>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div class="clear"></div>
    <?php if(is_array($floor) || $floor instanceof \think\Collection || $floor instanceof \think\Paginator): $i = 0; $__LIST__ = $floor;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
    <div class="enjoy_title">
        <h3><?php echo htmlentities($vo['name']); ?></h3>
    </div>
    <div class="no_enough">
        <ul>
            <?php if(is_array($vo['goods']) || $vo['goods'] instanceof \think\Collection || $vo['goods'] instanceof \think\Paginator): $i = 0; $__LIST__ = $vo['goods'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$goods): $mod = ($i % 2 );++$i;?>
            <li class="opacity_img1">
                <a href="#" target="_blank">
                    <div class="p_img">
                        <a href="<?php echo url('goods/index',['id'=>$goods['id']]); ?>"><img src="/uploads/<?php echo htmlentities($goods['pic']); ?>" /></a>
                    </div>
                    <div class="no_ename" title="<?php echo htmlentities($goods['name']); ?>">
                        <?php echo htmlentities($goods['content']); ?>
                    </div>
                    <div class="no_eprice">
                        <em>¥</em><?php echo htmlentities($goods['price']); ?>
                    </div>
                </a>
            </li>
            <?php endforeach; endif; else: echo "" ;endif; ?>
        </ul>
    </div>
    <div class="clear"></div>
    <?php endforeach; endif; else: echo "" ;endif; ?>
</div>
<!--底部-->
<div class="footer_con">
    <div class="fnc_container">
        <div class="help-list">
            <div class="help-item">
                <h3>新手上路 </h3>
                <ul>
                    <li>
                        <a href="#" title="售后流程" target="_blank">售后流程</a>
                    </li>
                    <li>
                        <a href="#" title="购物流程" target="_blank">购物流程</a>
                    </li>
                    <li>
                        <a href="#" title="订购方式" target="_blank">订购方式</a>
                    </li>
                </ul>

            </div>
            <div class="help-item">
                <h3>配送与支付 </h3>
                <ul>
                    <li>
                        <a href="#" title="货到付款区域" target="_blank">货到付款区域</a>
                    </li>
                    <li>
                        <a href="#" title="配送支付智能查询 " target="_blank">配送支付智能查询</a>
                    </li>
                    <li>
                        <a href="#" title="支付方式说明" target="_blank">支付方式说明</a>
                    </li>
                </ul>

            </div>
            <div class="help-item">
                <h3>会员中心</h3>
                <ul>
                    <li>
                        <a href="#" title="资金管理" target="_blank">资金管理</a>
                    </li>
                    <li>
                        <a href="#" title="我的收藏" target="_blank">我的收藏</a>
                    </li>
                    <li>
                        <a href="<?php echo url('flow/index'); ?>" title="我的订单" target="_blank">我的订单</a>
                    </li>
                </ul>

            </div>
            <div class="help-item">
                <h3>服务保证 </h3>
                <ul>
                    <li>
                        <a href="#" title="退换货原则" target="_blank">退换货原则</a>
                    </li>
                    <li>
                        <a href="#" title="售后服务保证" target="_blank">售后服务保证</a>
                    </li>
                    <li>
                        <a href="#" title="产品质量保证 " target="_blank">产品质量保证</a>
                    </li>
                </ul>

            </div>
            <div class="help-item">
                <h3>联系我们 </h3>
                <ul>
                    <li>
                        <a href="#" title="网站故障报告" target="_blank">网站故障报告</a>
                    </li>
                    <li>
                        <a href="#" title="选机咨询 " target="_blank">选机咨询</a>
                    </li>
                    <li>
                        <a href="#" title="投诉与建议 " target="_blank">投诉与建议</a>
                    </li>
                </ul>
            </div>
            <div class="help-item">
                <h3>关注我们 </h3>
                <img src='/static/images/erweima.png' />
            </div>

        </div>
    </div>
</div>
<!--右侧红包栏-->
<div class="bk_foot_redbag">
    <a href="javascript:void(0)" id="red_bag">
        <img src="/static/images/red_package.png" width="100%" alt="">
    </a>
    <span class="closehd"></span>
</div>
<div class="red_bag">
    <span class="hide font"><label for="">10</label>元红包砸中了您</span><br />
    <button class="hide font" id="ensure">确定</button>
</div>
<!--<div class="red_bag1">-->
    <!--<span class="hide font"><label for=""></label>红包离您而去</span><br />-->
    <!--<button class="hide font" id="ensure">确定</button>-->
<!--</div>-->
</body>
<script src="/static/layui/layui.js"></script>
<script>
    layui.use(['layer','jquery'], function() {
        var $ = layui.jquery,
            layer = layui.layer;
    });
</script>
<script type="text/javascript" src="/static/js/jquery.min.js"></script>
<script type="text/javascript" src="/static/js/kuCity.js"></script>
<script type="text/javascript" src="/static/js/jquery.SuperSlide.js"></script>
<script type="text/javascript" src="/static/js/slide.js"></script>
<script>
    $('.li_dorpdown').hover(function() {
        $('.dorpdown-layer').show();
    });
    $('.li_dorpdown').mouseleave(function() {
        $('.dorpdown-layer').hide();
    });
    $('.icon-close').click(function() {
        $('.top_banner').hide();
    });
    $('.my_center_box_left a').hover(function() {

    });
    $('.cate-layer-right-slide img').mouseenter(function() {

        $(this).delay('300').animate(300);
    });
    $('.cate-layer-right-slide img').mouseleave(function() {
        $(this).css('border', 'none')
    });
    $()
</script>
<script>
    $('.search').kuCity();
    $("#lun2").slide({
        autoplay: true,
        autoTime: 5000,
    });
</script>
<!--滚动展示-->
<script type="text/javascript">
    (function($) {

        $.fn.myScroll = function(options) {
            //默认配置
            var defaults = {
                speed: 50, //滚动速度,值越大速度越慢
                rowHeight: 50 //每行的高度
            };

            var opts = $.extend({}, defaults, options),
                intId = [];

            function marquee(obj, step) {

                obj.find("ul").animate({
                    marginTop: '-=1'
                }, 0, function() {
                    var s = Math.abs(parseInt($(this).css("margin-top")));
                    if(s >= step) {
                        $(this).find("li").slice(0, 1).appendTo($(this));
                        $(this).css("margin-top", 0);
                    }
                });
            }

            this.each(function(i) {
                var sh = opts["rowHeight"],
                    speed = opts["speed"],
                    _this = $(this);
                intId[i] = setInterval(function() {
                    if(_this.find("ul").height() <= _this.height()) {
                        clearInterval(intId[i]);
                    } else {
                        marquee(_this, sh);
                    }
                }, speed);

                _this.hover(function() {
                    clearInterval(intId[i]);
                }, function() {
                    intId[i] = setInterval(function() {
                        if(_this.find("ul").height() <= _this.height()) {
                            clearInterval(intId[i]);
                        } else {
                            marquee(_this, sh);
                        }
                    }, speed);
                });

            });

        }

    })(jQuery);

    $(function() {

        $("div.ranklist").myScroll({
            speed: 50,
            rowHeight: 50
        });

    });
    $(".closehd").click(function() { //右下角红包图标点击变小
        $(this).hide();
        $('.bk_foot_redbag a').animate({
            width: '80px',
            height: '100px'
        });
    });
    $('#red_bag').click(function(){
        $(this).hide();
        $('.closehd').hide();
        $('.font').show();
        $('.font').css('display','inline-block')
        $('.red_bag').animate({
            width:'400px',
            height:'300px'
        });
    });
    $('#ensure').click(function(){
        $('.red_bag').fadeOut();
    });
</script>

</html>