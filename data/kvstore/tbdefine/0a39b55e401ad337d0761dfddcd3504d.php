<?php exit(); ?>a:3:{s:5:"value";a:7:{s:7:"columns";a:7:{s:2:"id";a:5:{s:4:"type";s:12:"int unsigned";s:8:"required";b:1;s:4:"pkey";b:1;s:5:"extra";s:14:"auto_increment";s:8:"realtype";s:16:"int(10) unsigned";}s:8:"username";a:10:{s:4:"type";s:11:"varchar(50)";s:8:"required";b:1;s:5:"label";s:9:"操作员";s:10:"searchtype";s:3:"has";s:10:"filtertype";s:3:"yes";s:13:"filterdefault";b:1;s:5:"width";i:70;s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:11:"varchar(50)";}s:8:"realname";a:10:{s:4:"type";s:11:"varchar(50)";s:8:"required";b:1;s:5:"label";s:6:"姓名";s:10:"searchtype";s:3:"has";s:10:"filtertype";s:3:"yes";s:13:"filterdefault";b:1;s:5:"width";i:70;s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:11:"varchar(50)";}s:8:"dateline";a:9:{s:4:"type";s:4:"time";s:8:"required";b:1;s:5:"label";s:12:"操作时间";s:10:"filtertype";s:3:"yes";s:13:"filterdefault";b:1;s:5:"width";i:120;s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:16:"int(10) unsigned";}s:12:"operate_type";a:8:{s:4:"type";a:4:{s:6:"normal";s:6:"普通";s:7:"members";s:6:"会员";s:5:"goods";s:6:"商品";s:6:"orders";s:6:"订单";}s:7:"default";s:6:"normal";s:5:"label";s:12:"操作类型";s:5:"width";i:110;s:10:"filtertype";s:3:"yes";s:13:"filterdefault";b:1;s:7:"in_list";b:1;s:8:"realtype";s:41:"enum('normal','members','goods','orders')";}s:11:"operate_key";a:7:{s:4:"type";s:12:"varchar(255)";s:5:"label";s:12:"主关键字";s:5:"width";i:200;s:10:"searchtype";s:3:"has";s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:12:"varchar(255)";}s:4:"memo";a:6:{s:4:"type";s:8:"longtext";s:5:"label";s:12:"操作内容";s:5:"width";i:200;s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:8:"longtext";}}s:5:"index";a:3:{s:12:"ind_dateline";a:1:{s:7:"columns";a:1:{i:0;s:8:"dateline";}}s:12:"ind_username";a:1:{s:7:"columns";a:1:{i:0;s:8:"username";}}s:15:"ind_operate_key";a:1:{s:7:"columns";a:1:{i:0;s:11:"operate_key";}}}s:8:"idColumn";s:2:"id";s:5:"pkeys";a:1:{s:2:"id";s:2:"id";}s:7:"in_list";a:6:{i:0;s:8:"username";i:1;s:8:"realname";i:2;s:8:"dateline";i:3;s:12:"operate_type";i:4;s:11:"operate_key";i:5;s:4:"memo";}s:15:"default_in_list";a:5:{i:0;s:8:"username";i:1;s:8:"realname";i:2;s:8:"dateline";i:3;s:11:"operate_key";i:4;s:4:"memo";}s:10:"textColumn";s:8:"username";}s:3:"ttl";i:0;s:8:"dateline";i:1376705619;}