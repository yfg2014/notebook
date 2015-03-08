<?php
/**
 * ShopEx licence
 *
 * @copyright  Copyright (c) 2005-2010 ShopEx Technologies Inc. (http://www.shopex.cn)
 * @license  http://ecos.shopex.cn/ ShopEx License
 */
 

class b2c_ctl_admin_code extends base_controller
{
    public function __construct($app)
    {
        parent::__construct($app);
        header("cache-control: no-store, no-cache, must-revalidate");
    }
    
    //激活码校验
	public function codecheck()
	{
		//if ($_POST['auth_code'] && preg_match("/^\d{19}$/", substr($_POST['auth_code'], 1)))
		if ($_POST['auth_code'])
        {
			$code = kernel::single('b2c_cert_certcheck');
			$result = $code->check_code($_POST['auth_code']);
            if ($result['res'] == 'succ' && $result)
            {
				$activation_arr = $_POST['auth_code'];
				app::get('b2c')->setConf('activation_code', $activation_arr);
				header('Location:' .kernel::router()->app->base_url(1));
				exit;
            }
            else
            {
                switch ($result['msg'])
                {
					case 'key_false_ac':
                        $auth_error_msg = '激活码类型不对!';break;
                    case 'key_false_times':
                        $auth_error_msg = '失败：您已经连续6次提交失败，为了您的网店安全，请3小时后再次尝试|';break;
                    case 'key_false_key':
                        $auth_error_msg = '无效的激活码，请您重新输入激活码以便正常使用。';break;
                    case 'key_false_actived':
                        $auth_error_msg = '您的激活码已经失效，请您重新输入激活码以便正常使用。';break;
                    case 'key_false_oem':
                        $auth_error_msg = '您的网店License与输入的激活码类型不一，请联系激活码销售商!';break;
                    case 'key_false_type_1':
                        $auth_error_msg = '您的网店License与输入的激活码类型不一，请联系激活码销售商!';break;
                    case 'key_false_type_2':
                        $auth_error_msg = '您的网店License与输入的激活码类型不一，请联系激活码销售商!';break;
					case 'certificate_id_is_false':
                        $auth_error_msg = '您的网店License与输入的激活码类型不一，请联系激活码销售商!';break;
                }

                die($this->error_view($auth_error_msg));
            }

            header("Location: index.php");
            exit();
        }
	}
	
	function error_view($auth_error_msg)
    {
		$render = app::get('b2c')->render();
		$render->pagedata['res_url'] = app::get('desktop')->res_url;
		$render->pagedata['auth_error_msg'] = $auth_error_msg;
		echo $render->display('admin/activa_code.html');
		exit;
	}
	
}