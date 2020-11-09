<!DOCTYPE html>
<html id="aos">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="Cache-Control" content="no-cache,no-store,must-revalidate"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="0"/>
<title>我的订单</title>
<link rel="shortcut icon" href="favicon.ico">
<link href="<?php echo $this->_var['template_path']; ?>css/common.css?v=<?php echo $this->_var['aos_version']; ?>" rel="stylesheet" />
<link href="<?php echo $this->_var['template_path']; ?>css/user.css?v=<?php echo $this->_var['aos_version']; ?>" rel="stylesheet" />
<script src="<?php echo $this->_var['template_path']; ?>js/jquery.min.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<script src="<?php echo $this->_var['template_path']; ?>js/common.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<script src="<?php echo $this->_var['template_path']; ?>js/user.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<script src="<?php echo $this->_var['template_path']; ?>js/dropload.min.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
</head>
<body>
<div class="box-hide"></div>
<section class="container pdt pdb">
    <nav class="top-nav">
        <ul id="top-nav">
            <li<?php if ($this->_var['default'] == 0): ?> class="cur"<?php endif; ?>><a href="javascript:;"><span>全部</span></a></li>
            <li<?php if ($this->_var['default'] == 1): ?> class="cur"<?php endif; ?>><a href="javascript:;"><span>待付款</span></a></li>
            <li<?php if ($this->_var['default'] == 2): ?> class="cur"<?php endif; ?>><a href="javascript:;"><span>待成团</span></a></li>
            <li<?php if ($this->_var['default'] == 3): ?> class="cur"<?php endif; ?>><a href="javascript:;"><span>待核销</span></a></li>
            <li<?php if ($this->_var['default'] == 4): ?> class="cur"<?php endif; ?>><a href="javascript:;"><span>待发货</span></a></li>
            <li<?php if ($this->_var['default'] == 5): ?> class="cur"<?php endif; ?>><a href="javascript:;"><span>待收货</span></a></li>
            <li<?php if ($this->_var['default'] == 6): ?> class="cur"<?php endif; ?>><a href="javascript:;"><span>待评价</span></a></li>
        </ul>
    </nav>
  <div class="order_list"></div>
  <div class="order_list"></div>
  <div class="order_list"></div>
  <div class="order_list"></div>
  <div class="order_list"></div>
  <div class="order_list"></div>
  <div class="order_list"></div>
</section>



    
    <div class="box-veri">
        <a class="box-close" href="javascript:void(0)" onClick="verification()" title="关闭"></a>
        <h3>请出示二维码给核销员</h3>
        <p class="veri-code"></p>
    </div>
    <?php echo $this->fetch('inc/add_comments.htm'); ?>




<?php echo $this->fetch('inc/footer.htm'); ?>

<script>
window.onload = function() { 
    slide();
};

</script>


<script>

