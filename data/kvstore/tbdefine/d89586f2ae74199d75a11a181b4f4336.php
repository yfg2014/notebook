<?php exit(); ?>a:3:{s:5:"value";a:7:{s:7:"columns";a:8:{s:7:"item_id";a:6:{s:4:"type";s:12:"int unsigned";s:8:"required";b:1;s:4:"pkey";b:1;s:5:"extra";s:14:"auto_increment";s:8:"editable";b:0;s:8:"realtype";s:16:"int(10) unsigned";}s:9:"reship_id";a:5:{s:4:"type";s:12:"table:reship";s:8:"required";b:1;s:7:"default";i:0;s:8:"editable";b:0;s:8:"realtype";s:19:"bigint(20) unsigned";}s:13:"order_item_id";a:5:{s:4:"type";s:17:"table:order_items";s:8:"required";b:0;s:7:"default";i:0;s:8:"editable";b:0;s:8:"realtype";s:21:"mediumint(8) unsigned";}s:9:"item_type";a:5:{s:4:"type";a:4:{s:5:"goods";s:6:"商品";s:4:"gift";s:6:"赠品";s:3:"pkg";s:12:"捆绑商品";s:7:"adjunct";s:12:"配件商品";}s:7:"default";s:5:"goods";s:8:"required";b:1;s:8:"editable";b:0;s:8:"realtype";s:36:"enum('goods','gift','pkg','adjunct')";}s:10:"product_id";a:5:{s:4:"type";s:15:"bigint unsigned";s:8:"required";b:1;s:7:"default";i:0;s:8:"editable";b:0;s:8:"realtype";s:19:"bigint(20) unsigned";}s:10:"product_bn";a:4:{s:4:"type";s:11:"varchar(30)";s:8:"editable";b:0;s:8:"is_title";b:1;s:8:"realtype";s:11:"varchar(30)";}s:12:"product_name";a:3:{s:4:"type";s:12:"varchar(200)";s:8:"editable";b:0;s:8:"realtype";s:12:"varchar(200)";}s:6:"number";a:5:{s:4:"type";s:5:"float";s:8:"required";b:1;s:7:"default";i:0;s:8:"editable";b:0;s:8:"realtype";s:5:"float";}}s:7:"comment";s:25:"发货/退货单明细表";s:7:"version";s:13:"$Rev: 40654 $";s:8:"idColumn";s:7:"item_id";s:5:"pkeys";a:1:{s:7:"item_id";s:7:"item_id";}s:5:"index";a:2:{s:15:"idx_c_reship_id";a:1:{s:7:"columns";a:1:{i:0;s:9:"reship_id";}}s:19:"idx_c_order_item_id";a:1:{s:7:"columns";a:1:{i:0;s:13:"order_item_id";}}}s:10:"textColumn";s:10:"product_bn";}s:3:"ttl";i:0;s:8:"dateline";i:1376705509;}