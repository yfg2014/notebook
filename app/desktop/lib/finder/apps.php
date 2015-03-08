<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 
class desktop_finder_apps{

    var $addon_cols='local_ver,remote_ver,status,app_id';
    var $column_tools='操作';
    var $column_tools_width='150';
    function column_tools($row){
        $local_ver = $row[$this->col_prefix.'local_ver'];
        $remote_ver = $row[$this->col_prefix.'remote_ver'];
        $status = $row[$this->col_prefix.'status'];
        $app_id = $row[$this->col_prefix.'app_id'];

        $update_install_btn = '<button type="button" class="btn" onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'install\']).run(\''.$app_id.'\')});"><span><span class="c-green" >'.app::get('desktop')->_('升级并安装').'</span></span></button>';
        $download_install_btn = '<button  type="button" class="btn" onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'install\']).run(\''.$app_id.'\')});"><span><span class="c-green">'.app::get('desktop')->_('下载并安装').'</span></span></button>';
        $install_btn = '<button class="btn" type="button" onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'install\']).run(\''.$app_id.'\')});"><span><span class="c-green">'.app::get('desktop')->_('安装').'</span></span></button>';
        
        $depends_apps = app::get('base')->model('apps')->check_deploy_depends();
        $protetced_app = array_keys($depends_apps);
        
        if(in_array($app_id,$protetced_app)){
            $pause_btn = '<button type="button" class="btn disabled"><span><span class="c-disabled">'.app::get('desktop')->_('停用').'</span></span></button>';
            $active_btn = '<button type="button" class="btn disabled"><span><span class="c-disabled">'.app::get('desktop')->_('启用').'</span></span></button>';
            $uninstall_btn = '<!--<span class="c-gray">'.app::get('desktop')->_('已安装').'</span><span>&nbsp;</span>--><button type="button" class="btn disabled"><span><span class="c-disabled">'.app::get('desktop')->_('卸载').'</span></span></button>';
        }else{
            $pause_btn = '<button onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'pause\']).run(\''.$app_id.'\')});" class="btn" type="button"><span><span class="c-blue">'.app::get('desktop')->_('停用').'</span></span></button>';
            $active_btn = '<button onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'active\']).run(\''.$app_id.'\')});" class="btn" type="button"><span><span class="c-blue">'.app::get('desktop')->_('启用').'</span></span></button>';
            $uninstall_btn = '<!--<span class="c-gray">'.app::get('desktop')->_('已安装').'</span><span>&nbsp;</span>--><button onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'uninstall\']).run(\''.$app_id.'\')});" class="btn" type="button"><span><span class="c-blue">'.app::get('desktop')->_('卸载').'</span></span></button>';    
        }
        
        $update_btn = '<button type="button" class="btn" onclick="Ex_Loader(\'cmdrunner\',function(){appmgr([\'download\',\'update\']).run(\''.$app_id.'\')});"><span><span class="c-orange">'.app::get('desktop')->_('升级').'</span></span></button>';

        $output = '';
        switch($status){
            case 'uninstalled':
            if(!$local_ver){
                $output .= $download_install_btn;
            }elseif(version_compare($remote_ver,$local_ver,'>')){
                $output .= $update_install_btn;  
            }else{
                $output .= $install_btn;     
            }
            break;

            case 'installed':
            $output .= $start_btn;
            $output .= $uninstall_btn;
            if(version_compare($remote_ver,$local_ver,'>')){
                $output .= $update_btn;
            }
            break;

            case 'active':
            $output .= $pause_btn;
            $output .= $uninstall_btn;
            if(version_compare($remote_ver,$local_ver,'>')){
                $output .= $update_btn;
            }
            break;

            case 'paused':
            $output .= $active_btn;
            break;
        }
        return $output;
    }

    var $detail_info='info';
    function detail_info($id){
        $render = app::get('desktop')->render();
        $render->pagedata['appinfo'] = app::get($id)->define();
        $render->pagedata['docs'] = app::get($id)->docs();

        return $render->fetch('appmgr/info.html');
    }

}