$(function(){
    
    var itemIndex = <?php echo $this->_var['default']; ?>;
    var tab1LoadEnd = false;
    var tab2LoadEnd = false;
    var tab3LoadEnd = false;
    var tab4LoadEnd = false;
    var tab5LoadEnd = false;
    var tab6LoadEnd = false;
    var tab7LoadEnd = false;

    

    // tab
    $('.top-nav li').on('click',function(){
        var $this = $(this);
        itemIndex = $this.index();
        $this.addClass('cur').siblings('li').removeClass('cur');
        $('.order_list').eq(itemIndex).show().siblings('.order_list').hide();
        
        // 如果选中菜单一
        if(itemIndex == '0'){

            // 如果数据没有加载完
            if(!tab1LoadEnd){
                // 解锁
                dropload.unlock();
                dropload.noData(false);
            }else{
                // 锁定
                dropload.lock('down');
                dropload.noData();
            }
        // 如果选中菜单二
        }else if(itemIndex == '1'){
            if(!tab2LoadEnd){
                // 解锁
                dropload.unlock();
                dropload.noData(false);
            }else{
                // 锁定
                dropload.lock('down');
                dropload.noData();
            }
        // 如果选中菜单三
        }else if(itemIndex == '2'){
            if(!tab3LoadEnd){
                // 解锁
                dropload.unlock();
                dropload.noData(false);
            }else{
                // 锁定
                dropload.lock('down');
                dropload.noData();
            }
        // 如果选中菜单四
        }else if(itemIndex == '3'){
            if(!tab4LoadEnd){
                // 解锁
                dropload.unlock();
                dropload.noData(false);
            }else{
                // 锁定
                dropload.lock('down');
                dropload.noData();
            }
        }else if(itemIndex == '4'){
            if(!tab5LoadEnd){
                // 解锁
                dropload.unlock();
                dropload.noData(false);
            }else{
                // 锁定
                dropload.lock('down');
                dropload.noData();
            }
        }else if(itemIndex == '5'){
            if(!tab6LoadEnd){
                // 解锁
                dropload.unlock();
                dropload.noData(false);
            }else{
                // 锁定
                dropload.lock('down');
                dropload.noData();
            }
        }else if(itemIndex == '6'){
            if(!tab7LoadEnd){
                // 解锁
                dropload.unlock();
                dropload.noData(false);
            }else{
                // 锁定
                dropload.lock('down');
                dropload.noData();
            }
        }

        slide();
        // 重置
        dropload.resetload();
    });

    var num = 0;
    var page= 0;
    var page1= 0;
    var num1 = 0;
    var page2= 0;
    var num2 = 0;
    var page3= 0;
    var num3 = 0;
    var page4= 0;
    var num4 = 0;
    var page5= 0;
    var num5 = 0;
    var page6= 0;
    var num6 = 0;
    var dropload = $('.container').dropload({
        scrollArea : window,
        loadDownFn : function(me){
            // 加载菜单一的数据
            if(itemIndex == '0'){
                page++;
                var counter = page,last = num,amount = 6;
                $.ajax({
                    type: 'POST',
                    data: {last,amount},
                    url: 'index.php?c=user&a=order_list_ajax&status=all',
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
                            $('.order_list').eq(0).append(result);
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
                        // 即使加载出错，也得重置
                        me.resetload();
                    }
                });
            // 加载菜单二的数据
            }else if(itemIndex == '1'){
                page1++;
                var counter = page1,last = num1,amount = 10;
                $.ajax({
                    type: 'POST',
                    data: {last,amount},
                    url: 'index.php?c=user&a=order_list_ajax&status=await_pay',
                    dataType: 'json',
                    success: function(data){
                        var result = '';
                        
                        num1 = amount * counter;
                        last = num1;
                        if(typeof(data.info)!="undefined"){
                          for(i = 0; i < data.info.length; i++){
                              result +=data.info[i];
                                  
                          }
                        }
                        // 为了测试，延迟1秒加载
                        setTimeout(function(){
                            $('.order_list').eq(1).append(result);
                            if(last >= data.count){
                                // 数据加载完
                                tab2LoadEnd = true;
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
                        // 即使加载出错，也得重置
                        me.resetload();
                    }
                });
            // 加载菜单三的数据
            }else if(itemIndex == '2'){
                page2++;
                var counter = page2,last = num2,amount = 6;
                $.ajax({
                    type: 'POST',
                    data: {last,amount},
                    url: 'index.php?c=user&a=order_list_ajax&status=await_tuan',
                    dataType: 'json',
                    success: function(data){
                        var result = '';
                        
                        num2 = amount * counter;
                        last = num2;
                        if(typeof(data.info)!="undefined"){
                          for(i = 0; i < data.info.length; i++){
                              result +=data.info[i];
                                  
                          }
                        }
                        // 为了测试，延迟1秒加载
                        setTimeout(function(){
                            $('.order_list').eq(2).append(result);
                            if(last >= data.count){
                                // 数据加载完
                                tab3LoadEnd = true;
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
                        // 即使加载出错，也得重置
                        me.resetload();
                    }
                });
            // 加载菜单四的数据
            }else if(itemIndex == '3'){
                page3++;
                var counter = page3,last = num3,amount = 6;
                $.ajax({
                    type: 'POST',
                    data: {last,amount},
                    url: 'index.php?c=user&a=order_list_ajax&status=await_veri',
                    dataType: 'json',
                    success: function(data){
                        var result = '';
                        
                        num3 = amount * counter;
                        last = num3;
                        if(typeof(data.info)!="undefined"){
                          for(i = 0; i < data.info.length; i++){
                              result +=data.info[i];
                                  
                          }
                        }
                        // 为了测试，延迟1秒加载
                        setTimeout(function(){
                            $('.order_list').eq(3).append(result);
                            if(last >= data.count){
                                // 数据加载完
                                tab4LoadEnd = true;
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
                        // 即使加载出错，也得重置
                        me.resetload();
                    }
                });
            // 加载菜单五的数据
            }else if(itemIndex == '4'){
                page4++;
                var counter = page4,last = num4,amount = 6;
                $.ajax({
                    type: 'POST',
                    data: {last,amount},
                    url: 'index.php?c=user&a=order_list_ajax&status=await_ship',
                    dataType: 'json',
                    success: function(data){
                        var result = '';
                        
                        num4 = amount * counter;
                        last = num4;
                        if(typeof(data.info)!="undefined"){
                          for(i = 0; i < data.info.length; i++){
                              result +=data.info[i];
                                  
                          }
                        }
                        // 为了测试，延迟1秒加载
                        setTimeout(function(){
                            $('.order_list').eq(4).append(result);
                            if(last >= data.count){
                                // 数据加载完
                                tab5LoadEnd = true;
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
                        // 即使加载出错，也得重置
                        me.resetload();
                    }
                });
            }else if(itemIndex == '5'){
                page5++;
                var counter = page5,last = num5,amount = 6;
                $.ajax({
                    type: 'POST',
                    data: {last,amount},
                    url: 'index.php?c=user&a=order_list_ajax&status=await_receipt',
                    dataType: 'json',
                    success: function(data){
                        var result = '';
                        
                        num5 = amount * counter;
                        last = num5;
                        if(typeof(data.info)!="undefined"){
                          for(i = 0; i < data.info.length; i++){
                              result +=data.info[i];
                                  
                          }
                        }
                        // 为了测试，延迟1秒加载
                        setTimeout(function(){
                            $('.order_list').eq(5).append(result);
                            if(last >= data.count){
                                // 数据加载完
                                tab6LoadEnd = true;
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
                        // 即使加载出错，也得重置
                        me.resetload();
                    }
                });
            }else if(itemIndex == '6'){
                page6++;
                var counter = page6,last = num6,amount = 6;
                $.ajax({
                    type: 'POST',
                    data: {last,amount},
                    url: 'index.php?c=user&a=order_list_ajax&status=await_comment',
                    dataType: 'json',
                    success: function(data){
                        var result = '';
                        
                        num6 = amount * counter;
                        last = num6;
                        if(typeof(data.info)!="undefined"){
                          for(i = 0; i < data.info.length; i++){
                              result +=data.info[i];
                                  
                          }
                        }
                        // 为了测试，延迟1秒加载
                        setTimeout(function(){
                            $('.order_list').eq(6).append(result);
                            if(last >= data.count){
                                // 数据加载完
                                tab7LoadEnd = true;
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
                        // 即使加载出错，也得重置
                        me.resetload();
                    }
                });
            }
        }
    });

});
</script>   
</body>

</html>
