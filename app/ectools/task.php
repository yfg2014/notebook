<?php

/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class ectools_task  
{

    public function post_install() 
    {
        kernel::log('Initial ectools');
        kernel::single('base_initial', 'ectools')->init();
        
        kernel::log('Initial Regions');
        kernel::single('ectools_regions_mainland')->install();
    }//End Function
}//End Class
