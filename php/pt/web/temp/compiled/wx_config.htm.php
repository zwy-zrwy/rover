<script src="<?php echo $this->_var['http']; ?>res.wx.qq.com/open/js/jweixin-1.0.0.js"></script>
<script>
wx.config({
    //debug: true,
    debug: false,
    appId: '<?php echo $this->_var['appid']; ?>',
    timestamp: '<?php echo $this->_var['timestamp']; ?>',
    nonceStr: '<?php echo $this->_var['timestamp']; ?>',
    signature: '<?php echo $this->_var['signature']; ?>',
    jsApiList: ['onMenuShareAppMessage','onMenuShareTimeline']
});
wx.ready(function () {
  //分享给朋友
    wx.onMenuShareAppMessage({
      title: '<?php echo $this->_var['share']['title']; ?>',
      desc: '<?php echo $this->_var['share']['desc']; ?>',
      link: '<?php echo $this->_var['share']['link']; ?>',
      imgUrl: '<?php echo $this->_var['share']['imgUrl']; ?>',
      success: function() {
        layer.open({content: "分享成功",skin: 'msg',time: 3});
        statis(1,1);
      },
      cancel: function() {
        layer.open({content: "取消分享",skin: 'msg',time: 3});
        statis(1,2);
      }
    });
    //朋友圈
    wx.onMenuShareTimeline({
      title: '<?php echo $this->_var['share']['title']; ?>',
      desc: '<?php echo $this->_var['share']['desc']; ?>',
      link: '<?php echo $this->_var['share']['link']; ?>',
      imgUrl: '<?php echo $this->_var['share']['imgUrl']; ?>',
      success: function() {
        layer.open({content: "分享成功",skin: 'msg',time: 3});
        statis(2,1);
      },
      cancel: function() {
        layer.open({content: "取消分享",skin: 'msg',time: 3});
        statis(2,2);
      }
    });
});
wx.error(function (res) {
  //layer.open({content:res.errMsg,skin: 'msg',time: 2});
});
function statis(share_type,share_statu){
    var link = window.location.href;
    $.ajax({
        type:"post",//请求类型
        url: 'index.php?c=index&a=share',
        data: {link,share_type,share_statu},
        dataType:"json",//服务器返回结果类型(可有可无)
        error:function(){//错误处理函数(可有可无)
            //alert("ajax出错啦");
        },
        success:function(data){
        }
    });
}
</script>