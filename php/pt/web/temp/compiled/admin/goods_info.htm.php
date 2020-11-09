<?php echo $this->fetch('header.htm'); ?>
<div class="main">
  <div class="col_side">
    <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
      <h2>商品管理</h2>
    <div class="tab_navs">
      <ul>
        <li><a href="index.php?act=goods&amp;op=goods_list">商品管理</a></li>
        <li class="cur"><a href="javascript:void(0);"><?php if ($this->_var['is_add']): ?>添加商品<?php else: ?>修改商品<?php endif; ?></a></li>
        <li><a href="index.php?act=goods&amp;op=goods_trash">商品回收站</a></li>
      </ul>
    </div>
  </div>
  <div class="main_bd">



  <form class="goodsForm" enctype="multipart/form-data" action="" method="post" name="theForm" >
    <input type="hidden" name="MAX_FILE_SIZE" value="2097152" />
    <table class="table tb-type2">
		<tbody id="general-table">

          <tr>
            <td width="80">商品名称</td>
            <td><input type="text" size="100" name="goods_name" value="<?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?>" datatype="*1-80" ajaxurl="index.php?act=goods&op=check_goods_name&goods_id=<?php echo $this->_var['goods']['goods_id']; ?>"/></td>
          </tr>
          <tr>
            <td>商品编号</td>
            <td><input type="text" name="goods_sn" value="<?php echo htmlspecialchars($this->_var['goods']['goods_sn']); ?>" class="txt" datatype="*" ajaxurl="index.php?act=goods&op=check_goods_sn&goods_id=<?php echo $this->_var['goods']['goods_id']; ?>"/></td>
          </tr>

          <tr >
        <td>商品分类</td>
            <td><select name="cat_id"><option value="0">请选择...</option><?php echo $this->_var['cat_list']; ?></select>
            </td>
          </tr>
          <tr>
            <td>关键字</td>
            <td><input type="text" name="keywords" value="<?php echo htmlspecialchars($this->_var['goods']['keywords']); ?>" class="txt" size="60" /> 用空格分隔</td>
          </tr>

          <tr>
            <td>商品简介</td>
            <td><textarea name="goods_brief" cols="80" rows="3"><?php echo htmlspecialchars($this->_var['goods']['goods_brief']); ?></textarea></td>
          </tr>

          <tr>
            <td>产品标签</td>
            <td>

            <?php $_from = $this->_var['label_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'label');if (count($_from)):
    foreach ($_from AS $this->_var['label']):
?>
            <input id="label_<?php echo $this->_var['label']['label_id']; ?>" type="checkbox" name="goods_label[]" value="<?php echo $this->_var['label']['label_id']; ?>" <?php if (in_array ( $this->_var['label']['label_id'] , $this->_var['goods_label'] )): ?>checked="checked"<?php endif; ?> /><label for="label_<?php echo $this->_var['label']['label_id']; ?>"><?php echo $this->_var['label']['label_name']; ?>&nbsp;&nbsp;&nbsp;</label>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </td>
        </tr>
        <tr>
          <td>商品视频</td>
            <td><input type="text" name="goods_video" value="<?php echo $this->_var['goods']['goods_video']; ?>" size="100" /></td>
          </tr>
        <tr>
          <td>团长佣金</td>
            <td><input type="text" name="commission" value="<?php echo $this->_var['goods']['commission']; ?>" class="txt"/>
            </td>
          </tr>
          <tr>
          <td>限购数量</td>
            <td><input type="text" name="restrictions" value="<?php echo $this->_var['goods']['restrictions']; ?>" class="txt"/> 0为不限购
            </td>
          </tr>
          <tr>
          <td>商品价格</td>
            <td><input type="text" name="shop_price" value="<?php echo $this->_var['goods']['shop_price']; ?>" class="txt"/>
            </td>
          </tr>

          <tr>
          <td>拼团价格</td>
            <td>

              <div id="J_tuan_price" class="price_list">
                <ul>
                  <li>数量</li>
                  <li>价格</li>
                  <li>删除</li>
                </ul>
                <?php $_from = $this->_var['tuan_price_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'tuan_price');if (count($_from)):
    foreach ($_from AS $this->_var['tuan_price']):
?>
                <ul>
                  <li>
                     <input type="text" name="tuan_number[]" value="<?php echo $this->_var['tuan_price']['number']; ?>"/>
                  </li>
                  <li>
                    
                     <input type="text" name="tuan_price[]" value="<?php echo $this->_var['tuan_price']['price']; ?>"/>
                  </li>
                  <li>
                     <a onclick="price_del()"">删除</a>
                  </li>
                </ul>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              </div>
              <div class="J_price_add btn">添加</div>
            </td>
          </tr>

          <tr>
          <td>市场价格</td>
            <td><input type="text" name="market_price" value="<?php echo $this->_var['goods']['market_price']; ?>" class="txt" />
            </td>
          </tr>





                    <tr>
          <td>商品规格</td>
            <td>
              <div id="J_attr" class="attr_list">
                <ul>
                  <li>名称</li>
                  <li>货号</li>
                  <li>价格</li>
                  <li>库存</li>
                  <li>图片</li>
                  <li>删除</li>
                </ul>
                <?php $_from = $this->_var['goods_attr']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'attr');$this->_foreach['sku'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['sku']['total'] > 0):
    foreach ($_from AS $this->_var['attr']):
        $this->_foreach['sku']['iteration']++;
?>
                <ul>
                  <li>
                     <input type="text" name="attr_value_list[]" value="<?php echo $this->_var['attr']['attr_value']; ?>" />
                     <input type="hidden" name="attr_id_list[]" value="<?php echo $this->_var['attr']['attr_id']; ?>"/>
                  </li>
                  <li>
                     <input type="text" name="attr_sn_list[]" value="<?php echo $this->_var['attr']['product_sn']; ?>" />
                  </li>
                  <li>
                    
                     <input type="text" name="attr_price_list[]" value="<?php echo $this->_var['attr']['attr_price']; ?>" />
                  </li>
                  <li>
                     <input type="text" name="attr_num_list[]" value="<?php echo $this->_var['attr']['product_number']; ?>" />
                  </li>
                  <li>
                    <div class="upimg skuimg">
                      <input name="attr_img_list[]" type="file" id="up_skuimg_<?php echo $this->_foreach['sku']['iteration']; ?>" />
                      
                      <img src="<?php if ($this->_var['attr']['attr_img']): ?>../<?php echo $this->_var['attr']['attr_img']; ?><?php else: ?>static/images/preview.png<?php endif; ?>" id="show_skuimg_<?php echo $this->_foreach['sku']['iteration']; ?>"/>
                      <span></span>
                      <i class="fa fa-edit"></i>
                    </div>
                  </li>
                  <li>
                     <a onclick="attr_del()"">删除</a>
                  </li>
                </ul>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
              </div>
              <div class="J_attr_add btn">添加</div>
            </td>
          </tr>



          <tr>
          <td>商品图片</td>
		  
		  <td>


      <div class="imgbox goodimg">
      <input name="goods_img" type="file" id="up_goodimg" />
      <?php if ($this->_var['goods']['goods_img']): ?>
      <img src="../<?php echo $this->_var['goods']['goods_img']; ?>" id="show_goodimg"/>
      <span></span>
      <font>重新上传</font>
      <?php else: ?>
      <img src="static/images/upload_bg.png" id="show_goodimg"/>
      <?php endif; ?>
      </div>

      图片尺寸：200X200像素

        </td>
          </tr>

          <tr>
          <td>拼团图片</td>
            <td>


            <div class="imgbox tuanimg">
      <input name="tuan_img" type="file" id="up_tuanimg" />
      <?php if ($this->_var['goods']['tuan_img']): ?>
      <img src="../<?php echo $this->_var['goods']['tuan_img']; ?>" id="show_tuanimg" />
      <span></span>
      <font>重新上传</font>
      <?php else: ?>
      <img src="static/images/upload_bg.png" id="show_tuanimg"/>
      <?php endif; ?>
      </div>

      图片尺寸：640X400像素
            </td>
			<td></td>
          </tr>
		  </tbody>



          <tbody id="album-table">
          <tr>
            <td>商品相册</td>
            <td>
            <div id="J_sort" class="upbox">
              <ul>
                <?php if ($this->_var['album_list']): ?>
                <?php $_from = $this->_var['album_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('i', 'album');if (count($_from)):
    foreach ($_from AS $this->_var['i'] => $this->_var['album']):
?>
                <li class="J_album_<?php echo $this->_var['album']['album_id']; ?>">
                  <input type="hidden" name="sort_order[<?php echo $this->_var['album']['album_id']; ?>]" size="3" />
                  <span class="opcity"></span>
                  <span class="toolbar">
                      <a href="javascript:;" class="J_leftMove" title="左移"><i class="fa fa-arrow-left"></i></a>
                      <a href="javascript:;" class="J_rightMove" title="右移"><i class="fa fa-arrow-right"></i></a>
                      <a href="javascript:;" onclick="imgRemove('album','<?php echo $this->_var['album']['album_id']; ?>')" title="删除"><i class="fa fa-trash"></i></a>
                  </span>
                  <img src="../<?php echo $this->_var['album']['album_img']; ?>"/>
                </li>
                <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
                <?php endif; ?>
              </ul>
              <ul class="uplist">
                <li class="upload"><input type="file" name="album_img[]" id="album_file" class="file" value="" multiple /></li>
              </ul>
            </div>

            图片尺寸：640X400像素

            </td>
          </tr>

          

          <tr>
          <td>商品重量</td>
            <td><input type="text" name="goods_weight" value="<?php echo $this->_var['goods']['goods_weight_by_unit']; ?>" size="20" /> <select name="weight_unit"><?php echo $this->html_options(array('options'=>$this->_var['unit_list'],'selected'=>$this->_var['weight_unit'])); ?></select></td>
          </tr>
          <?php if ($this->_var['cfg']['use_storage']): ?>

          <tr>
          <td>商品库存</td>
            <td><input type="text" name="goods_number" value="<?php echo $this->_var['goods']['goods_number']; ?>" <?php if ($this->_var['goods']['is_goods_attr']): ?>readonly="readonly"<?php endif; ?> class="txt" />
            库存在商品存在货品时为不可编辑状态，库存数值取决于其货品数量</td>
          </tr>

          <tr>
          <td>库存警告</td>
            <td><input type="text" name="warn_number" value="<?php echo $this->_var['goods']['warn_number']; ?>" class="txt" /></td>
          </tr>
          <?php endif; ?>

          <tr>
          <td>加入推荐</td>
            <td><input type="checkbox" name="is_best" value="1" <?php if ($this->_var['goods']['is_best']): ?>checked="checked"<?php endif; ?> />精品 <input type="checkbox" name="is_new" value="1" <?php if ($this->_var['goods']['is_new']): ?>checked="checked"<?php endif; ?> />新品 <input type="checkbox" name="is_hot" value="1" <?php if ($this->_var['goods']['is_hot']): ?>checked="checked"<?php endif; ?> />热销</td>
          </tr>

          <tr>
          <td>免运费</td>
            <td>
              <input type="checkbox" name="is_shipping" value="1" <?php if ($this->_var['goods']['is_shipping']): ?>checked="checked"<?php endif; ?> /> 是
              是否为免运费商品
            </td>
          </tr>
          <tr>
          <td>开启推广</td>
            <td>
              <input type="radio" name="is_dist" value="1" <?php if ($this->_var['goods']['is_dist'] == 1): ?>checked="true"<?php endif; ?> onclick="showunit(1)"/> 是
              <input type="radio" name="is_dist" value="0" <?php if ($this->_var['goods']['is_dist'] == 0): ?>checked="true"<?php endif; ?> onclick="showunit(0)"/> 否
            </td>

          </tr>
          <tr id="dist_off" <?php if ($this->_var['goods']['is_dist'] != 1): ?> style="display:none;"<?php endif; ?>>
          <td>推广佣金</td>
            <td>
              单买 <input type="text" name="comm_shop_price" value="<?php echo $this->_var['goods']['comm_shop_price']; ?>" class="txt" />
              拼团 <input type="text" name="comm_tuan_price" value="<?php echo $this->_var['goods']['comm_tuan_price']; ?>" class="txt" />
            </td>
          </tr>

          <tr>
          <td>商家备注</td>
            <td>
            <textarea name="seller_note" cols="60" rows="3"><?php echo $this->_var['goods']['seller_note']; ?></textarea>仅供商家自己看的信息
            </td>
          </tr>
		  </tbody>

    
	
	
	<tbody id="desc-table">
          <tr>
            <td>商品详情</td>
            <td colspan="2">
    <script src="editor/kindeditor-min.js"></script>
    <script src="editor/lang/zh_CN.js"></script>
    <script>
      var editor;
      KindEditor.ready(function(K) {
        editor = K.create('textarea[name="goods_desc"]', {
          urlType : 'domain',
          resizeType : 1,
          allowPreviewEmoticons : false,
          allowImageUpload : false,
          items : [
            'source', '|', 'forecolor', 'hilitecolor', 'bold',
            'removeformat', '|', 'justifyleft', 'justifycenter', 'justifyright', '|', 'image', 'multiimage', 'link']
        });
      });
    </script>
    <textarea id="goods_desc" name="goods_desc" style='width:680px;height:600px;'><?php echo $this->_var['goods']['goods_desc']; ?></textarea>


            </td>
          </tr>

		  </tbody>

    </table>
          

          <input type="hidden" name="goods_id" value="<?php echo $this->_var['goods']['goods_id']; ?>" />
		  <?php if (! $this->_var['is_add']): ?>
		  <input type="hidden" name="is_on_sale" value="1" />
		  <?php endif; ?>
          <input type="submit" value="确定" class="btn" />

        <input type="hidden" name="op" value="<?php echo $this->_var['form_act']; ?>" />




      </form>

  </div>
</div>
<script src="static/js/move.js"></script>
<script src="static/js/uploadPreview.js"></script>
<script type="text/javascript">
function showunit(eve){
  if(eve == 1){
    $('#dist_off').show();
  }else{
    $('#dist_off').hide();
  }

}
  $(function(){
    $(".goodsForm").Validform();
    

    var sku_num = $("#J_attr ul").length;
    $('.J_price_add').click(function(e) {
      var $html=$("<ul><li><input type='text' name='tuan_number[]' size='15' value=''/></li><li><input type='text' name='tuan_price[]' size='15' value=''/></li><li><a onclick=\'price_del()\'>删除</a></li></ul>");
      $('#J_tuan_price').append($html);
    });

    $('.J_attr_add').click(function(e) {
      var num=$("#J_attr ul").length;
      var $html=$("<ul><li><input type='text' name='attr_value_list[]' value=''/><input type='hidden' name='attr_id_list[]' value=''/></li><li><input type='text' name='attr_sn_list[]' value=''/></li><li><input type='text' name='attr_price_list[]' value=''/><li><input type='text' name='attr_num_list[]' value=''/></li></li><li><div class='skuimg'><input name='attr_img_list[]' type='file' id='up_skuimg_"+num+"' /><img src='static/images/preview.png' id='show_skuimg_"+num+"'/><span></span><i class='fa fa-edit'></i></div></li><li><a onclick='attr_del()'>删除</a></li></ul>");
      $('#J_attr').append($html);
      attr_img();
    });
    attr_img();
    new uploadPreview({UpBtn:"up_goodimg", ImgShow:"show_goodimg"});
    new uploadPreview({UpBtn:"up_tuanimg", ImgShow:"show_tuanimg"});
  })

  function attr_img(){
    var sku_num = $("#J_attr ul").length;
    for(var i=1; i<sku_num;i++){
      new uploadPreview({UpBtn: "up_skuimg_"+i, ImgShow: "show_skuimg_"+i}); 
    }
  }
  function price_del(){
    $("#J_tuan_price ul").eq($(this).index()).remove();
  }
  function attr_del(){
    $("#J_attr ul").eq($(this).index()).remove();
  }
</script>
<script language="JavaScript">
$(function(){
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
              layer.msg(file.size);
              layer.msg('您这个"'+ file.name +'"文件大小过大');  
            } else {
              // 在这里需要判断当前所有文件中
              arrFiles.push(file);  
            }
          }else{
            layer.msg('您这个"'+ file.name +'"上传类型不符合'); 
          }
        }else{
          layer.msg('您这个"'+ file.name +'"没有类型, 无法识别');  
        }
    }
    return arrFiles;
  }

})
</script>
<?php echo $this->fetch('footer.htm'); ?>
