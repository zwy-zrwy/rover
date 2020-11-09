<?php echo $this->fetch('header.htm'); ?>
<script type="text/javascript" src="static/js/customMenu.js"></script>
<div class="main">
  <div class="col_side">
    <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
      <h2>自定义菜单</h2>
    </div>
    <div class="main_bd">


      <form name="theForm" class="wxmenuForm" method="post" action="index.php?act=wechat&op=addmenu&id=<?php echo $this->_var['menu']['id']; ?>" enctype="multipart/form-data">
        <table class="table caidan_tab">
          <thead class="biaotou">
            <tr>
              <th>显示顺序</th>
              <th>菜单名称</th>
              <th>触发动作</th>
              <th>响应动作</th>
              <th>启用</th>
              <th>操作</th>
            </tr>
          </thead>
          <?php $_from = $this->_var['pmenu']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('k', 'item');if (count($_from)):
    foreach ($_from AS $this->_var['k'] => $this->_var['item']):
?>
          <tbody id="tab<?php echo $this->_var['k']; ?>">
            <tr >
              <td><input type="text" name="wei_stort[]" value="<?php echo $this->_var['item']['sort_order']; ?>" size="2"></td>
              <td>
                <input type="hidden" name="menu_list[]" value="<?php echo $this->_var['item']['id']; ?>">
                  主：<input type="text" name="wei_name[]" value="<?php echo $this->_var['item']['name']; ?>" size="6" datatype="*">   
                <input value="+" class="btn" title="添加子菜单" id="add<?php echo $this->_var['k']; ?>" type="button">
              </td>
              <td>
                <select onchange="getValue<?php echo $this->_var['k']; ?>()" id="zhu<?php echo $this->_var['k']; ?>" name="wei_type[]">
                  <option value="0" >请选择</option>
                  <option value="1" <?php if ($this->_var['item']['type'] == 1): ?>selected<?php endif; ?>>图文消息</option>
                  <option value="2" <?php if ($this->_var['item']['type'] == 2): ?>selected<?php endif; ?>>跳转链接</option>
                </select>
              </td>
              <td>
                <input id="zhu1_<?php echo $this->_var['k']; ?>" name="wei_value[]" value="<?php echo $this->_var['item']['value']; ?>" type="text" placeholder="请输入文本消息" size="40">
              </td>
              <td><input type="checkbox" name="wei_enabled[]" value="<?php echo $this->_var['k']; ?>" <?php if ($this->_var['item']['enabled'] == 1): ?> checked <?php endif; ?>></td>
              <td><!--<a onclick='attr_del1()'>删除</a>--></td>
            </tr>
            <?php $_from = $this->_var['item']['pids']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('key', 'its');if (count($_from)):
    foreach ($_from AS $this->_var['key'] => $this->_var['its']):
