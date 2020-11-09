<?php if ($this->_var['goods']['goods_id']): ?>
<dl>
  <dt>
    <a href="<?php echo $this->_var['goods']['url']; ?>"><img class="lazy_<?php echo $this->_var['page']; ?>" data-original="<?php echo $this->_var['goods']['goods_img']; ?>" src="uploads/images/no_picture.jpg"></a>
    <?php if ($this->_var['goods']['goods_number'] == 0): ?>
    <span class="soldout"></span>
    <?php endif; ?>
  </dt>
  <dd class="ico-box"><i><?php echo $this->_var['key']; ?></i></dd>
  <dd class="info-box">
      <a href="<?php echo $this->_var['goods']['url']; ?>" class="join-name"><?php echo $this->_var['goods']['goods_name']; ?></a>
      <p><span><?php echo $this->_var['goods']['tuan_price']; ?></span><?php echo $this->_var['goods']['min_number']; ?><?php if ($this->_var['goods']['max_number'] > $this->_var['goods']['min_number']): ?>-<?php echo $this->_var['goods']['max_number']; ?><?php endif; ?>人团</p>
      <a href="<?php echo $this->_var['goods']['url']; ?>" class="join-box">
        <span><b><?php echo $this->_var['goods']['sales']; ?>人</b>已参团</span>
        <span class="btn">去开团</span>
      </a>
  </dd>
</dl>
<?php endif; ?>
	