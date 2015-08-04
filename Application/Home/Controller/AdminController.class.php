<?php
namespace Home\Controller;
use Think\Controller;
class AdminController extends Controller {
    //本Controller已废弃
    
    //当前域名或IP
    private static $nowurl='http://zz.leovito.com/';
    
    
    //登录页
    public function loginview(){
      $this->display();
    }
    //首页
    public function index(){
        if(!session('admin_id')){
            $this->redirect('index');
            return;
        }
    }
    
    /** 
    * 函数login,管理员登录
    * 
    * 管理员登录
    * 
    * @param string 昵称(post.username)
    * @param string 密码(post.pass)
    * @return json
    */ 
    public function login(){
        $username=I('post.username');
        $pass=I('post.pass');
        
        $Admin=M('admin');
        $where['admin_nickname']=$username;
        $where['admin_pass']=md5($pass);
        $where['admin_isdel']=0;
        $ret=$Admin->where($where)->find();
        $arr=array();
        if($ret){
            //登录成功
            session('admin_id',$ret['admin_id']);
            $arr['state']='0';
        }
        else{
            $where=array();
            $where['admin_nickname']=$username;
            $ret=$Admin->where($where)->find();
            if($ret){
                if($ret['admin_isdel']-0!=0){
                    //已禁用
                    $arr['state']='90001';
                }
                else{
                    //密码错误
                    $arr['state']='90002';
                }
            }
            else{
                //管理员不存在
                $arr['state']='90000';
            }
        }
        $this->ajaxReturn($arr);
        
    }
    
    /** 
    * 函数lbrest,轮播图管理API(表单提交)
    * 
    * 轮播图管理API
    * 
    * @param string 操作(post.operation) edit|add|delete
    * @param string 轮播图id(post.id)  可选
    * @param string 轮播图排序(post.rank)  可选
    * @param string 轮播图图片(post.img)  可选（文件形式）
    * @param string 轮播图标题(post.title)  可选
    * @param string 轮播图链接网址(post.url)  可选
    */ 
    public function lbrest(){
        
        if(!session('admin_id')){
            $this->redirect('index');
            return;
        }
        
        
        
        $operation=I('post.operation');
        if($operation=='edit'){
            //修改
            
            $where['lb_id']=I('post.id');
            
            
            $lb=M('lb');
            $savedata['lb_time']=time();
            
            
            if($_FILES['img']){
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize   =     1024*1024*5 ;// 设置附件上传大小
                $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath  =      './Uploads/'; // 设置附件上传根目录
                // 上传单个文件 
                $info   =   $upload->uploadOne($_FILES['img']);
                if(!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                    return;
                }else{// 上传成功 获取上传文件信息
                    //echo $info['savepath'].$info['savename'];
                     $savedata['lb_img']=self::$nowurl.$info['savepath'].$info['savename'];
                }
            }
            
            if(I('post.rank')!=null&&I('post.rank')!=''){
                $savedata['lb_rank']=I('post.rank');
            }
            
            if(I('post.title')!=null&&I('post.title')!=''){
                $savedata['lb_title']=I('post.title');
            }
            
            if(I('post.url')!=null&&I('post.url')!=''){
                $savedata['lb_weburl']=I('post.url');
            }
            if($lb->where($where)->save($savedata)){
                $this->success('修改成功');
            }
            else{
                $this->error('修改失败');
            }
            
            return;
        }
        else if($operation=='add'){
            //新增
            
            $url=I('post.url');
            if($url==null||$url==''){
                $this->error('请输入链接网址');
                return;
            }
            $title=I('post.title');
            if($title==null||$title==''){
                $this->error('请输入标题');
                return;
            }
            
            
            
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     1024*1024*5 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =      './Uploads/'; // 设置附件上传根目录
            // 上传单个文件 
            $info   =   $upload->uploadOne($_FILES['img']);
            if(!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
                return;
            }else{// 上传成功 获取上传文件信息
                //echo $info['savepath'].$info['savename'];
            }
            
            $lb=M('lb');
            
            $Adddata['lb_time']=time();
            $Adddata['lb_img']=self::$nowurl.$info['savepath'].$info['savename'];
            
            if(I('post.rank'))
                $Adddata['lb_rank']=I('post.rank');
            else{
                $Adddata['lb_rank']=$lb->max('lb_rank')+1;
            }
            $Adddata['lb_title']=$title;
            $Adddata['lb_weburl']=$url;
            if($lb->add($Adddata)){
                $this->success('新增成功');
            }
            else{
                $this->error('未知错误');
            }
            return;
        }
        else if($operation=='delete'){
            //删除
            
            $lb=M('lb');
            $where['lb_id']=I('post.id');
            if($lb->where($where)->delete()){
                $this->success('移除成功');
            }
            else{
                $this->error('找不到该条轮播图');
            }
            return;
            
        }
        else{
            $this->error('非法操作');
        }
    }
    
    
    
    
    /** 
    * 函数newsrest,新闻管理API(表单提交)
    * 
    * 新闻管理API
    * 
    * @param string 操作(post.operation) edit|add|delete
    * @param string 新闻id(post.id)  可选
    * @param string 新闻标题(post.title)  可选
    * @param string 新闻正文(post.content)  可选
    * @param string 新闻图片(post.img)  可选（文件形式）
    */ 
    public function newsrest(){
        if(!session('admin_id')){
            $this->redirect('index');
            return;
        }
        $operation=I('post.operation');
        if($operation=='edit'){
            //修改
            
            $where['news_id']=I('post.id');
            
            
            $news=M('news');
            $savedata['news_time']=time();
            
            
            if($_FILES['img']){
                $upload = new \Think\Upload();// 实例化上传类
                $upload->maxSize   =     1024*1024*5 ;// 设置附件上传大小
                $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
                $upload->rootPath  =      './Uploads/'; // 设置附件上传根目录
                // 上传单个文件 
                $info   =   $upload->uploadOne($_FILES['img']);
                if(!$info) {// 上传错误提示错误信息
                    $this->error($upload->getError());
                    return;
                }else{// 上传成功 获取上传文件信息
                    //echo $info['savepath'].$info['savename'];
                     $savedata['news_image']=self::$nowurl.$info['savepath'].$info['savename'];
                }
            }
            
          
            
            if(I('post.title')!=null&&I('post.title')!=''){
                $savedata['news_title']=I('post.title');
            }
            
            if(I('post.content')!=null&&I('post.content')!=''){
                $savedata['new_content']=I('post.content');
            }
            if($news->where($where)->save($savedata)){
                $this->success('修改成功');
            }
            else{
                $this->error('修改失败');
            }
            
            return;
        }
        else if($operation=='add'){
            //新增
            
        
            $title=I('post.title');
            if($title==null||$title==''){
                $this->error('请输入标题');
                return;
            }
            
            
            
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize   =     1024*1024*5 ;// 设置附件上传大小
            $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
            $upload->rootPath  =      './Uploads/'; // 设置附件上传根目录
            // 上传单个文件 
            $info   =   $upload->uploadOne($_FILES['img']);
            if(!$info) {// 上传错误提示错误信息
                $this->error($upload->getError());
                return;
            }else{// 上传成功 获取上传文件信息
                //echo $info['savepath'].$info['savename'];
            }
            
            $news=M('news');
            
            $Adddata['news_time']=time();
            $Adddata['news_image']=self::$nowurl.$info['savepath'].$info['savename'];
            
       
            $Adddata['news_title']=$title;
            $content=I('post.content');
            $Adddata['new_content']=$content;
            if($news->add($Adddata)){
                $this->success('新增成功');
            }
            else{
                $this->error('未知错误');
            }
            return;
        }
        else if($operation=='delete'){
            //删除
            
            $news=M('news');
            $where['news_id']=I('post.id');
            if($news->where($where)->delete()){
                $this->success('移除成功');
            }
            else{
                $this->error('找不到该条新闻');
            }
            return;
            
        }
        else{
            $this->error('非法操作');
        }
    }
    
    
     
