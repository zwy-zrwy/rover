<?php /*a:1:{s:76:"/www/wwwroot/shop.zhouweiyaocloud.xyz/application/index/view/flow/index.html";i:1585114422;}*/ ?>

<!doctype html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>购物车</title>
    <link rel="stylesheet" href="/static/css/carts.css" />
    <link rel="stylesheet" type="text/css" href="/static/css/base.css" />
    <link rel="stylesheet" type="text/css" href="/static/css/style.css" />
    <link rel="stylesheet" href="/static/css/slide.css" />
    <style>
        .dsc-search {
            float: right;
            margin: 16px 120px 0 78px;
            width: 546px;
            position: relative;
        }
    </style>
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
            <a href="#" class="cart_title">购物车</a>
        </div>
        <div class="dsc-search">
            <div class="form">
                <form class="search-form">
                    <input name="keywords" type="text" id="keyword" value="" class="search-text" style="color: rgb(153, 153, 153);">
                    <button type="submit" class="button button-icon"><i class="search_form"></i></button>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="clear"></div>
<div class="w1200_container">
    <div class="no_goods hide">
        <div class="cart_empt">
            <div class="cart_message">
                <div class="txt">购物车快饿瘪了，主人快给我挑点宝贝吧</div>
                <div class="info">
                    <a href="#" class="btn sc_redBg_btn">马上去逛逛</a>
                    <a href="javascript:void(0);" id="go_pay" class="login">去登录</a>
                </div>
            </div>
        </div>
    </div>
    <section class="cartMain">
        <div class="cartMain_hd">
            <ul class="order_lists cartTop">
                <li class="list_chk">
                    <!--所有商品全选-->
                    <input type="checkbox" id="all" class="whole_check">
                    <label for="all"></label> 全选
                </li>
                <li class="list_con">商品信息</li>
                <li class="list_info">商品参数</li>
                <li class="list_price">单价</li>
                <li class="list_amount">数量</li>
                <li class="list_sum">金额</li>
                <li class="list_op">操作</li>
            </ul>
        </div>

        <div class="cartBox">
            <div class="order_content">
                <?php if(is_array($data) || $data instanceof \think\Collection || $data instanceof \think\Paginator): $i = 0; $__LIST__ = $data;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>
                <ul class="order_lists" >
                    <li class="list_chk">
                        <input type="checkbox" id="checkbox_<?php echo htmlentities($key+1); ?>" class="son_check" onclick="sum(<?php echo htmlentities($vo['id']); ?>)">
                        <label for="checkbox_<?php echo htmlentities($key+1); ?>"></label>
                    </li>
                    <li class="list_con">
                        <div class="list_img">
                            <a href="javascript:;"><img src="<?php echo htmlentities($vo['pic']); ?>" alt=""></a>
                        </div>
                        <div class="list_text">
                            <a href="javascript:;"><?php echo htmlentities($vo['goods_name']); ?></a>
                        </div>
                    </li>
                    <li class="list_info">
                        <?php if($vo['attr_id']): ?>
                        <p>规格：<?php echo htmlentities($vo['name']); ?></p>
                        <?php else: ?>
                        <p>规格：默认</p>
                        <p>尺寸：16*16*3(cm)</p>
                        <?php endif; ?>
                    </li>
                    <li class="list_price">
                        <p class="price">￥<?php echo htmlentities($vo['goods_price']); ?></p>
                    </li>
                    <li class="list_amount">
                        <div class="amount_box" onclick="updateCart(<?php echo htmlentities($vo['id']); ?>,$(this))">
                            <a href="javascript:;" class="reduce reSty">-</a>
                            <input type="text" value="<?php echo htmlentities($vo['goods_num']); ?>" class="sum">
                            <a href="javascript:;" class="plus">+</a>
                        </div>
                    </li>
                    <li class="list_sum">
                        <p class="sum_price">￥<?php echo htmlentities($vo['sum_price']); ?></p>
                    </li>
                    <li class="list_op">
                        <p class="del">
                            <a href="javascript:;" onclick="delGood(<?php echo htmlentities($vo['id']); ?>)" class="delBtn">移除商品</a>
                        </p>
                    </li>
                </ul>
                <?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        </div>

        <!--底部-->
        <div class="bar-wrapper">
            <div class="bar-right">
                <div class="piece">已选商品<strong class="piece_num">0</strong>件</div>
                <div class="totalMoney">共计: <strong class="total_text">0.00</strong></div>
                <div class="calBtn">
                    <a href="javascript:;" onclick="checkout()">结算</a>
                </div>
            </div>
        </div>
    </section>
    <section class="model_bg"></section>
    <section class="my_model">
        <p class="title">删除宝贝<span class="closeModel">X</span></p>
        <p>您确认要删除该宝贝吗？</p>
        <div class="opBtn">
            <a href="javascript:;" class="dialog-sure">确定</a>
            <a href="javascript:;" class="dialog-close">关闭</a>
        </div>
    </section>
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
<!--<div class="bk_foot_redbag">-->
    <!--<a href="#">-->
        <!--<img src="/static/images/red_package.png" width="100%" alt="">-->
    <!--</a>-->
    <!--<span class="closehd"></span>-->
