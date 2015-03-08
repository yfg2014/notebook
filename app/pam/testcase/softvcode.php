<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

require dirname(__FILE__)."/../lib/softvcode.php";
$vcode_model = new pam_softvcode;
$vcode_model->init(4);
$vcode_model->output();