?>
            <tr id='z1_zi1' class='zi1'>
                <td>
                  <input type='text' name='wei_<?php echo $this->_var['k']; ?>_stort[]' value="<?php echo $this->_var['its']['sort_order']; ?>" size="2">
                </td>
                <td>
                  <input type='hidden' name='menu_<?php echo $this->_var['k']; ?>_list[]' value="<?php echo $this->_var['its']['id']; ?>">
                ——子：<input type='text' name='wei_<?php echo $this->_var['k']; ?>_name[]' value="<?php echo $this->_var['its']['name']; ?>" size="6" datatype="*">
                </td>
                <td>
                  <select name='wei_<?php echo $this->_var['k']; ?>_type[]'>
                    <option value='0' selected='selected'>请选择</option>
                    <option value='1' <?php if ($this->_var['its']['type'] == 1): ?>selected<?php endif; ?>>图文消息</option>
                    <option value='2' <?php if ($this->_var['its']['type'] == 2): ?>selected<?php endif; ?>>跳转链接</option>
                  </select>
                </td>
                <td>
                  <input name='wei_<?php echo $this->_var['k']; ?>_value[]' value="<?php echo $this->_var['its']['value']; ?>" placeholder='请输入信息' type='text' size="40">
                </td>
                <td>
                  <input type='checkbox' name="wei_<?php echo $this->_var['k']; ?>_enabled[]" value="<?php echo $this->_var['key']; ?>"  <?php if ($this->_var['its']['enabled'] == 1): ?> checked <?php endif; ?>></td>
                <td><a  onclick='attr_del(<?php echo $this->_var['k']; ?>)'>删除</a></td>
            </tr>
            <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
          </tbody>
          <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </table>
        <div class="pagination">
          <div class="handler">
            <input class="btn" name="sub" value="保存" type="submit">
            <input class="btn" name="sub" value="生成自定义菜单" type="submit">
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
$(function(){
  $(".wxmenuForm").Validform({tiptype:1});
  $('#add1').click(function(e){
    var a = $("#tab1 tr").length;
    var stort=a+10;
    var a1 = a-1;
    var $html=$("<tr id='z1_zi1' class='zi1'><td><input value="+stort+" type='text' name='wei_1_stort[]' size=2></td><td><input type='hidden' name='menu_1_list[]' value=''>——子：<input type='text' name='wei_1_name[]' size = 6></td><td><select  name='wei_1_type[]'><option value='0' selected='selected'>请选择</option><option value='1'>图文消息</option><option value='2'>跳转链接</option><option value='3'>文本消息</option><option value='4'>图片消息</option></select></td><td><input id='zhu1_zi1_3' name='wei_1_value[]' placeholder='请输入信息' type='text' size = 40></td><td><input type='checkbox'  name='wei_1_enabled[]' value="+a1+"></td><td><a  onclick='attr_del(1)'>删除</a></td></tr>");
    
    if(a < 6){
      $('#tab1').append($html);
    }
  })
     
  $('#add2').click(function(e){
    var b = $("#tab2 tr").length;
    var stort=b+20;
    var b1 = b-1;
    var $html=$("<tr id='z1_zi1' class='zi1'><td><input value="+stort+" type='text' name='wei_2_stort[]' size = 2></td><td><input type='hidden' name='menu_2_list[]' value=''>——子：<input type='text' name='wei_2_name[]' size = 6></td><td><select name='wei_2_type[]'><option value='0' selected='selected'>请选择</option><option value='1'>图文消息</option><option value='2'>跳转链接</option><option value='3'>文本消息</option><option value='4'>图片消息</option></select></td><td><input id='zhu1_zi1_3' name='wei_2_value[]' placeholder='请输入信息' type='text' size = 40></td><td><input type='checkbox'   name='wei_2_enabled[]' value="+b1+"></td><td><a href='javascript:;' onclick='attr_del(2)'>删除</a></td></tr>");

    if(b < 6){
      $('#tab2').append($html);
    }
  })
  var c = 0;
  $('#add3').click(function(e){
    var c = $("#tab3 tr").length;
    var stort=c+30;
    var c1 = c-1;
    var $html=$("<tr id='z1_zi1' class='zi1'><td><input value="+stort+" type='text' name='wei_3_stort[]' size = 2></td><td><input type='hidden' name='menu_3_list[]' value=''>——子：<input type='text' name='wei_3_name[]' size = 6></td><td><select name='wei_3_type[]'><option value='0' selected='selected'>请选择</option><option value='1'>图文消息</option><option value='2'>跳转链接</option><option value='3'>文本消息</option><option value='4'>图片消息</option></select></td><td><input id='zhu1_zi1_3' name='wei_3_value[]' placeholder='请输入信息' type='text' size = 40></td><td><input type='checkbox'   name='wei_3_enabled[]' value="+c1+"></td><td><a  onclick='attr_del(3)'>删除</a></td></tr>");
    
    if(c < 6){
      $('#tab3').append($html);
    }
  })
})
function attr_del(d){
  $("#tab"+d+" tr").eq($(this).index()).remove();
}
</script>
<?php echo $this->fetch('footer.htm'); ?>