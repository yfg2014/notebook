<?php
class notebook_ctl_site_default extends site_controller{

    public function index(){
        $this->pagedata['items'] = $this->app->model('item')->getList('*');
         //通过留言者的email获得头像图片
        //查找服务notebook_addon,完成通过email获得头像的操作和点一下警报就弹的操作
        foreach(kernel::servicelist('notebook_addon') as $object){
            foreach($this->pagedata['items'] as $k=>$item){
                $this->pagedata['items'][$k]['addon'][] = $object->get_output($item);
            }
        }
        
        $this->page('default.html');
    }

    public function addnew(){
        //begin开启事务，设置错误或成功时返回的URL地址，设置用户自定义的错误处理函数_errorHandler
        //和 end 配合使用
        $this->begin(array("ctl" => "site_default", "act" => "index"));
        //接受提交过来的参数
        $data = array(
                'item_subject'=>$_POST['subject'],
                'item_content'=>$_POST['content'],
                'item_email'=>$_POST['email'],
                'item_posttime'=>time(),
            );
        //将数据插入表中
        $result = $this->app->model('item')->insert($data);
        //end结束事务
        $this->end($result);
    }

}
