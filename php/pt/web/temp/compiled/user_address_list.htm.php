<!DOCTYPE html>
<html id="aos">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
<meta name="format-detection" content="telephone=no">
<meta http-equiv="Cache-Control" content="no-cache,no-store,must-revalidate"/>
<meta http-equiv="Pragma" content="no-cache"/>
<meta http-equiv="Expires" content="0"/>
<title>收货地址</title>
<link rel="shortcut icon" href="favicon.ico">
<link href="<?php echo $this->_var['template_path']; ?>css/common.css?v=<?php echo $this->_var['aos_version']; ?>" rel="stylesheet" />
<link href="<?php echo $this->_var['template_path']; ?>css/user.css?v=<?php echo $this->_var['aos_version']; ?>" rel="stylesheet" />
<script src="<?php echo $this->_var['template_path']; ?>js/jquery.min.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<script src="<?php echo $this->_var['template_path']; ?>js/common.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<script src="<?php echo $this->_var['template_path']; ?>js/user.js?v=<?php echo $this->_var['aos_version']; ?>"></script>
<body>
<section class="container pdb">
    <?php if ($this->_var['consignee_list']): ?>   
    <?php $_from = $this->_var['consignee_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }; $this->push_vars('sn', 'consignee');if (count($_from)):
    foreach ($_from AS $this->_var['sn'] => $this->_var['consignee']):
?>
    <?php if ($this->_var['consignee']['address_id']): ?>
    <div class="address_list">
        <dl>
            <dt><?php echo htmlspecialchars($this->_var['consignee']['consignee']); ?>&nbsp;&nbsp;<?php echo $this->_var['consignee']['mobile']; ?></dt>
            <dd><?php echo $this->_var['consignee']['area']; ?> <?php echo htmlspecialchars($this->_var['consignee']['address']); ?></dd>
            <dd class="btn">
                <div class="add_l">
                    <a id="address_<?php echo $this->_var['consignee']['address_id']; ?>" href="javascript:set_address(<?php echo $this->_var['consignee']['address_id']; ?>)"<?php if ($this->_var['consignee']['address_id'] == $this->_var['address']): ?> class="on"<?php endif; ?>><em><?php if ($this->_var['consignee']['address_id'] == $this->_var['address']): ?>已设为默认<?php else: ?>设为默认<?php endif; ?></em></a>
                </div>
                <div class="add_r">
                    <a class="edit" href="index.php?c=user&a=address&id=<?php echo $this->_var['consignee']['address_id']; ?>">编辑</a>
                    <a class="drop" href="javascript:drop_address(<?php echo $this->_var['consignee']['address_id']; ?>)">删除</a>
                </div>
            </dd>
        </dl>
    </div>
    <?php endif; ?>
    <?php endforeach; endif; unset($_from); ?><?php $this->pop_vars();; ?>
    <?php else: ?>
    <div class="line">
        <span>暂无收货地址，请尽快添加</span>
    </div>
    <?php endif; ?>
    <div class="fixed address_add"><a href="index.php?c=user&a=address">添加新地址</a></div>  
</section>
</body>
</html>
