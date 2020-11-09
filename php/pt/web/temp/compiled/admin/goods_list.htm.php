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
  			    <li class="cur"><a href="javascript:void(0);">商品管理</a></li>
  				<li><a href="index.php?act=goods&amp;op=goods_add">添加商品</a></li>
  				<li><a href="index.php?act=goods&amp;op=goods_trash">商品回收站</a></li>
  			</ul>
  		</div>
	  </div>
	  <div class="main_bd">
      <div class="top_s">
        <form method="get" action="index.php">
          <input type="hidden" name="act" value="<?php echo $this->_var['action']; ?>">
          <input type="hidden" name="op" value="<?php echo $this->_var['operation']; ?>">
          <label for="search_goods_name">商品名称</label>
          <input type="text" value="<?php echo $this->_var['filter']['goods_name']; ?>" name="search_goods_name" id="search_goods_name" class="txt">
          <label for="search_commonid">商品货号</label>
          <input type="text" value="<?php echo $this->_var['filter']['goods_sn']; ?>" name="search_commonid" id="search_commonid" class="txt">
          <label>商品分类</label>
          <select class="querySelect" name="cat_id">
            <option value="0">全部分类</option>
            <?php echo $this->_var['cat_list']; ?>
          </select>
          <input type="submit" value="搜索" class="btn">
        </form>
      </div>
      <table class="table">
        <thead>
        <tr>
          <th><input onclick='checkAll(this, "checkboxes")' type="checkbox" class="table-list-checkbox-all" /></th>
          <th>ID</th>
          <th colspan="2">商品</th>
          <th>价格</th>
          <th>上架</th>
          <!--th>标签</th-->
          <th>排序</th>
          <th>虚拟销量</th>
          <th>操作</th>
        <tr>
        </thead>
        <form method="post" action="index.php?act=goods&op=goods_batch" name="listForm">
        <tbody>
        <?php $_from = $this->_var['goods_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'goods');if (count($_from)):
    foreach ($_from AS $this->_var['goods']):
?>
        <tr id="remove_<?php echo $this->_var['goods']['goods_id']; ?>">
          <td width="13"><input type="checkbox" name="checkboxes[]" value="<?php echo $this->_var['goods']['goods_id']; ?>" /></td>
          <td><?php echo $this->_var['goods']['goods_id']; ?></td>
          <td width="60"><img src="../<?php echo $this->_var['goods']['goods_img']; ?>" width="60" height="60"></td>
          <td><?php echo htmlspecialchars($this->_var['goods']['goods_name']); ?></td>
          <td width="80"><?php echo $this->_var['goods']['shop_price']; ?></td>
          <td class="binding" width="50">
            <span><i class="fa <?php if ($this->_var['goods']['is_on_sale']): ?>fa-toggle-on<?php else: ?>fa-toggle-off<?php endif; ?>" onclick="toggle(this, 'toggle_on_sale', <?php echo $this->_var['goods']['goods_id']; ?>)"></i></span>
          </td>
          <!--td>
            <div class="binding" width="70">
              <span>精品：<i class="fa <?php if ($this->_var['goods']['is_best']): ?>fa-toggle-on<?php else: ?>fa-toggle-off<?php endif; ?>" onclick="toggle(this, 'toggle_best', <?php echo $this->_var['goods']['goods_id']; ?>)"></i></span>
              <span>新品：<i class="fa <?php if ($this->_var['goods']['is_new']): ?>fa-toggle-on<?php else: ?>fa-toggle-off<?php endif; ?>" onclick="toggle(this, 'toggle_new', <?php echo $this->_var['goods']['goods_id']; ?>)"></i></span>
              <span>热门：<i class="fa <?php if ($this->_var['goods']['is_hot']): ?>fa-toggle-on<?php else: ?>fa-toggle-off<?php endif; ?>" onclick="toggle(this, 'toggle_hot', <?php echo $this->_var['goods']['goods_id']; ?>)"></i></span>
            </div>
          </td-->
          <td width="50"><span class="edit" onclick="edit(this, 'edit_sort_order', <?php echo $this->_var['goods']['goods_id']; ?> ,'goods')"><?php echo $this->_var['goods']['sort_order']; ?></span></td>
          <td width="60"><span class="edit" onclick="edit(this, 'edit_virtual_sales', <?php echo $this->_var['goods']['goods_id']; ?> ,'goods')"><?php echo $this->_var['goods']['virtual_sales']; ?></span></td>
          <td class="handle" width="90">
            <a href="index.php?act=goods&op=goods_edit&goods_id=<?php echo $this->_var['goods']['goods_id']; ?>">修改</a>
            <a  href="javascript:;" onclick="remove(<?php echo $this->_var['goods']['goods_id']; ?>,  'goods_remove')">删除</a>
          </td>
        </tr>
        <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </tbody>
      </table>
		  <div class="pagination">
        <div class="handler">
            <input type="hidden" name="type" value="trash" />
            <!--<input type="hidden" name="op" value="batch" />-->
            <input type="submit" id="btnSubmit" value="加入回收站" class="btn" />
          </form>
        </div>
        <?php echo $this->_var['pager']; ?>
      </div>
    </div>
	</div>
</div>










<?php echo $this->fetch('footer.htm'); ?>