<!--</div>-->
</body>
<script type="text/javascript" src="/static/js/jquery.min.js"></script>
<script type="text/javascript" src="/static/js/kuCity.js"></script>
<script type="text/javascript" src="/static/js/carts.js"></script>
<script type="text/javascript" src="/static/js/jquery.SuperSlide.js"></script>
<script type="text/javascript" src="/static/js/slide.js"></script>
<script src="/static/layui/layui.js" ></script>
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
</script>
<script language="javascript" type="text/javascript">
    $(function() {
        //全选或全不选
        $("#all").click(function() {
            if(this.checked) {
                $("#list :checkbox").prop("checked", true);
            } else {
                $("#list :checkbox").prop("checked", false);
            }
        });
        //全选  
        $("#selectAll").click(function() {
            $("#list :checkbox,#all").prop("checked", true);
        });
        				// //全不选
        				// $("#unSelect").click(function() {
        				// 	$("#list :checkbox,#all").prop("checked", false);
        				// });
        				// //反选
        				// $("#reverse").click(function() {
        				// 	$("#list :checkbox").each(function() {
        				// 		$(this).prop("checked", !$(this).prop("checked"));
        				// 	});
        				// 	allchk();
        				// });

        //设置全选复选框
        $("#list :checkbox").click(function() {
            allchk();
        });

        //获取选中选项的值
        $("#getValue").click(function() {
            var valArr = new Array;
            $("#list :checkbox[checked]").each(function(i) {
                valArr[i] = $(this).val();
            });
            var vals = valArr.join(',');
            alert(vals);
        });
    });

    function allchk() {
        var chknum = $("#list :checkbox").size(); //选项总个数
        var chk = 0;
        $("#list :checkbox").each(function() {
            if($(this).prop("checked") == true) {
                chk++;
            }
        });
        if(chknum == chk) { //全选
            $("#all").prop("checked", true);
        } else { //不全选
            $("#all").prop("checked", false);
        }
    }
    // 数量变化
    // 		$(".amount_warp").find(".btn_add").click(function() { //点击数量增加
    // 			var index = $(this).parent().siblings(".buy_num").val();
    // 			index++;
    // 			$(this).parent().siblings(".buy_num").val(index--);
    // 		});
    // 		$(".amount_warp").find(".btn_reduce").click(function() { //点击数量减少
    // 			var index = $(this).parent().siblings(".buy_num").val();
    // 			if(index > 1) {
    // 				index--;
    // 			}
    //
    // 			$(this).parent().siblings(".buy_num").val(index++);
    // 		})
</script>
<script>
    layui.use(['layer','jquery'], function() {
        var $ = layui.jquery,
            layer = layui.layer;
    });
        function updateCart(cid,that)
    {
        var cart_id = cid;
        var cart_num = that.find('.sum').val();
        $.ajax({
            'url':'update',
            'data':{'id':cart_id,'goods_num':cart_num},
            'type':'post',
            'dataType':'json',
            success:function(res){
                if(res.code == 0){
                    that.parent().next().find('.sum_price').html('￥'+res.sum+'元');
                    layer.msg(res.msg);
                }
                else
                {
                    layer.msg(res.msg);
                }
            }
        })
    }
    function delGood(id) {
            $.ajax({
                'url':'del',
                'data':{'id':id},
                'type':'post',
                'dataType':'json',
                success:function(res){
                    layer.msg(res.msg);
                }
            })
    }
    var arr = [];
    function sum(id){
        var flag = arr.indexOf(id);
        if(flag > -1)
        {
            arr.splice(flag,1);
        }
        else
        {
            arr.push(id);
        }
    }

    function checkout()
    {
        $.ajax({
            'url':"<?php echo url('flow/check'); ?>",
            'data':{'id':arr},
            'type':'post',
            'dataType':'json',
            success:function(res)
            {
                if(res.code == 0)
                {
                    layer.msg(res.msg);
                    var address = "<?php echo url('index/flow/checkout',['id'=>'code']); ?>";
                    var url = address.replace('code',res.list);
                    location.href = url;
                }
                else
                {
                    layer.msg(res.msg);
                }
            }
        })
    }
</script>
</html>