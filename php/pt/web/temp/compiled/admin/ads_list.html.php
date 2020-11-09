<?php echo $this->fetch('header.htm'); ?>
<div class="main">
  <div class="col_side">
      <?php echo $this->fetch('side_menu.htm'); ?>
  </div>
  <div class="col_main">
    <div class="main_hd">
      <h2>轮播管理</h2>
      <div class="tab_navs">
        <ul>
          <li class="cur"><a href="javascript:void(0);">轮播管理</a></li>
          <li><a href="index.php?act=app&op=ad_add">添加轮播</a></li>
        </ul>
      </div>
    </div>
    <div class="main_bd">
      <table class="table">
        <thead>
        <tr>
          <th>广告名称</th>
          <th>广告图片</th>
          <th>开始日期</th>
          <th>结束日期</th>
          <th>排序</th>
          <th>启用</th>
          <th>操作</th>
        </thead>
        <tbody>
        <?php $_from = $this->_var['ads_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'list');if (count($_from)):
    foreach ($_from AS $this->_var['list']):
?>
        <tr id="remove_<?php echo $this->_var['list']['ad_id']; ?>">
          <td><?php echo htmlspecialchars($this->_var['list']['ad_name']); ?></td>
          <td><img src="../uploads/ads_img/<?php echo $this->_var['list']['ad_code']; ?>" width="100"></td>
          <td><?php echo $this->_var['list']['start_date']; ?></td>
          <td><?php echo $this->_var['list']['end_date']; ?></td>
          <td width="50"><span class="edit" onclick="edit(this, 'edit_sort_order', <?php echo $this->_var['list']['ad_id']; ?> ,'ads')"><?php echo $this->_var['list']['sort_order']; ?></span></td>
          <td class="binding" width="50">
            <span><i class="fa <?php if ($this->_var['list']['enabled']): ?>fa-toggle-on<?php else: ?>fa-toggle-off<?php endif; ?>" onclick="toggle(this, 'ad_enabled', <?php echo $this->_var['list']['ad_id']; ?>)"></i></span>
          </td>
          <td>
            <a href="index.php?act=app&op=ad_edit&id=<?php echo $this->_var['list']['ad_id']; ?>">修改</a>
            <a  href="javascript:" onclick="remove(<?php echo $this->_var['list']['ad_id']; ?>, 'ad_remove')">删除</a>
          </td>
        </tr>
        <?php endforeach; else: ?>
        <tr class="no_data">
          <td colspan="5">您还没有添加广告</td>
        </tr>
        <?php endif; unset($_from); ?><?php $this->pop_vars();; ?>
        </tbody>
      </table>
      <div class="pagination">
        <?php echo $this->_var['pager']; ?>
      </div>
    </div>
  </div>
</div>
<?php echo $this->fetch('footer.htm'); ?>