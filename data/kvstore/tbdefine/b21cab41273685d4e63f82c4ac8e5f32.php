<?php exit(); ?>a:3:{s:5:"value";a:8:{s:7:"columns";a:10:{s:7:"flow_id";a:9:{s:5:"label";s:6:"序号";s:4:"type";s:6:"number";s:8:"required";b:1;s:4:"pkey";b:1;s:5:"extra";s:14:"auto_increment";s:8:"editable";b:0;s:7:"in_list";b:1;s:15:"default_in_list";b:1;s:8:"realtype";s:21:"mediumint(8) unsigned";}s:9:"flow_from";a:6:{s:5:"label";s:9:"发送者";s:4:"type";a:3:{s:4:"user";s:6:"用户";s:6:"system";s:6:"系统";s:8:"internet";s:6:"站外";}s:7:"default";s:6:"system";s:8:"required";b:1;s:7:"in_list";b:1;s:8:"realtype";s:32:"enum('user','system','internet')";}s:7:"from_id";a:4:{s:4:"type";s:6:"number";s:7:"default";i:0;s:8:"editable";b:0;s:8:"realtype";s:21:"mediumint(8) unsigned";}s:7:"subject";a:8:{s:5:"label";s:12:"消息标题";s:4:"type";s:11:"varchar(50)";s:8:"required";b:1;s:7:"default";s:0:"";s:8:"editable";b:0;s:7:"in_list";b:1;s:8:"is_title";b:1;s:8:"realtype";s:11:"varchar(50)";}s:9:"flow_desc";a:7:{s:5:"label";s:12:"消息描述";s:4:"type";s:12:"varchar(100)";s:8:"required";b:1;s:7:"default";s:0:"";s:8:"editable";b:0;s:7:"in_list";b:1;s:8:"realtype";s:12:"varchar(100)";}s:4:"body";a:6:{s:5:"label";s:12:"内容本体";s:4:"type";s:4:"text";s:8:"required";b:1;s:8:"editable";b:0;s:7:"in_list";b:1;s:8:"realtype";s:4:"text";}s:7:"flow_ip";a:5:{s:4:"type";s:11:"varchar(20)";s:7:"default";s:0:"";s:8:"required";b:1;s:8:"editable";b:0;s:8:"realtype";s:11:"varchar(20)";}s:9:"send_mode";a:4:{s:4:"type";a:3:{s:6:"direct";s:6:"直送";s:9:"broadcast";s:6:"广播";s:5:"fetch";s:6:"收取";}s:7:"default";s:6:"direct";s:8:"required";b:1;s:8:"realtype";s:34:"enum('direct','broadcast','fetch')";}s:9:"flow_type";a:5:{s:4:"type";s:11:"varchar(32)";s:7:"default";s:7:"default";s:8:"required";b:1;s:8:"editable";b:0;s:8:"realtype";s:11:"varchar(32)";}s:9:"send_time";a:3:{s:4:"type";s:4:"time";s:8:"required";b:1;s:8:"realtype";s:16:"int(10) unsigned";}}s:7:"comment";s:9:"信息表";s:7:"version";s:5:"$Rev$";s:8:"idColumn";s:7:"flow_id";s:7:"in_list";a:5:{i:0;s:7:"flow_id";i:1;s:9:"flow_from";i:2;s:7:"subject";i:3;s:9:"flow_desc";i:4;s:4:"body";}s:15:"default_in_list";a:1:{i:0;s:7:"flow_id";}s:5:"pkeys";a:1:{s:7:"flow_id";s:7:"flow_id";}s:10:"textColumn";s:7:"subject";}s:3:"ttl";i:0;s:8:"dateline";i:1376705406;}