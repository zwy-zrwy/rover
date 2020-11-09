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
        <li><a href="index.php?act=goods&amp;op=goods_add">添加商品</a></li>
        <li class="cur"><a href="index.php?act=goods&amp;op=goods_trash">商品回收站</a></li>
      </ul>
    </div>
  </div>
  <div class="main_bd">


<!-- 商品列表 -->
<form method="post" action="" name="listForm" onsubmit="return confirmSubmit(this)">
  <!-- start goods list -->
  <div class="list-div" id="listDiv">

<table class="table tb-type2">
  <thead>
  <tr class="thead">
    <th>
      <input onclick='checkAll(this, "checkboxes")' type="checkbox" />
      <a href="javascript:listTable.sort('goods_id'); ">ID</a>
    </th>
    <th><a href="javascript:listTable.sort('goods_name'); ">商品名称</a></th>
    <th><a href="javascript:listTable.sort('goods_sn'); ">货号</a></th>
    <th><a href="javascript:listTable.sort('shop_price'); ">价格</a></th>
    <th>操作</th>
  <tr>
  </thead>
  <tbody>
  <form method="post" action="index.php?act=goods" name="listForm">
  <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
  <tr id="remove_<?php echo $this->_var['goods']['goods_id']; ?>">
    <td><input type="checkbox" name="checkboxes[]" value="<?php echo $this->_var['goods']['goods_id']; ?>" /><?php echo $this->_var['goods']['goods_id']; ?></td>
    <td><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></td>
    <td><?php echo $this->_var['goods']['goods_sn']; ?></td>
    <td align="right"><?php echo $this->_var['goods']['shop_price']; ?></td>
    <td align="center">

      <a href="index.php?act=goods&op=restore_goods&id=<?php echo $this->_var['goods']['goods_id']; ?>" onclick="{if(confirm('您确实要把该商品还原吗？')){return true;}return false;}">还原</a> |
      <a href="javascript:;" onclick="remove(<?php echo $this->_var['goods']['goods_id']; ?>,  'drop_goods')">删除</a>
    </td>
  </tr>
  <?php endforeach; else: ?>
  <tr class="no_data">
    <td colspan="10"><?php echo $this->_var['lang']['no_records']; ?></td>
  </tr>
  <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
  </tbody>
    <tfoot>
    <tr class="tfoot">
          <td>
      <input type="hidden" name="op" value="goods_batch" />
      <select name="type" id="selAction">
        <option value="">请选择...</option>
        <option value="restore">还原</option>
        <option value="drop">删除</option>
      </select>
      <input type="submit" value="确定"  name="btnSubmit" class="btn"  />
    </td>
    <td align="right" colspan="4" nowrap="true">
    <?php echo $this->fetch('page.htm'); ?>
    </td>
    </tr>
    </tfoot>
    </form>
</table>
<!-- end goods list -->
</div>


</form>
    </div>


  </div>
</div>










<?php echo $this->fetch('footer.htm'); ?>
