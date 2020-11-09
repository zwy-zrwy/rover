<?php echo $this->fetch('header.htm'); ?>
<script src="static/js/laydate/laydate.js"></script>
<div class="main">
  <div class="col_side">
      <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
      <h2>轮播编辑</h2>
      <div class="tab_navs">
          <ul>
            <li><a href="index.php?act=app&op=app_ad">轮播管理</a></li>
            <li class="cur"><a href="javascript:void(0);"><?php if ($this->_var['form_act'] == 'insert'): ?>添加轮播<?php else: ?>轮播编辑<?php endif; ?></a></li>
        </ul>
      </div>
    </div>
    <div class="main_bd">
      <form action="index.php?act=app" method="post" name="theForm" enctype="multipart/form-data" onsubmit="return validate()">
        <table width="100%">
          <tr>
            <td>广告名称</td>
            <td><input type="text" name="ad_name" value="<?php echo $this->_var['ads']['ad_name']; ?>" size="35" /></td>
          </tr>
          <tr>
            <td>开始日期</td>
            <td>
              <input name="start_time" type="text" id="start_time" size="22" value='<?php echo $this->_var['ads']['start_time']; ?>' onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})" />
            </td>
          </tr>
          <tr>
            <td>结束日期</td>
            <td>
              <input name="end_time" type="text" id="end_time" size="22" value='<?php echo $this->_var['ads']['end_time']; ?>' onclick="laydate({istime: true, format: 'YYYY-MM-DD hh:mm:ss'})" />
            </td>
          </tr>
          <tbody>
          <tr>
            <td>广告链接</td>
            <td>
              <input type="text" name="ad_link" value="<?php echo $this->_var['ads']['ad_link']; ?>" size="35" />
            </td>
          </tr>
          <tr>
            <td>广告图片</td>
            <td>
              <div class="upimg bpic">
                <input name="ad_img" type="file" id="up_img" />
                <img src="<?php if ($this->_var['ads']['ad_code']): ?>../uploads/ads_img/<?php echo $this->_var['ads']['ad_code']; ?><?php else: ?>static/images/preview.png<?php endif; ?>" id="show_img"/>
                <span></span>
                <i class="fa fa-edit"></i>
              </div>
            </td>
          </tr>
          <tr>
            <td>是否开启</td>
            <td>
              <input type="radio" name="enabled" value="1" <?php if ($this->_var['ads']['enabled'] == 1): ?> checked="true" <?php endif; ?> />开启
              <input type="radio" name="enabled" value="0" <?php if ($this->_var['ads']['enabled'] == 0): ?> checked="true" <?php endif; ?> />关闭
            </td>
          </tr>
          <tr>
             <td class="label">&nbsp;</td>
             <td>
              <input type="submit" value="确定" class="btn" />
              <input type="hidden" name="op" value="<?php echo $this->_var['form_act']; ?>" />
              <input type="hidden" name="id" value="<?php echo $this->_var['ads']['ad_id']; ?>" />
            </td>
          </tr>
          </tbody>
        </table>
      </form>
    </div>
  </div>
</div>
<script src="static/js/uploadPreview.js"></script>
<script>
$(function(){
  new uploadPreview({UpBtn: "up_img", ImgShow: "show_img"});

  var delParent;
  var defaults = {
    fileType:["jpg","png","jpeg"],   // 上传文件的类型
    fileSize:1024 * 1024 * 1  // 上传文件的大小 1M
  };
  /*点击图片的文本框*/
  $(".file").change(function(){  
    var idFile = $(this).attr("id");
    var itemIndex = $(this).index();
    var file = document.getElementById(idFile);
    var uplist = $(this).parents(".uplist"); //存放图片的父亲元素
    var fileList = file.files; //获取的图片文件
    var input = $(this).parent();//文本框的父亲元素
    var imgArr = [];

    fileList = validateUp(fileList);
    for(var i = 0;i<fileList.length;i++){
      var imgUrl = window.URL.createObjectURL(fileList[i]);
        imgArr.push(imgUrl);
      var $upimg = $("<li class='upimg loading'><span class='opcity'></span>");
        uplist.prepend($upimg);
      var $close = $("<a href='javascript:;' class='up-close' title='删除'>").on("click",function(event){
        delParent = $(this).parent();
        delParent.remove(); 
      });   
      $close.appendTo($upimg);
      var $trash = $("<i class='fa fa-trash'>");
      $trash.appendTo($close);
      var $img = $("<img class='up-img up-opcity'>");
      $img.attr("src",imgArr[i]);
      $img.appendTo($upimg);
    }

    setTimeout(function(){
      $(".upimg").removeClass("loading");
      $(".up-img").removeClass("up-opcity");
    },450);
  });

  function validateUp(files){
    var arrFiles = [];//替换的文件数组
    for(var i = 0, file; file = files[i]; i++){
      //获取文件上传的后缀名
      var newStr = file.name.split("").reverse().join("");
      if(newStr.split(".")[0] != null){
          var type = newStr.split(".")[0].split("").reverse().join("");
          console.log(type+"===type===");
          if(jQuery.inArray(type, defaults.fileType) > -1){
            // 类型符合，可以上传
            if (file.size >= defaults.fileSize) {
              alert(file.size);
              alert('您这个"'+ file.name +'"文件大小过大');  
            } else {
              // 在这里需要判断当前所有文件中
              arrFiles.push(file);  
            }
          }else{
            alert('您这个"'+ file.name +'"上传类型不符合'); 
          }
        }else{
          alert('您这个"'+ file.name +'"没有类型, 无法识别');  
        }
    }
    return arrFiles;
  }

})
</script>
<?php echo $this->fetch('footer.htm'); ?>