<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class site_task 
{
    function post_install() 
    {
        kernel::log('Initial themes');
        kernel::single('site_theme_base')->set_last_modify();
        kernel::single('site_theme_install')->initthemes();
        $themes = kernel::single('site_theme_install')->check_install();
    }//End Function
}//End Class
