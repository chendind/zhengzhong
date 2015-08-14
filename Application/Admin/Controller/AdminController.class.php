<?php
namespace Admin\Controller;
use Think\Controller;
class AdminController extends Controller {
    
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
    
    
    
     /** 
    * 函数getmedicineorclasslist,得到药品或者药品类型列表
    * 
    * 得到药品或者药品类型列表
    * 
    * @param string 类型id(post.id)
    * @return 
    */ 
    public function getmedicineorclasslist(){
      $arr=array();
      if(!session('admin_id')){
        $arr['state']='2001';
      }
      else{
        $arr['state']='0';
        
        $medicineclass=M('medicineclass');
        $medicineclassWhere['medicineclass_parentid']=I('post.id');
        $medicineclassWhere['medicineclass_id']=array('neq',0);
        $classcount=$medicineclass->where($medicineclassWhere)->count();
        
        $arr['classcount']=$classcount;
        if($classcount>0){
          $arr['class']=$medicineclass->where($medicineclassWhere)->order(' CONVERT( medicineclass_classname USING gbk ) ')->select();
        }
        
        $medicine_medicineclass=M('medicine_medicineclass');
        $medicine_medicineclassWhere['medicine_medicineclass_medicineclassid']=I('post.id');
        $medicinecount=$medicine_medicineclass->where($medicine_medicineclassWhere)->count();
        
       
        if($medicinecount>0){
          $arr['medicine']=$medicine_medicineclass->where($medicine_medicineclassWhere)
            ->order(' CONVERT( medicine_productname USING gbk ) asc,medicine_id asc ')->select();
            $j=0;
          for($i=0;$i<$medicinecount;$i++){
            $tmp=$this->getMedicineName($arr['medicine'][$i]['medicine_medicineclass_medicinecode']);
            for($k=0;$k<count($tmp);$k++){
              $tt=$tmp[$k];
              $arr['medicine'][$j]['medicine_productname']=$tt['medicine_productname'];
              $arr['medicine'][$j]['medicine_approvedrugname']=$tt['medicine_approvedrugname'];
              $j++;
            }
            
            
          }
        }
        $arr['medicinecount']=$j;
        
      }
      $this->ajaxReturn($arr);
    }
    
    /** 
    * 函数getMedicineName,得到药品名
    * 
    * 得到药品名
    * 
    * @param string 药品code
    * @return array 药品名
    */ 
    private function getMedicineName($code){
        $medicine=M('medicine');
        $condition['medicine_code'] = $id;
        
        $arr= $medicine->field('medicine_productname,medicine_approvedrugname')->where($condition)->select();
        return $arr;
    }
    
    
    
    /** 
    * 函数getmedicinedetail,得到药品详情（网页）
    * 
    * 得到药品详情（网页）
    * 
    * @param string 药品id(post.id)
    * @return 
    */ 
    public function getmedicinedetail(){
      if(!session('admin_id')){
        $this->redirect('index');
        return;
      }
      else{
        $medicine=M('medicine');
        $condition['medicine_id'] = $id;
        $this->assign('detail',$medicine->where($condition)->find());
        $this->display();
      }
    }
    
     /** 
    * 函数setmedicineclass,设置药品类型(表单提交)
    * 
    * 设置药品类型
    * 
    * @param string 药品id(post.id)
    * @param string 药品类型id(post.classid)
    * @return 
    */ 
    public function addmedicineclass(){
      if(!session('admin_id')){
        $this->redirect('index');
        return;
      }
      else{
        
        $medicine_medicineclass=M('medicine_medicineclass');
        $condition['medicine_medicineclass_medicineid'] = I('post.id');
        $condition['medicine_medicineclass_medicineclassid'] = I('post.classid');
        
        if($this->checkmedicineclassExist(I('post.classid'))){
          $this->error('药品类型不存在');
        }
        else{
          if($medicine_medicineclass->add($condition)){
            $this->success('添加成功');
          }
          else{
            $this->error('添加失败');
          }
        }
        
        
      }
    }
    
    
    
     /** 
    * 函数checkmedicineclassExist,检查药品类型是否存在
    * 
    * 检查药品类型是否存在
    * 
    * @param string 药品类型id
    * @return BOOL
    */ 
    private function checkmedicineclassExist($id){
      $medicineclass=M('medicineclass');
      $where['medicineclass_id']=$id;
      if($medicineclass->where($where)->find()){
        return true;
      }
      else{
        return false;
      }
    }
    
    /** 
    * 函数listtaboo,列出禁忌症
    * 
    * 列出禁忌症
    * 
    * @return 
    */
    public function listtaboo(){
      if(!session('admin_id')){
        $this->redirect('index');
        return;
      }
      else{
        
        $taboo=M('taboo');
        
        $this->assign('list',$taboo->select());
        $this->display();
      }
    }
    
     /** 
    * 函数addtaboo,添加禁忌症
    * 
    * 添加禁忌症
    * 
    * @param string 禁忌症名
    * @return BOOL
    */
    private function addtaboo($name,$explain){
      $taboo=M('taboo');
      $where['taboo_name']=$name;
      $where['taboo_explain']=$explain;
      if($this->checktabooExist($name)){
        if($taboo->add($where)){
          return true;
        }
        else{
          return false;
        }
      }
      else{
        return false;
      }
      
    }
     
      /** 
    * 函数checktabooExist,检查禁忌症是否存在
    * 
    * 检查禁忌症是否存在
    * 
    * @param string 禁忌症名
    * @return BOOL
    */ 
    private function checktabooExist($name){
      $taboo=M('taboo');
      $where['taboo_name']=$name;
      if($taboo->where($where)->find()){
        return true;
      }
      else{
        return false;
      }
    }
    
    
    
    
    /** 
    * 函数addtherapy,添加药物适应症
    * 
    * 添加药物适应症
    * 
    * @param string 药物code(post.code)
    * @param string 疾病icd(post.icd)
    * @return
    */ 
    public function addtherapy(){
       if(!session('admin_id')){
        $this->redirect('index');
        return;
      }
      else{
        
        $therapy=M('therapy');
        $condition['therapy_medicinecode'] = I('post.code');
        $condition['therapy_icdcode'] = I('post.icd');
        
        if($therapy->where($condition)->find()){
          $this->error('已添加');
        }
        else if($therapy->add($condition)){
          $this->success('添加成功');
        }
        else{
          $this->error('添加失败');
        }
      }
    }
    
    
   /** 
    * 函数removetherapy,移除药物适应症
    * 
    * 移除药物适应症
    * 
    * @param string 药物code(post.code)
    * @param string 疾病icd(post.icd)
    * @return
    */ 
    public function removetherapy(){
       if(!session('admin_id')){
        $this->redirect('index');
        return;
      }
      else{
        $therapy=M('therapy');
        $condition['therapy_medicinecode'] = I('post.code');
        $condition['therapy_icdcode'] = I('post.icd');
        
        if($therapy->where($condition)->find()){
          if($therapy->where($condition)->delete()){
            $this->success('移除成功');
          }
          else{
            $this->error('移除失败');
          }
        }
        else{
          $this->error('未找到符合要求的记录');
        }
      }
    }
    
    
    
    
    
    
    
    
    
}

