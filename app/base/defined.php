<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */ 
$constants = array(    
        'DATA_DIR'=>ROOT_DIR.'/data',
        'OBJ_PRODUCT'=>1,
        'OBJ_ARTICLE'=>2,
        'OBJ_SHOP'=>0,
        'MIME_HTML'=>'text/html',
        'P_ENUM'=>1,
        'P_SHORT'=>2,
        'P_TEXT'=>3,
        'HOOK_BREAK_ALL'=>-1,
        'HOOK_FAILED'=>0,
        'HOOK_SUCCESS'=>1,
        'SYSTEM_ROLE_ID'=>0,
        'MSG_OK'=>true,
        'MSG_WARNING'=>E_WARNING,
        'MSG_ERROR'=>E_ERROR,
        'MNU_LINK'=>0,
        'PAGELIMIT'=>20,
        'MNU_BROWSER'=>1,
        'MNU_PRODUCT'=>2,
        'MNU_ARTICLE'=>3,
        'MNU_ART_CAT'=>4,
        'PLUGIN_BASE_URL'=>'plugins',
        'MNU_TAG'=>5,
        'TABLE_REGEX'=>'([]0-9a-z_\:\"\`\.\@\[-]*)',
        'PMT_SCHEME_PROMOTION'=>0,
        'PMT_SCHEME_COUPON'=>1,
        'APP_ROOT_PHP'=>'',
        'SET_T_STR'=>0,
        'SET_T_INT'=>1,
        'SET_T_ENUM'=>2,
        'SET_T_BOOL'=>3,
        'SAFE_MODE'=>false,
        'SET_T_TXT'=>4,
        'SET_T_FILE'=>5,
        'SET_T_DIGITS'=>6,
        'LC_MESSAGES'=>6,
        'BASE_LANG'=>'zh_CN',
        'DEFAULT_LANG'=>'zh_CN',
        'DEFAULT_INDEX'=>'',
        'ACCESSFILENAME'=>'.htaccess',
        'DEBUG_TEMPLETE'=>false,
        'WITH_REWRITE'=>false,
        'PRINTER_FONTS'=>'',
        'APP_DIR'=>ROOT_DIR.'/app',
        'PHP_SELF'=>(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME']),
        'LOG_TYPE'=>3,
        'DATABASE_OBJECT'=>'base_db_connections',
        'KVSTORE_STORAGE'=>'base_kvstore_filesystem',
        'CACHE_STORAGE'=>'base_cache_secache',
		
		'SHOP_USER_ENTERPRISE'=>'http://passport.shopex.cn/index.php',
		'SHOP_USER_ENTERPRISE_API'=>'http://passport.shopex.cn/api.php',
        'URL_APP_FETCH_INDEX'=>'http://get.ecos.shopex.cn/index.xml',
        'LICENSE_CENTER'=>'http://service.ecos.shopex.cn/openapi/api.php', //证书的正式外网地址.
        'LICENSE_CENTER_V'=>'http://service.shopex.cn',  //License授权输出图片流【tito】 请求地址
        'URL_APP_FETCH'=>'http://get.ecos.shopex.cn/%s/',
        'MATRIX_RELATION_URL' => 'http://www.matrix.ecos.shopex.cn/',
        'OPENID_URL' => 'http://openid.ecos.shopex.cn/redirect.php',
        "SHOPEX_STAT_WEBURL" => 'http://stats.shopex.cn/index.php',
        'LICENSE_CENTER_INFO'=>'http://service.shopex.cn/',
        'IMAGE_MAX_SIZE'=> 1024*1024,
        'KV_PREFIX' => 'defalut',
        'MATRIX_URL'=>'http://matrix.ecos.shopex.cn/async',
		'MATRIX_REALTIME_URL'=>'http://matrix.ecos.shopex.cn/sync',
		'MATRIX_SERVICE_URL'=>'http://matrix.ecos.shopex.cn/service',
        'MATRIX_GLOBAL' => 1,
        'MATRIX_REALTIME' => 2,
        'MATRIX_SERVICE' => 3,
        'LANG' => 'zh-cn',
        'AUTH_OPEN_URL' => 'http://auth.open.shopex.cn',
    );

foreach($constants as $k=>$v){
    if(!defined($k))define($k,$v);
}
