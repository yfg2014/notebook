<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */


class b2c_service_desktop_indexseo
{

    public function title() 
    {
        return app::get('b2c')->getConf('system.shopname');
    }//End Function

}//End Class