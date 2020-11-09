<?php if ($this->_var['goods']['goods_id']): ?>
<li class="tuan-item">
  <div class="goods-image">
    <a href="<?php echo $this->_var['goods']['url']; ?>">
      <img class="lazy_<?php echo $this->_var['page']; ?>" data-original="<?php echo $this->_var['goods']['tuan_img']; ?>" src="uploads/images/no_tuan_picture.jpg">
      <?php if ($this->_var['goods']['sales']): ?><span class="sales">已团<?php echo $this->_var['goods']['sales']; ?>件</span><?php endif; ?>
      <?php if ($this->_var['goods']['goods_video']): ?><i class="video"></i><?php endif; ?>
    </a>
    <?php if ($this->_var['goods']['goods_number'] == 0): ?>
    <span class="soldout"></span>
    <?php endif; ?>
  </div>

  <p class="goods-name"><a href="<?php echo $this->_var['goods']['url']; ?>"><?php echo $this->_var['goods']['goods_name']; ?></a></p>
  <div class="detail">
    <div class="left-side">
      <span class="tuan-num"><?php echo $this->_var['goods']['min_number']; ?><?php if ($this->_var['goods']['max_number'] > $this->_var['goods']['min_number']): ?>-<?php echo $this->_var['goods']['max_number']; ?><?php endif; ?>人团</span>
      <span class="sale-price"><?php echo $this->_var['goods']['tuan_price']; ?></span>
    </div>
    <?php if ($this->_var['goods']['goods_number'] == 0): ?>
    <div class="enter-button disable"><a href="<?php echo $this->_var['goods']['url']; ?>">已抢光</a></div>
    <?php else: ?>
    <div class="enter-button"><a href="<?php echo $this->_var['goods']['url']; ?>">去开团</a></div>
    <?php endif; ?>
    <div class="local-groups">
      <?php $_from = $this->_var['goods']['ing']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('', 'ing');if (count($_from)):
    foreach ($_from AS $this->_var['ing']):
?>
      <div class="avatar"><img src="<?php echo $this->_var['ing']['headimgurl']; ?>"></div>
      <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    </div>
  </div>
</li>
<?php endif; ?>
	