    /** 
    * 函数settingedit,设置管理API(表单提交)
    * 
    * 设置管理API
    * 
    * @param string 关键词(post.name)
    * @param string 新内容(post.value)
    * @return 
    */ 
    public function settingedit(){
        
        if(!session('admin_id')){
            $this->redirect('index');
            return;
        }
        
        $name=I('post.name');
        if(in_array($name,array('name','phone','email','weixin','weibo','qq','time','xieyi','money_smart','money_doctor','statement'))){
            $setting=M('setting');
            $where['setting_name']=$name;
            
            $value=I('post.value');
            
            $where['setting_values']=$value;
            if($setting->save($where)){
                $this->success('修改成功');
            }
            else{
                $this->error('修改失败');
            }
            
        }
        else{
            $this->error('非法操作');
        }
    }
    
    
    /** 
    * 函数selfedit,管理员修改自己信息
    * 
    * 管理员修改自己信息
    * 
    * @param string 昵称(post.username)
    * @param string 密码(post.pass)
    * @return 
    */ 
    public function selfedit(){
        if(!session('admin_id')){
            $this->redirect('index');
            return;
        }
        
        $admin=M('admin');
        $username=I('post.username');
        if($username==null||$username==''){
            $this->error('请输入昵称');
            return;
        }
        
        $pass=I('post.pass');
        $pass=md5($pass);
        $where['admin_id']=session('admin_id');
        $savedata['admin_nickname']=$username;
        $savedata['admin_pass']=$pass;
        if($admin->where($where)->save($savedata)){
            $this->success('修改成功');
        }
        else{
            $newWhere['admin_nickname']=$username;
            if($admin->where($newWhere)->find()){
                $this->error('昵称已存在，无法修改');
            }
            else{
                $this->error('新信息与旧信息一致，无法修改');
            }
        }
        
        
    }
    
    
    
    
    
    
    
}

