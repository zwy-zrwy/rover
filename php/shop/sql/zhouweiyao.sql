/*
Navicat MySQL Data Transfer

Source Server         : shop
Source Server Version : 50644
Source Host           : 111.229.160.29:3306
Source Database       : zhouweiyao

Target Server Type    : MYSQL
Target Server Version : 50644
File Encoding         : 65001

Date: 2020-11-09 17:33:57
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for `ad`
-- ----------------------------
DROP TABLE IF EXISTS `ad`;
CREATE TABLE `ad` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `pic` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `type` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of ad
-- ----------------------------
INSERT INTO `ad` VALUES ('2', '的点点滴滴', '20191121\\edf01b24c18354a78fb9c224cd3cbe2a.png', '435450767@qq.com', '1', '1');

-- ----------------------------
-- Table structure for `admin`
-- ----------------------------
DROP TABLE IF EXISTS `admin`;
CREATE TABLE `admin` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(30) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role_id` int(10) unsigned DEFAULT NULL,
  `status` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of admin
-- ----------------------------
INSERT INTO `admin` VALUES ('1', 'admin', '$2y$10$JV1KNVweV2WiiLlMtDGdLugcu9rd8rPi5507qll4ZJ7TVcrSYRVam', '1', '1');
INSERT INTO `admin` VALUES ('4', '商品管理员2', '$2y$10$JGjuknpcpa/ScMEje.6ps.f/ZBePbVErc589.zjSxpl//cWy4YnSS', '2', '1');

-- ----------------------------
-- Table structure for `adtype`
-- ----------------------------
DROP TABLE IF EXISTS `adtype`;
CREATE TABLE `adtype` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `width` int(11) DEFAULT NULL,
  `height` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of adtype
-- ----------------------------
INSERT INTO `adtype` VALUES ('1', '首页轮播图', '1200', '500');
INSERT INTO `adtype` VALUES ('2', '楼层顶部轮播图', '600', '300');

-- ----------------------------
-- Table structure for `brand`
-- ----------------------------
DROP TABLE IF EXISTS `brand`;
CREATE TABLE `brand` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL COMMENT '鍝佺墝鍚?',
  `status` tinyint(1) DEFAULT '1' COMMENT '鐘舵€?绂佺敤1鍚敤',
  `pic` varchar(255) DEFAULT NULL COMMENT '鍝佺墝鍥剧墖',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='商品品牌表';

-- ----------------------------
-- Records of brand
-- ----------------------------
INSERT INTO `brand` VALUES ('1', '可口可乐', '1', '20191112\\fa0547e611c32e29b12c673c549fa473.png');
INSERT INTO `brand` VALUES ('2', '乐视', '1', '20191114\\8b246ad0803a73a142f29c0211b70ccd.jpg');
INSERT INTO `brand` VALUES ('3', '创维', '1', '20191114\\00066357c0317724c1b236bcbd8a927a.jpg');

-- ----------------------------
-- Table structure for `cart`
-- ----------------------------
DROP TABLE IF EXISTS `cart`;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `goods_id` int(11) NOT NULL COMMENT '商品id',
  `goods_name` varchar(100) NOT NULL COMMENT '商品名',
  `goods_num` int(11) NOT NULL COMMENT '商品数量',
  `goods_price` decimal(10,2) NOT NULL COMMENT '鍟嗗搧浠锋牸',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `goods_sku` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=138 DEFAULT CHARSET=utf8 COMMENT='购物车';

-- ----------------------------
-- Records of cart
-- ----------------------------
INSERT INTO `cart` VALUES ('125', '11', '乐视（Letv）超级电视 ', '3', '1699.00', '10', null);
INSERT INTO `cart` VALUES ('131', '11', '乐视（Letv）超级电视 ', '1', '1699.00', '3', null);
INSERT INTO `cart` VALUES ('137', '10', '百事可乐', '3', '3.00', '3', null);

-- ----------------------------
-- Table structure for `category`
-- ----------------------------
DROP TABLE IF EXISTS `category`;
CREATE TABLE `category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL COMMENT '鍟嗗搧绉嶇被',
  `pic` varchar(255) DEFAULT NULL COMMENT '鍒嗙被鍟嗗搧鍥?',
  `status` tinyint(1) DEFAULT '1' COMMENT '鐘舵€?绂佺敤1鍚敤',
  `pid` int(11) DEFAULT NULL COMMENT '鍒嗙被绛夌骇',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of category
-- ----------------------------
INSERT INTO `category` VALUES ('1', '家用电器', '20191202/e17a9fa6c43e69113f3eec013fbbd425.jpg', '1', '0');
INSERT INTO `category` VALUES ('2', '空调', '20191202/5a084bbab1217aeb2cb63301e4561131.jpg', '1', '1');
INSERT INTO `category` VALUES ('3', '电视', '20191202/b81503cbb1712e650d9ab0dd3af0b1d3.jpg', '1', '1');
INSERT INTO `category` VALUES ('4', '洗衣机', '20191202/00e2b1dc3339f2c6f43db95fdfbaf2b5.jfif', '1', '1');
INSERT INTO `category` VALUES ('5', '空调挂机', '20191202/cd90ebf402c36869d405faaea63178b8.jpg', '1', '2');
INSERT INTO `category` VALUES ('6', '空调柜机', '20191202/8e8579048b62244c63728b352a0119a8.jpg', '1', '2');
INSERT INTO `category` VALUES ('7', '全面屏电视', '20191202/897c3a637046c9277d2ff7d7bb4461b4.jfif', '1', '3');
INSERT INTO `category` VALUES ('8', '超薄电视', '20191202/cf27b5217701a45e753afef2bbf27fe8.jfif', '1', '3');
INSERT INTO `category` VALUES ('9', '滚筒洗衣机', '20191202/23248303d6c4f7ce62c389c785f7279a.jfif', '1', '4');
INSERT INTO `category` VALUES ('10', '洗烘一体机', '20191202/1bb19f9458fcd0f7060248ff2f7694d0.jfif', '1', '4');
INSERT INTO `category` VALUES ('11', '食品', '20191202/692d556bd1539c63ce3bc96453a5e37d.jpg', '1', '0');
INSERT INTO `category` VALUES ('12', '新鲜水果', '20191202/e0a6be40f78ddac71c969b10359cc318.jfif', '1', '11');
INSERT INTO `category` VALUES ('13', '饮料冲调 ', '20191202/eb15eff68133f2c9b834a0ef6b419e22.jpg', '1', '11');
INSERT INTO `category` VALUES ('14', '粮油调味', '20191202/62ccf2996be38fc23924324aca03e8d9.jfif', '1', '11');
INSERT INTO `category` VALUES ('15', '苹果', '20191202/5d93ccd8b90d9156cc749335ec56609e.jfif', '1', '12');
INSERT INTO `category` VALUES ('16', '橙子', '20191202/65d53d1b3ab4f9342fe72b5a6d225fcd.jfif', '1', '12');
INSERT INTO `category` VALUES ('17', '饮料', '20191113\\13e126cb338ee370ebedbe2bf75cc4bb.jpg', '1', '13');
INSERT INTO `category` VALUES ('18', '牛奶酸奶', '20191202/9de31c7d04368ae8070e979b1514e90c.jpg', '1', '13');
INSERT INTO `category` VALUES ('19', '大米', '20191202/f1eb44298c6a6dc59ffe63dc149a69df.jpg', '1', '14');
INSERT INTO `category` VALUES ('20', '食用油', '20191202/77885b9e5fab06c09770dc4730992a51.jfif', '1', '14');

-- ----------------------------
-- Table structure for `goods`
-- ----------------------------
DROP TABLE IF EXISTS `goods`;
CREATE TABLE `goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic` varchar(255) DEFAULT NULL COMMENT '妫板嫯顫嶉崶?',
  `name` varchar(255) DEFAULT NULL COMMENT '鍟嗗搧鍚?',
  `content` text COMMENT '閸熷棗鎼х粻鈧禒?',
  `price` decimal(10,2) DEFAULT NULL COMMENT '濞寸娀鏀遍悧?',
  `number` int(11) DEFAULT NULL COMMENT '搴撳瓨',
  `cid` int(11) DEFAULT NULL COMMENT '閸熷棗鎼х粔宥囪',
  `status` tinyint(1) DEFAULT '1' COMMENT '鐘舵€?绂佺敤1鍚敤',
  `brand_id` int(10) unsigned DEFAULT NULL,
  `is_attr` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8 COMMENT='商品表';

-- ----------------------------
-- Records of goods
-- ----------------------------
INSERT INTO `goods` VALUES ('10', '20191112\\fa0547e611c32e29b12c673c549fa473.png', '百事可乐', '百事可乐(英文名称Pepsi-Cola)，诞生于19世纪90年代，', '3.00', '2000', '17', '1', '1', '0');
INSERT INTO `goods` VALUES ('11', '20191114\\e4969102a20f85c572fe4e12601743d7.jpg', '乐视（Letv）超级电视 ', 'Y43 43英寸 1GB+8GB大存储 人工智能全高清LED平板液晶网络超薄电视机', '1699.00', '200', '8', '1', '2', '0');
INSERT INTO `goods` VALUES ('12', '20191114\\ecada16a4d42ae1cce1b2c192c5011e5.jpg', '创维（SKYWORTH）', '43H5 43英寸4K超高清HDR 护眼全面屏 AI人工智能语音 蓝牙网络WIFI 液晶平板电视机1', '1999.00', '200', '7', '1', '3', '0');
INSERT INTO `goods` VALUES ('25', '20191117\\e34e805900dc2cb204c34ac3a98c2f5e.jpg', '樱桃味可乐', '哈哈哈哈哈1', '3.00', '1000', '17', '1', '1', '1');
INSERT INTO `goods` VALUES ('26', '20191119\\913fbba3183d678974322425d453d23b.jpg', '香蕉可乐', '香蕉味可乐', '3.00', '100', '17', '1', '1', '0');
INSERT INTO `goods` VALUES ('28', '20191119\\12c417458a5913d33a11aefc8180016c.jpg', '葡萄味可乐', '葡萄味可乐', '3.00', '100', '17', '1', '1', '0');
INSERT INTO `goods` VALUES ('29', '20191123\\e8629a9431fd57b96cb089a28407be39.jpg', '荔枝味可乐', '荔枝味可乐1', '3.00', '100', '5', '1', '1', '1');
INSERT INTO `goods` VALUES ('184', 'https://img.alicdn.com/imgextra/i3/4226848392/O1CN01csEIgR2BraMOIL3fU_!!4226848392.jpg', '迷你电饭煲正品多功能学生全自动式', '【居家必抢】环保内胆，不粘涂层，内胆环绕传热，米饭不夹生，不粘内胆，赖人常备，便捷式手提让你随时随地开小灶【赠运费险】', '49.90', '100000', '13', '1', '3', '0');
INSERT INTO `goods` VALUES ('185', 'https://img.alicdn.com/imgextra/i1/1785549470/O1CN01DeOm3t2JpJJjHCQ1O_!!0-item_pic.jpg', '【超火】加绒加厚冬季保暖棉靴', '天猫旗舰店品质，时尚马丁鞋秋天百搭，增高鞋！短裤、长裙，怎么搭怎么美。质量好到爆，趁活动，快跟小姐妹一人来一双，秀出大长腿，走到哪都是亮的星~', '89.00', '100000', '14', '1', '2', '0');
INSERT INTO `goods` VALUES ('186', 'https://img.alicdn.com/imgextra/i1/2719939093/O1CN01QlbXr72H2dzshzwB6_!!2719939093.jpg', '蒙古包狗窝冬季保暖狗屋猫窝四季通用', '一窝两用，可拆叠，易清洗，有窝无忧，换季不换窝，优质面料，舒适柔软，耐脏耐用，可长期使用，手洗机洗，清洗方便不易变形，陪爱宠走过春夏秋冬，特别的设计，给特别的TA！【赠运费险】', '11.80', '5000', '14', '1', '3', '0');
INSERT INTO `goods` VALUES ('187', 'https://img.alicdn.com/imgextra/i2/2129855975/O1CN01yQd5LU1u0ay3NgqRS_!!2129855975.jpg', '【家丽芙旗舰店】浴室橡塑防滑拖鞋', '甄选高弹力橡塑材质，简约大方的橡塑拖鞋，轻盈舒适，仿佛踩在云端的柔软，告别EVA的硬，告别PVC的重，多颜色可选，居家必备，精选推荐，爱生活享品质~【赠运费险】', '14.80', '100000', '3', '1', '1', '0');
INSERT INTO `goods` VALUES ('188', 'https://img.alicdn.com/imgextra/i3/799716377/O1CN01Q1KSsB1wyiHps0x7p_!!799716377.jpg', '短款毛领拼接菱格纯色棉衣女加厚保暖棉服', '【赠运费险】2019冬季新款复古毛毛领拼接菱格纯色棉服，加厚保暖短款潮流棉衣外套，修身显瘦，时尚百搭，甄选优质面料，柔软舒适，不缩水不缩水，3种颜色任你选择！冬季秒变女神！不可错过！', '185.00', '100000', '3', '1', '3', '0');
INSERT INTO `goods` VALUES ('189', 'https://img.alicdn.com/imgextra/i4/678275524/O1CN01YKqBGY1qg2QxFRnMo_!!678275524.jpg', '英雄7032钢笔三合一礼盒装', '不锈钢笔尖，笔身镂空设计，实时观察墨水剩余量，书写顺滑流畅，旋转式吸墨，使用方便稳定免费刻字，一笔三用套装', '43.00', '5000', '3', '1', '3', '0');
INSERT INTO `goods` VALUES ('190', 'https://img.alicdn.com/imgextra/i3/2399510805/O1CN01LateDy1HojNro8Z9E_!!2399510805.jpg', '【洛凡美皙】提亮肤色烟酰胺补水保湿精华液', '【正品保证】【9.9一件10.9两件】烟酰胺+玻尿酸精华原液！美白亮肤，提高肤色，补水保湿，国际高端奢华原液，让肌肤白嫩无暇，女神美白必备，女神防衰老必备，让你重返18岁~', '9.90', '100000', '12', '1', '1', '0');
INSERT INTO `goods` VALUES ('191', 'https://img.alicdn.com/imgextra/i1/1785549470/O1CN01BdT6Hn2JpJJZSqm8n_!!0-item_pic.jpg', '【稀世爱恋】增高加绒保暖套脚袜子鞋', '简约时尚，为舒适而生，内增高拉长腿部线条，更加美丽迷人！从内到外，让你舒服到底，原宿风高帮袜子鞋，美爆你的朋友圈！', '59.00', '100000', '12', '1', '3', '0');
INSERT INTO `goods` VALUES ('192', 'https://img.alicdn.com/imgextra/i4/2200778016598/O1CN01xMgUOw1ybvolTb0Hb_!!2200778016598.jpg', '【诗柯彩】老爹鞋女超火加绒厚底运动休闲鞋', '小仙女种草啦！冬季必囤，穿着舒服又暖和的！简单大气设计~非常百搭的款~裙装，裤装都hold得住！', '109.00', '100000', '3', '1', '1', '0');
INSERT INTO `goods` VALUES ('193', 'https://img.alicdn.com/imgextra/i3/1926226889/O1CN01shN4ga20lD3o44o8r_!!1926226889.jpg', '【买一送一】南极人聚拢哺乳文胸', '【现在拍下买一送一，到手2件！】专为哺乳期宝妈设计的文胸，无钢圈，舒适无压迫，聚拢放下垂，依然“挺拔”~前开扣设计，方便哺乳不尴尬，宝妈抢鸭！【赠运费险】', '69.00', '100000', '12', '1', '3', '0');
INSERT INTO `goods` VALUES ('194', 'https://img.alicdn.com/imgextra/i2/1591665776/O1CN01iCUQj11sXSDQQJPpD_!!1591665776.jpg', '植贝寡肽面膜修复补水精华20片', '【拍1件19.9，拍2件29.9，20片！草本25ml足量精华液】历经2年定制测试，逾5000人肤测，1000家美容院测试。补水抗敏感，修护微创受损肌肤，敏感肌肤、美容项目修护，日常补水保养', '19.90', '80000', '13', '1', '2', '0');
INSERT INTO `goods` VALUES ('195', 'https://img.alicdn.com/imgextra/i3/2970693153/O1CN019Z0Ilc1ZA7MXswHQA_!!2970693153.jpg', '【抖音必秒】特级流心吊柿饼1000g', '【29元1000g】香甜软糯柿饼，肉质致密，纤维少汁液多，农家传统手工12道工序霜降精制，味道甜美，营养丰富。坏果包赔【赠运费险】', '29.90', '100000', '14', '1', '2', '0');
INSERT INTO `goods` VALUES ('196', 'https://img.alicdn.com/bao/uploaded/i3/2206596444419/O1CN01LSfk6G1iVwn4KsEwI_!!2206596444419.jpg', '包芯纱毛衣秋冬外穿慵懒风打底衫', '抖音网红爆款，不易起球，不抽丝，弹性收腹，柔软舒适，收腹瘦腰塑造迷人小蛮腰，减肥不是一天，显瘦却能立竿见影，勾勒高挑身材！', '39.90', '100000', '13', '1', '3', '0');
INSERT INTO `goods` VALUES ('197', 'https://img.alicdn.com/imgextra/i3/2284669707/O1CN01BI4xG52LZrBX9TdDR_!!2284669707.jpg', '【羽克】新款可爱儿童连体泳衣', '【羽克旗舰店】泳衣大牌，温泉、沙滩标配女童连体游泳衣，穿脱方便，内置底裤，给宝宝公主般的泳池体验，可舒服了，假期出游装备先买起来！', '34.90', '100000', '12', '1', '1', '0');
INSERT INTO `goods` VALUES ('198', 'https://img.alicdn.com/bao/uploaded/O1CN01tN8HZz2H6lY8hqhoZ_!!2-item_pic.png', '【抖音同款】修复淡化青春痘荟胶护肤品', '佰束旗舰店日本药妆店同步销售！急护祛痘，无激素无副作用敏感痘肌专用，效果明显！专注解决各类顽固痘痘，平衡水油，深层修护，祛痘淡印，收缩毛孔，青少年学生党的祛痘神器，战“痘”到底不留痕~', '8.80', '10000', '12', '1', '3', '0');
INSERT INTO `goods` VALUES ('199', 'https://img.alicdn.com/imgextra/i2/3728395164/TB2hL9VwkOWBuNjSsppXXXPgpXa_!!3728395164.jpg', '益辈子生菜籽粉500克现磨', '【超值500g装】20年品牌实力。来自长白山的生菜籽纯纯粉，不加糖，不加香精，不加防腐剂。低温烘焙，高温灭菌，自然鲜香，营养健康，老少皆宜，是接骨、壮骨及补钙不错的选择。【赠运险费】', '38.00', '2000', '4', '1', '2', '0');
INSERT INTO `goods` VALUES ('200', 'https://img.alicdn.com/imgextra/i3/1785549470/O1CN01fSxbAC2JpJJj3oimT_!!0-item_pic.jpg', '【百人验货】秋冬加绒增高保暖鞋', '精挑细选，手感柔滑，舒适透气，时尚美观，好货不等于昂贵，舒适大气，满足人体工学原理，拉长腿部线条', '79.00', '100000', '3', '1', '1', '0');
INSERT INTO `goods` VALUES ('201', 'https://img.alicdn.com/i2/1869822355/O1CN0165Zkle1TGdIERIhKe_!!1869822355.jpg', '加厚连裤袜加绒光腿打底裤', '加厚连裤袜加绒美腿神器，高弹显瘦、穿出裸感美肌，不起球不勾丝，特好搭配衣服，拒绝臃肿，微压塑型超显瘦，裸感美肌。', '19.90', '10000', '13', '1', '1', '0');
INSERT INTO `goods` VALUES ('202', 'https://img.alicdn.com/imgextra/i3/1785549470/O1CN01AgZSEO2JpJJTOMR8w_!!0-item_pic.jpg', '【超火】杨幂同款秋冬加绒马丁靴', '天猫旗舰店品质，时尚马丁鞋秋天百搭，显瘦设计款！短裤、长裙，怎么搭怎么美。舒适又不累脚，趁活动，快跟小姐妹一人来一双，秀出大长腿，走到哪都是亮的星~【送运费险】', '69.00', '100000', '4', '1', '2', '0');
INSERT INTO `goods` VALUES ('203', 'https://gd1.alicdn.com/imgextra/i1/1722796410/O1CN013RUYqC1xDpLuZTeh2_!!0-item_pic.jpg', '迷你双面红桃心正品项链锁骨链女吊坠', '抖音爆款！迷你双面红桃心，日韩简约清新，锁骨链长短刚刚好，多款吊坠选择，速抢~', '9.80', '100000', '3', '1', '3', '0');
INSERT INTO `goods` VALUES ('204', 'https://img.alicdn.com/imgextra/i2/737104095/O1CN01KOYqyW1g7YRssa5Aw_!!737104095.jpg', '汤臣倍健孕妈孕妇维生素D3补钙片', '丹麦进口维生素D3，给妈妈加骨劲，缓解抽筋腰酸，促进钙吸收，2瓶120粒，超划算哦~！', '62.00', '1000', '3', '1', '1', '0');
INSERT INTO `goods` VALUES ('205', 'https://img.alicdn.com/imgextra/i4/1867640421/TB2uUSuXPzyQeBjy1zdXXaInpXa_!!1867640421.jpg', '除甲醛去味竹炭包活性炭筒备长竹碳', '采用五年以上老竹高温烧制，独立OPP塑料密封，家居、办公场所用，既有净化空气的效果又美丽。', '40.80', '10000', '14', '1', '3', '0');
INSERT INTO `goods` VALUES ('206', 'https://img.alicdn.com/bao/uploaded/TB1Tf6Zjr2pK1RjSZFsXXaNlXXa.png', '汽车车载流行音乐DJ优盘MP4', '16G，32g车载音乐U盘，震撼音效，强劲舞曲，黑胶芯片，大容量，防水防震，高速传输，即插即用，拒绝假货，高品质音质，车载佳品。', '29.00', '10000', '3', '1', '3', '0');
INSERT INTO `goods` VALUES ('207', 'https://img.alicdn.com/bao/uploaded/O1CN01ezjZaS2K0lDTW0q2q_!!2-item_pic.png', '40包！【植护旗舰店】原木抽纸巾', '植护原生木浆抽纸巾，无荧光剂，柔韧吸水不易破，擦拭无尘屑，皮肤不刺激。默认发27包！备注A40发40包！！', '26.90', '100000', '14', '1', '2', '0');
INSERT INTO `goods` VALUES ('208', 'https://img.alicdn.com/imgextra/i1/2976250623/O1CN01fyMJaF1GTNFBNB0Tf_!!0-item_pic.jpg', '五指棉手套女加绒加厚保暖', '【爆款线手套】加绒加厚，户外骑车手套超舒服，带着手套玩手机，让你这个冬天不再寒冷！', '13.90', '100000', '14', '1', '2', '0');
INSERT INTO `goods` VALUES ('209', 'https://img.alicdn.com/imgextra/i2/3226952177/O1CN019L2wlU1Rx6oKM2Gxd_!!3226952177.jpg', '上海香皂 上海硫磺皂洗澡香皂', '【超值5块装，下单再随机送一块香皂】杀灭细菌、真菌、霉菌以及螨虫、寄生虫、祛痘等。用来洗澡，脸，洗衣服，洗内衣，洗枕套被套都可以，很实用【赠运费险】', '18.30', '100000', '3', '1', '1', '0');
INSERT INTO `goods` VALUES ('210', 'https://img.alicdn.com/bao/uploaded/i4/723837224/O1CN01VcFC7E23EdkkjkWFG_!!0-item_pic.jpg', '【清仓】小奖状小学生多款通用奖状纸a4', '学生奖状多款通用奖状纸a4，可打印空白手写奖状创意奖状，小学生奖状教师专用，100G加厚纸张，普通纸加厚纸防水防污两款可选', '5.90', '8000', '13', '1', '1', '0');
INSERT INTO `goods` VALUES ('211', 'https://img.alicdn.com/imgextra/i1/1077535870/O1CN01MaLb731tEVTVPUvvp_!!1077535870-2-daren.png', '拍1发3！维生素VE乳300ml', '【全国包邮】拍1发3，三瓶到手仅需19.9！形象美维生素VE乳，为你轻松打造水嫩美肌~维生素E成分，天然温和，深层保湿，敏感肌也能用！持久滋润，秋冬选ta准没错！', '19.90', '100000', '13', '1', '3', '0');
INSERT INTO `goods` VALUES ('212', 'https://img.alicdn.com/imgextra/i2/2200778016598/O1CN01Jly7v61ybvo896fKy_!!2200778016598.jpg', '【诗柯彩】毛毛鞋女冬季加绒保暖一脚蹬棉鞋', '网红同款，时尚显瘦鞋秋冬百搭，短裤、长裙，怎么搭怎么美。舒适又不累脚，趁活动，快跟小姐妹一人来一双，秀出大长腿，走到哪都是亮的星~', '59.00', '100000', '4', '1', '3', '0');
INSERT INTO `goods` VALUES ('213', 'https://img.alicdn.com/i4/2738701223/O1CN01nvQtgf1KuAszzSuQd_!!2738701223.jpg', '抗液压剪电动防盗车锁摩托车锁', '超强防盗U型锁，抗剪抗据，防砸防撬，纯铜材质耐磨不生锈，高强度锁梁，防小偷破坏', '29.00', '10000', '13', '1', '1', '0');
INSERT INTO `goods` VALUES ('214', 'https://img.alicdn.com/imgextra/i4/1785549470/O1CN014Y9XDf2JpJJR1r5Qm_!!0-item_pic.jpg', '【超火】长绒保暖平底靴子', 'TPR材质耐磨防滑大底，脚感炸裂毫无踩屎感！好货不等于昂贵，时下流行，随性百搭，出街必备，给你高端舒适的体验~【赠送运费险】', '89.00', '100000', '3', '1', '1', '0');
INSERT INTO `goods` VALUES ('215', 'https://img.alicdn.com/imgextra/i4/2216909097/O1CN010nYMm82H4TZK5b06C_!!2216909097.jpg', '旅行神器户外折叠小板凳', '【第二件半价】排队神器折叠小板凳地铁凳，轻负重便携，承重强方便实用，户外活动钓鱼美术写生坐火车常备，免安装一体收纳，稳固支架设计，坐如山稳重，速度抢！', '26.00', '20000', '12', '1', '1', '0');
INSERT INTO `goods` VALUES ('216', 'https://img.alicdn.com/imgextra/i3/2200682639901/O1CN010GJV0x2N0i0yKZuyO_!!2200682639901.jpg', '儿童加绒裤子宝宝加厚保暖秋裤', '加绒保暖高腰护肚裤，柔软舒适，亲肤，不紧勒，宝宝活动不受限！锁温持久，保持蓄热，一件过冬！【赠运费险】', '13.90', '100000', '4', '1', '2', '0');
INSERT INTO `goods` VALUES ('217', 'https://img.alicdn.com/imgextra/i2/2968425836/O1CN01aSKLgY1syw0qoEXVO_!!2968425836.jpg', '男士宽松加绒休闲裤束脚工装裤九分裤男裤', '潮流摩登时尚风，穿搭随性！简约修身，优质面料，舒适抗皱，时尚百搭，不易褪色，透气有弹力，随心搭，穿出自己的格调，男神必备款！', '89.00', '50000', '13', '1', '3', '0');
INSERT INTO `goods` VALUES ('218', 'https://img.alicdn.com/imgextra/i4/2805649662/O1CN01PHYSGV2LFFJP7iaWc_!!2805649662.jpg', '火车头摩托车碟刹锁电动车碟刹锁刹', '【火车头】碟刹锁合金材质，六大技术防盗升级，自动寻位上锁，受力点加厚加硬，超B级锁芯升级，锁体加大，内部传动结构升级，适用性更广！物美价廉，快快行动！！', '21.00', '10000', '13', '1', '3', '0');
INSERT INTO `goods` VALUES ('219', 'https://img.alicdn.com/i4/2012845533/O1CN01PmaSXu1qk9zuY02wh_!!2012845533.jpg', '女加绒加厚修手可爱保暖皮手套', '加绒加厚不加价，保暖全新升级！优质PU皮，防风抗寒，锁温保暖，全掌颗粒防滑，可触屏设计，一杯奶茶的价钱，让你冬天也能保护好双手！', '8.80', '10000', '2', '1', '3', '0');
INSERT INTO `goods` VALUES ('220', 'https://img.alicdn.com/imgextra/i4/2200778016598/O1CN01wGvnQZ1ybvoRuZVC0_!!2200778016598.jpg', '【诗柯彩】长靴子女加绒过膝瘦瘦靴显腿长', '小仙女种草啦！冬季必囤，穿着舒服又暖和的，加绒加厚长靴！简单大气设计~非常百搭的款~裙装，裤装都hold得住！', '89.90', '100000', '13', '1', '2', '0');
INSERT INTO `goods` VALUES ('221', 'https://img.alicdn.com/imgextra/i1/3322855491/O1CN01o2ITXr1qQvNC1nrKL_!!3322855491.jpg', '多功能便携斜跨证件卡包保护套袋', '【聚划算】多功能收纳包，小身材大容量，轻量便携护照证件包，防水面料设计，按扣设计，拉链隔层设计，分类收纳，出差旅行必备！潮男靓女必抢！赠运费险！售价49元，领10元券包邮秒杀！券后只需39元，抢~', '39.00', '100000', '3', '1', '3', '0');
INSERT INTO `goods` VALUES ('222', 'https://img.alicdn.com/imgextra/i2/2184988482/O1CN01NwUlff2CWnzo8vioP_!!2184988482.jpg', '【暖宫护肾】双层加绒高腰内裤', '【保暖收腹两不误】引用美国面料，德国轻奢设计！一条高腰保暖内裤=一条收腹裤+一条保暖裤，这个冬季一件保暖过冬，才用优质牛奶绒，亲肤不卷边，拯救各种宫寒怕冷小肚腩！', '39.90', '100000', '4', '1', '3', '0');
INSERT INTO `goods` VALUES ('223', 'https://img.alicdn.com/imgextra/i2/209384611/O1CN01hRhxaE1jvspU3GCf5_!!209384611.jpg', '电热水壶厨房热水壶双层防烫家用', 'lenrood邻鹿电热水壶，厨房热水壶，双层防烫，家用水壶，开水壶不锈钢！', '49.00', '20000', '13', '1', '2', '0');
INSERT INTO `goods` VALUES ('224', 'https://img.alicdn.com/imgextra/i1/4067723542/O1CN01yr0LZy1c2HQ3pIjfp_!!0-item_pic.jpg', '高腰宽松显瘦女裤大码弹力小脚裤子', '超好质量的魔术裤，不抽丝不起球，外穿十年不坏！提臀修身有弹力，拉长腿部线条，视觉显瘦10斤！女神必备魔术裤~【赠运费险】', '448.00', '100000', '2', '1', '1', '0');

-- ----------------------------
-- Table structure for `goods_photo`
-- ----------------------------
DROP TABLE IF EXISTS `goods_photo`;
CREATE TABLE `goods_photo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pic` varchar(255) DEFAULT NULL COMMENT '鍟嗗搧鍥剧墖',
  `gid` int(11) DEFAULT NULL COMMENT '鍟嗗搧ID',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=65 DEFAULT CHARSET=utf8 COMMENT='商品相册';

-- ----------------------------
-- Records of goods_photo
-- ----------------------------
INSERT INTO `goods_photo` VALUES ('6', '20191114\\d737c4fdd5fe39dfb33ca178208b4907.jpg', '11');
INSERT INTO `goods_photo` VALUES ('5', '20191112\\b63020ff0e82f2d6ac8d92cbf09ae047.jpg', '10');
INSERT INTO `goods_photo` VALUES ('4', '20191112\\fec9f366bd9b3d042f184ae2cd6452d6.jpg', '10');
INSERT INTO `goods_photo` VALUES ('7', '20191114\\27aa7bb002661b5047129906ef72c88b.jpg', '11');
INSERT INTO `goods_photo` VALUES ('8', '20191114\\780ebea99901ba5bb5e8ddd40721c91a.jpg', '11');
INSERT INTO `goods_photo` VALUES ('9', '20191114\\3a7ad0c7430b31a3f9ca2cc5f6b2c9c2.jpg', '12');
INSERT INTO `goods_photo` VALUES ('10', '20191114\\3f6a2585e1a1f7d78630132d49a5b494.jpg', '12');
INSERT INTO `goods_photo` VALUES ('11', '20191114\\4e5c82a2fe1214bc7e2713d4faabb95b.jpg', '12');
INSERT INTO `goods_photo` VALUES ('60', '20191119\\9fe4cd4c0834b90cdc40204b4b19950d.jpg', '28');
INSERT INTO `goods_photo` VALUES ('59', '20191119\\ee3cbc3a360b68b664ceae91da898f7a.png', '28');
INSERT INTO `goods_photo` VALUES ('56', '20191119\\c299ee6aa2859dfcc4503833931a3c73.jpg', '26');
INSERT INTO `goods_photo` VALUES ('55', '20191119\\7be1d4f9cbb545081912d98a4e1b5336.png', '26');
INSERT INTO `goods_photo` VALUES ('54', '20191117\\2c1aed3a268d06d2387215de2ee0fdcf.jpg', '25');
INSERT INTO `goods_photo` VALUES ('53', '20191117\\6adfaa874f9d8579e24bfd46e809cbba.jpg', '25');
INSERT INTO `goods_photo` VALUES ('61', '20191123\\34a016db2d804779b6d9f31eb80bbcb9.jpg', '29');
INSERT INTO `goods_photo` VALUES ('62', '20191123\\8e4a4ac4853df235d6b28fc01e82e2eb.jpg', '29');

-- ----------------------------
-- Table structure for `material`
-- ----------------------------
DROP TABLE IF EXISTS `material`;
CREATE TABLE `material` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `pic` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT NULL,
  `media_id` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of material
-- ----------------------------
INSERT INTO `material` VALUES ('13', '图片', '20191130/f8b6afa106df2f59b065032b8129e5b7.jpg', 'image', 'SdgRNgS3WDA4-UqwbtlSgQlquVv3TXdPu4VrEQqFIZNTWSDSXpkguGsbcBRkvYAm');
INSERT INTO `material` VALUES ('14', '语音', '20191130/294e0d03dbba9cde2d8500e21b887d51.mp3', 'voice', '6uju0MwlbRmRIpZAF9bCQS47d14IZOEsEkXVefFi4iLhnvDmkk-GC_pMHs1FAIqB');
INSERT INTO `material` VALUES ('15', '视频', '20191130/50a9b5d1c0f4e0c6c01b198db34d64e5.mp4', 'video', 'bLFWauZH_p7GWB84f-YISebjplmBV4al0pN3VujZMRNL5XSZ9jP98_z_fELcyyNq');

-- ----------------------------
-- Table structure for `node`
-- ----------------------------
DROP TABLE IF EXISTS `node`;
CREATE TABLE `node` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `con` varchar(30) DEFAULT NULL COMMENT '鎺у埗鍣?',
  `pid` int(11) DEFAULT NULL COMMENT '鏉冮檺绾у埆',
  `status` tinyint(1) DEFAULT '1' COMMENT '閻樿埖鈧?缁備胶鏁?閸氼垳鏁?',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of node
-- ----------------------------
INSERT INTO `node` VALUES ('1', '权限管理', null, '0', '1');
INSERT INTO `node` VALUES ('2', '商品管理', null, '0', '1');
INSERT INTO `node` VALUES ('3', '管理员管理', 'Admin', '1', '1');
INSERT INTO `node` VALUES ('4', '角色管理', 'Role', '1', '1');
INSERT INTO `node` VALUES ('5', '节点管理', 'Node', '1', '1');
INSERT INTO `node` VALUES ('6', '商品管理', 'Goods', '2', '1');
INSERT INTO `node` VALUES ('7', '商品分类', 'Category', '2', '1');
INSERT INTO `node` VALUES ('21', '微信管理', ' ', '0', '1');
INSERT INTO `node` VALUES ('17', '品牌管理', 'Brand', '2', '1');
INSERT INTO `node` VALUES ('18', '商品规格', 'Sttr', '2', '1');
INSERT INTO `node` VALUES ('19', '广告管理', 'Ad', '2', '1');
INSERT INTO `node` VALUES ('20', '广告栏位', 'Adtype', '2', '1');
INSERT INTO `node` VALUES ('22', '微信设置', 'Wxconfig', '21', '1');
INSERT INTO `node` VALUES ('23', '自定义菜单', 'Wxmenu', '21', '1');
INSERT INTO `node` VALUES ('24', '关键字回复', 'Reply', '21', '1');
INSERT INTO `node` VALUES ('25', '素材管理', 'Material', '21', '1');
INSERT INTO `node` VALUES ('26', '消息推送', 'Sendmsg', '21', '1');

-- ----------------------------
-- Table structure for `order_goods`
-- ----------------------------
DROP TABLE IF EXISTS `order_goods`;
CREATE TABLE `order_goods` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `goods_name` varchar(100) NOT NULL,
  `goods_price` decimal(10,2) NOT NULL,
  `goods_num` int(11) NOT NULL,
  `goods_sku` int(11) DEFAULT NULL,
  `goods_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of order_goods
-- ----------------------------
INSERT INTO `order_goods` VALUES ('21', '11', '乐视（Letv）超级电视 ', '1699.00', '1', null, '11');
INSERT INTO `order_goods` VALUES ('20', '11', '樱桃味可乐', '0.01', '1', '10', '25');
INSERT INTO `order_goods` VALUES ('19', '11', '樱桃味可乐', '0.01', '1', '10', '25');
INSERT INTO `order_goods` VALUES ('22', '12', '樱桃味可乐', '0.01', '1', '10', '25');
INSERT INTO `order_goods` VALUES ('23', '13', '樱桃味可乐', '0.01', '1', '10', '25');
INSERT INTO `order_goods` VALUES ('24', '14', '樱桃味可乐', '3.00', '1', null, '25');
INSERT INTO `order_goods` VALUES ('25', '14', '百事可乐', '3.00', '2', null, '10');

-- ----------------------------
-- Table structure for `order_info`
-- ----------------------------
DROP TABLE IF EXISTS `order_info`;
CREATE TABLE `order_info` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_sn` varchar(30) NOT NULL,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `mobile` char(11) NOT NULL COMMENT '电话',
  `name` varchar(30) NOT NULL COMMENT '名称',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `add_time` int(11) NOT NULL COMMENT '添加时间',
  `pay_time` int(11) DEFAULT NULL,
  `payment` varchar(50) NOT NULL DEFAULT '0' COMMENT '閺€顖欑帛閺傜懓绱?',
  `shipping` varchar(30) DEFAULT NULL COMMENT '闁板秹鈧焦鏌熷?',
  `ship_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '閰嶉€佺姸鎬?',
  `sum_price` decimal(10,2) NOT NULL COMMENT '鍗曞彿',
  `pay_status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '鏀粯鐘舵€?',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of order_info
-- ----------------------------
INSERT INTO `order_info` VALUES ('11', '2019112398505598', '3', '17799485242', '之人未央', '会展中心', '1574504075', null, 'Alipay', '韵达', '0', '0.02', '0');
INSERT INTO `order_info` VALUES ('12', '2019113049555249', '3', '17799485242', '之人未央', '会展中心', '1575074385', null, 'Alipay', '韵达', '0', '0.01', '0');
INSERT INTO `order_info` VALUES ('13', '2019113048481011', '3', '17799485242', '之人未央', '会展中心', '1575074464', null, 'Alipay', '韵达', '0', '0.01', '0');
INSERT INTO `order_info` VALUES ('14', '2020011652545199', '3', '17799485242', '之人未央', '会展中心', '1579157764', null, 'Alipay', '韵达', '0', '3.00', '0');

-- ----------------------------
-- Table structure for `pay`
-- ----------------------------
DROP TABLE IF EXISTS `pay`;
CREATE TABLE `pay` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `code` varchar(30) DEFAULT NULL,
  `pic` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of pay
-- ----------------------------
INSERT INTO `pay` VALUES ('1', '微信支付', 'wechatxpay', null, '1');
INSERT INTO `pay` VALUES ('2', '支付宝支付', 'Alipay', null, '1');

-- ----------------------------
-- Table structure for `reply`
-- ----------------------------
DROP TABLE IF EXISTS `reply`;
CREATE TABLE `reply` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `keys` varchar(40) DEFAULT NULL,
  `content` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of reply
-- ----------------------------
INSERT INTO `reply` VALUES ('1', '文本', '文本内容', '1');
INSERT INTO `reply` VALUES ('2', '图文', '25', '2');
INSERT INTO `reply` VALUES ('3', '语音', '11', '3');
INSERT INTO `reply` VALUES ('4', '视频', '12', '4');

-- ----------------------------
-- Table structure for `role`
-- ----------------------------
DROP TABLE IF EXISTS `role`;
CREATE TABLE `role` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL COMMENT '角色名',
  `nodes` varchar(255) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '鐘舵€?绂佺敤1鍚敤',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of role
-- ----------------------------
INSERT INTO `role` VALUES ('1', '超级管理员', '', '1');
INSERT INTO `role` VALUES ('2', '商品管理员', '2,6,7', '1');
INSERT INTO `role` VALUES ('3', '权限管理员', '1,3,4,5', '1');
INSERT INTO `role` VALUES ('4', '商品管理员', '2,6,7', '1');
INSERT INTO `role` VALUES ('5', '微信管理员', '1,2', '1');

-- ----------------------------
-- Table structure for `sendmsg`
-- ----------------------------
DROP TABLE IF EXISTS `sendmsg`;
CREATE TABLE `sendmsg` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `ids` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sendmsg
-- ----------------------------
INSERT INTO `sendmsg` VALUES ('1', '测试推送', '12', '1');

-- ----------------------------
-- Table structure for `sku`
-- ----------------------------
DROP TABLE IF EXISTS `sku`;
CREATE TABLE `sku` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `price` decimal(10,2) DEFAULT NULL,
  `num` int(11) DEFAULT NULL,
  `gid` int(11) DEFAULT NULL,
  `attr_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sku
-- ----------------------------
INSERT INTO `sku` VALUES ('12', '33.00', '333', '25', '1');
INSERT INTO `sku` VALUES ('11', '22.00', '222', '25', '2');
INSERT INTO `sku` VALUES ('10', '0.01', '111', '25', '3');
INSERT INTO `sku` VALUES ('13', '0.01', '100', '29', '1');
INSERT INTO `sku` VALUES ('14', '0.02', '200', '29', '2');
INSERT INTO `sku` VALUES ('15', '0.03', '300', '29', '3');

-- ----------------------------
-- Table structure for `sttr`
-- ----------------------------
DROP TABLE IF EXISTS `sttr`;
CREATE TABLE `sttr` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of sttr
-- ----------------------------
INSERT INTO `sttr` VALUES ('1', '红色', '1');
INSERT INTO `sttr` VALUES ('2', '蓝色', '1');
INSERT INTO `sttr` VALUES ('3', '绿色', '1');

-- ----------------------------
-- Table structure for `user`
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(60) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `openid` varchar(100) DEFAULT NULL,
  `nickname` varchar(100) DEFAULT NULL,
  `sex` tinyint(1) DEFAULT NULL,
  `province` varchar(60) DEFAULT NULL,
  `city` varchar(60) DEFAULT NULL,
  `headimgurl` varchar(255) DEFAULT NULL,
  `Longitude` varchar(100) DEFAULT NULL,
  `Latitude` varchar(100) DEFAULT NULL,
  `xopenid` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES ('1', '李苏虎', '$2y$10$JV1KNVweV2WiiLlMtDGdLugcu9rd8rPi5507qll4ZJ7TVcrSYRVam', null, 'oEpvdtzlKhzyjRsoOrfWz4AWI_z4', '夜斗君', '1', '哈杜马蒂', '', 'http://thirdwx.qlogo.cn/mmopen/vi_32/w5CHlVGQkT5Mm0WWL0QEh0hDKIp1MicCbgbRYmShIcY9Cjh2MibKvavT7KIibV3hwKZ8ztoqLRNP6OuOu3rkA7QrQ/132', '108.924507', '34.231834', null);
INSERT INTO `user` VALUES ('2', 'user', '$2y$10$eH8URKK9qZj8NKmj2tl.m.hAZu93OELYI/m6T8PQddyQa2O0e52wC', '435450767@qq.com', '', '', '0', '', '', '', null, null, null);
INSERT INTO `user` VALUES ('3', '之人未央', '$2y$10$nzev4qpuUsHkpqN2Jhc0u.0EqwT7IqK8Xdwot/ZE9MCFOOVxVHxge', '435450767@qq.com', 'oEpvdt8tVO2-NzFNtTenFFeeik8Y', '之人未央', '1', '陕西', '西安', 'http://thirdwx.qlogo.cn/mmopen/vi_32/Q0j4TwGTfTL0vMbFxMJhueTInk6gk2nh7HxIsMkUvQyv0sQIYs4x05tKvVNGMl2F2pROpgX7prj9pBlnxoaSbw/132', '108.924583', '34.229404', 'o4WeluJdFnOqA9b5U34vXZNDZNY4');

-- ----------------------------
-- Table structure for `user_address`
-- ----------------------------
DROP TABLE IF EXISTS `user_address`;
CREATE TABLE `user_address` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL COMMENT '用户id',
  `name` varchar(30) NOT NULL COMMENT '名称',
  `mobile` char(11) NOT NULL COMMENT '电话号码',
  `address` varchar(255) NOT NULL COMMENT '详细地址',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of user_address
-- ----------------------------
INSERT INTO `user_address` VALUES ('1', '1', '张三', '13745788457', '会展中心');
INSERT INTO `user_address` VALUES ('2', '1', '李四', '13845768549', '小寨');
INSERT INTO `user_address` VALUES ('3', '3', '之人未央', '17799485242', '会展中心');
INSERT INTO `user_address` VALUES ('4', '3', '之人未央', '17799485242', '小寨');

-- ----------------------------
-- Table structure for `wxconfig`
-- ----------------------------
DROP TABLE IF EXISTS `wxconfig`;
CREATE TABLE `wxconfig` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `appid` varchar(60) DEFAULT NULL,
  `appsecret` varchar(100) DEFAULT NULL,
  `token` varchar(30) DEFAULT NULL,
  `access_token` varchar(255) DEFAULT NULL,
  `msg` varchar(255) DEFAULT NULL,
  `jsapi_ticket` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wxconfig
-- ----------------------------
INSERT INTO `wxconfig` VALUES ('1', '之人未央', 'wxfb3351a32346d7d4', 'af90069c185325bd9b49168c0f066550', 'zhoudayao', '34_0uMWXNsoS5DpJSYet5Jqi8gEirSc66w1Xin1HtPiAaLKgZCGFrQVaQRIFKIMAUAUIvVjj3gcOjngFPKtwknEGGRHWfm2ATQMH2td8Ujq_fRPetPQqDDHWEk5JmEnVO8u5gKvaq4kH5kZw5b2VXTeAAAQSO', '欢迎关注！\r\n之人未央的测试公众号O(∩_∩)O', 'sM4AOVdWfPE4DxkXGEs8VBrLk5cX4L0cQCW4Xl-p1pThW50MM6EgwZZgRCugf97oaDjj7X5GPXPzMqwhmP6LSA');

-- ----------------------------
-- Table structure for `wxmenu`
-- ----------------------------
DROP TABLE IF EXISTS `wxmenu`;
CREATE TABLE `wxmenu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(40) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `key` varchar(50) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `pid` int(11) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of wxmenu
-- ----------------------------
INSERT INTO `wxmenu` VALUES ('1', '点击菜单', 'click', '', '', '0', '1');
INSERT INTO `wxmenu` VALUES ('2', '图文', 'click', 'news', '', '1', '1');
INSERT INTO `wxmenu` VALUES ('3', '语音', 'click', 'voice', '', '1', '1');
INSERT INTO `wxmenu` VALUES ('4', '视频', 'click', 'video', '', '1', '1');
INSERT INTO `wxmenu` VALUES ('5', '链接菜单', 'click', '', '', '0', '1');
INSERT INTO `wxmenu` VALUES ('6', '获取地理位置', 'click', 'address', '', '1', '1');
INSERT INTO `wxmenu` VALUES ('7', '淘宝', 'view', '', 'https://m.taobao.com', '5', '1');
INSERT INTO `wxmenu` VALUES ('8', '登录', 'view', '', 'http://zhouweiyao.xarlit.cn/mobile/login/index', '5', '1');
