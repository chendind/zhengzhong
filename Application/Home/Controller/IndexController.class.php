<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {


    //
    //wap端首页

    public  function index(){
     
     //获取轮播图
    	$LB=M('lb');
          $lb=$LB->order('lb_rank')->select();
          $this->assign("lb",$lb);
         
      //获取新闻
      
      $News=M("News");
      $news=$News->order("news_time desc")->limit(0,10)->select();
      $this->assign("news",$news);


       $this->display();
    }
    
    //函数personCenter()个人中心界面
    public function personCenter()
    {
        if(session('id'))
        {
            $User=M('user');
            $Letter=M('letter');
            $uid=session('id');
            $phone=$User->where("user_id=$uid")->field('user_phone')->find();
            $condition['letter_uid']=$uid;
            $condition['letter_state']='0';
            $count=$Letter->where($condition)->count();
            $this->assign('phone',$phone);
            $this->assign('lettercount',$count);
            $this->assign('islogin','1');//已登录
        }
        else
        {
            $this->assign('islogin','0');//未登录
        }
        $this->display();
    }
    
    //历史订单界面
    public function historyOrder()
    {
        if(session('id'))
        {
            $uid=session('id');
            $Order=M('order');
            $details=$Order->where("order_uid=$uid")->field("order_id,order_requesttime,order_receivetime,order_price,order_payway,order_type")->order('order_requesttime desc')->limit(0,10)->select();
            $this->assign('orderDetail',$details);
            $this->assign('islogin','1');//已登录
        }
        else
        {
            $this->assign('islogin','0');//未登录
        }
        $this->display();
    }
    
    //历史订单详情界面
    public function historyOrderDetail($orderid)
    {
        if(session('id'))
        {
            $Order=M('order');
            $Autoanalyzerecord=M('autoanalyzerecord');
            $orderDetail=$Order->where("order_id=$orderid")->field("order_id,order_requesttime,order_receivetime,order_price,order_payway,order_type")->find();
            $autoanalyzeDetail=$Autoanalyzerecord->where("autoanalyzerecord_orderid=$orderid")->field("autoanalyzerecord_time,autoanalyzerecord_relationship,autoanalyzerecord_medicine,autoanalyzerecord_inspect,autoanalyzerecord_zhenduan,autoanalyzerecord_notfound,autoanalyzerecord_taboo,autoanalyzerecord_result")->find();
            $this->assign('orderDetail',$orderDetail);
            $this->assign('autoanalyzeDetail',$autoanalyzeDetail);
            $this->assign('islogin','1');//已登录
        }
        else
        {
            $this->assign('islogin','0');
        }
        $this->display();
    }
    
    //联系我们界面
    public function contactUs(){
        $tablesetting=M('setting');
        $where['setting_id']=array(array('egt','1'),array('elt','7'),'AND');
        $contact=$tablesetting->where($where)->select();
        $this->assign('contact',$contact);
        $this->display();
    }
    
    //私信界面
    public function letter()
    {
        if(session('id'))
        {
            $tableletter=M('letter');
            $where['letter_uid']=session('id');
            
            $Letter=$tableletter->field('letter_id,letter_title,letter_content,letter_time,letter_state')->where($where)->order('letter_time desc')->limit(0,10)->select();
            $this->assign('Letter',$Letter);
            $this->assign('islogin','1');//已登录
        }
        else
        {
            $this->assign('islogin','0');//未登录
        }
        $this->display();
    }
    
    //咨询界面
    public function consultation()
    {
        
    }
    
    
    
    public function getletter(){
       if(!session('id')){
            $arr['state']="2001";//登录过期
        }
       else{
            $tableletter=M('letter');
            $start=I('post.start');
            $show=I('post.show');
            $where['letter_uid']=session('id');
            
            $arr['letter']=$tableletter->field('letter_id,letter_title,letter_content,letter_time,letter_state')->where($where)->order('letter_time')->limit($start,$show)->select();
            $arr['count']=$tableletter->where($where)->count();
            $arr['state']="0";//成功
            
        }
        echo json_encode($arr);
    }
    //获取新闻api
    public function getNews()
    {
        $News=M("News");
        $from=I("post.from");
        $num=I("post.num");
        $detail=$News->order("news_time desc")->limit($from,$num)->select();
        if($detail)
        {
            $arr["detail"]=$detail;
            $arr["state"]="1";//success
        }
        else
        {
            $arr["state"]="0";//network error
        }
        echo json_encode($arr);
    }
    //函数getHistoryOrder() 无请求参数
    public function getHistoryOrder()
    {
        if(session('id'))
        {
            $uid=session('id');
            $Order=M('order');
            $from=I("post.from");
            $num=I("post.num");
            $arr['details']=$Order->where("order_uid=$uid")->field("order_id,order_requesttime,order_receivetime,order_price,order_payway,order_type")->order('order_requesttime desc')->limit($from,$num)->select();
            $arr['state']='0';//成功
        }
        else
        {
            $arr['state']='30016';//未登录
        }
        echo json_encode($arr);
    }
    
}