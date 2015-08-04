<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------

namespace Home\Controller;

use Think\Controller;
use Com\Wechat;

class ChatController extends Controller{
    
    private static $client_id='YXA6BbdmUDW7EeWZ7Yky5gX_BA';
    private static $client_secret='YXA69h0j5EIKT2_TxcvJj2O_u3POd3w';
    private static $org_name='jieyao';
    private static $app_name='jieyao';
    
    
    /** 
    * 函数listcustomerservice,列出客服列表
    * 
    * 列出客服列表
    * 
    * @return json
    */ 
    public function listcustomerservice(){
	$arr=array();
	$customservice=M('customservice');
	$where['customservice_department']=array('in',array('诊后调节','投诉与建议'));
	$arr['customservice']=$customservice->where($where)
	    ->field('customservice_id,customservice_nickname,customservice_state,customservice_department,customservice_image')
	    ->order('customservice_state desc,rand() asc')
	    ->select();
	$this->ajaxReturn($arr);
    }
    
    /** 
    * 函数 checkOrderPaied,订单是否已支付
    * 
    * 订单是否已支付
    * @param string 订单号
    * @return BOOL
    */ 
    private function checkOrderPaied($oid){
        $Order=M('order');
        $OrderWhere['order_id']=$oid;
        $OrderWhere['order_uid']=session('id');
        $OrderWhere['order_state']='1';
        $OrderWhere['order_type']='1';
        if($Order->where($OrderWhere)->find()){
            return true;
        }
        else{
            return false;
        }
    }
    
    /** 
    * 函数 getcustomerservice,得到科室客服，创建会话
    * 
    * 得到科室客服，创建会话
    * @param string 订单号(post.oid)
    * @return 
    */ 
    public function getdepartmentcustomerservice(){
	$arr=array();
	if(!session('id')){
	    $arr['state']='2001';
	}
	else{
	    $customservice_conversation=M('customservice_conversation');
	    $customservice_conversationWhere['customservice_conversation_oid']=I('post.oid');
	    $customservice_conversationWhere['customservice_conversation_uid']=session('id');
	    $ret=$customservice_conversation->where($customservice_conversationWhere)->find();
	    if($ret){
		if($ret['customservice_conversation_state']=='1'){
		    //已关闭
		    $arr['state']='11000';
		}
		else{
		    //活动中
		     $arr['state']='0';
		     $arr['groupid']=$ret['customservice_conversation_id']."";
		}
	    }
	    else{
		
		$customservice=M('customservice');
		$where['customservice_department']=I('post.department');
		$list=$customservice->where($where)->order('customservice_state desc,rand() asc')->limit('0,1')->select();
		
		
		
		require('easemob.class.php');
		
		$options=array();
		$options['client_id']=self::$client_id;
		$options['client_secret']=self::$client_secret;
		$options['org_name']=self::$org_name;
		$options['app_name']=self::$app_name;
		
		$easemob=new \easemob($options);
		
		$now=time();
		
		$groupoption['groupname']=$now.'';
		$groupoption['desc']=$now.'';
		$groupoption['public']=false;
		//$groupoption['approval']=true;
		$groupoption['owner']=session('id');
		$groupoption['members']=array('cs'.$list[0]['customservice_id']);
		
		$result=$easemob->createGroups($groupoption);
		
		$result=json_decode($result,true);
		$groupid=$result['data']['groupid'];
		
		if($groupid){
		     $customservice_conversationWhere['customservice_conversation_id']=$groupid;
		     $customservice_conversationWhere['customservice_conversation_csid']=$list[0]['customservice_id'];
		     $customservice_conversationWhere['customservice_conversation_starttime']=$now;
		     $customservice_conversationWhere['customservice_conversation_endtime']=$now+72*3600;
		     $customservice_conversationWhere['customservice_conversation_star']=0;
		     $customservice_conversationWhere['customservice_conversation_cscomment']='';
		     $customservice_conversationWhere['customservice_conversation_state']=0;
		     $customservice_conversation->add($customservice_conversationWhere);
		     $arr['state']='0';
		     $arr['groupid']=$groupid;
		}
		else{
		    $arr['state']='11001';
		}
		
		
	    }
	}
	$this->ajaxReturn($arr);
    }
    
     
    /** 
    * 函数 deleteGroup,移除群组
    * 移除群组
    * @param string 群组号
    * @return 
    */ 
    private function deleteGroup($groupId){
	require('easemob.class.php');
	$options=array();
	$options['client_id']=self::$client_id;
	$options['client_secret']=self::$client_secret;
	$options['org_name']=self::$org_name;
	$options['app_name']=self::$app_name;
		
	$easemob=new \easemob($options);
	$easemob->deleteGroups($groupId);
	
    }
     /** 
    * 函数 endConversation,结束会话
    * 结束会话
    * @param string 群组号（post.id）
    * @return 
    */ 
    public function endConversation(){
	if(!session('id')&&!session('csid')){
	    $arr['state']='2001';
	}
	else{
	    $customservice_conversation=M('customservice_conversation');
	    $conversationid=I('post.id');
	    $where['customservice_conversation_id']=$conversationid;
	    $where['customservice_conversation_state']=0;
	    $savedata['customservice_conversation_state']=1;
	    $savedata['customservice_conversation_endtime']=time();
	    if(session('id')){
		//用户结束
		$where['customservice_conversation_uid']=session('id');
		if($customservice_conversation->where($where)->save($savedata)){
		    
		    $this->deleteGroup($conversationid);
		    
		    $arr['state']='0';
		}
		else{
		    $arr['state']='11000';
		}
	    }
	    else{
		//客服结束
		$where['customservice_conversation_csid']=session('csid');
		if($customservice_conversation->where($where)->save($savedata)){
		    
		    $this->deleteGroup($conversationid);
		    
		    $arr['state']='0';
		}
		else{
		    $arr['state']='11000';
		}
	    }
	}
	$this->ajaxReturn($arr);
    }
    
    
     /** 
    * 函数 listconversations,列出会话列表
    * 列出会话列表
    * @param string 群组号（post.id）
    * @return 
    */ 
    public function listconversations(){
	if(!session('id')){
	    $arr['state']='2001';
	}
	else{
	    $customservice_conversation=M('customservice_conversation');
	    $where['customservice_conversation_uid']=session('id');
	    $where['customservice_conversation_state']=0;
	    $arr['count']=$customservice_conversation->where($where)->count();
	    $arr['list']=$customservice_conversation->where($where)->order('customservice_conversation_id desc')->select();
	    if($arr['list']){
		for($i=0;$i<count($arr['list']);$i++){
		    $arr['list'][$i]['csdetail']=$this->getCSdetail($arr['list'][$i]['customservice_conversation_csid']);
		}
	    }
	    
	    $arr['state']='0';
	}
	$this->ajaxReturn($arr);
    }
    
    /** 
    * 函数 getCSdetail,得到客服信息
    * 列出会话列表
    * @param string 客服编号
    * @return 
    */
    private function getCSdetail($customservice_conversation_csid){
	$customservice=M('customservice');
	$where['customservice_id']=$customservice_conversation_csid;
	return $customservice->where($where)->find();
    }
    
    
    
     /** 
    * 函数 rateconversation,评价
    * 评价
    * @param string 会话编号(post.cid)
    * @param string 专业程度评分(post.star)
    * @param string 耐心认真评分(post.star2)
    * @param string 是否解决了问题(post.solved)
    * @return 
    */
    public function rateconversation(){
	if(!session('id')){
	    $arr['state']='2001';
	}
	else{
	    
	    $star=I('post.star');
	    $star2=I('post.star2');
	    $solved=I('post.solved');
	    if($star<0||$star>5||$star2<0||$star2>5||$solved<0||$solved>1){
		$arr['state']='10001';
	    }
	    else{
	    
	    
		$customservice_conversation=M('customservice_conversation');
		$where['customservice_conversation_uid']=session('id');
	    
		$where['customservice_conversation_id']=I('post.cid');
		$r=$customservice_conversation->where($where)->find();
		if($r){
		    //找到会话
		    if($r['customservice_conversation_state']=='0'){
			//未结束
			$arr['state']='80001';//会话未结束
		    }
		    else if($r['customservice_conversation_state']=='1'){
			$where['customservice_conversation_state']=1;
			$savedata['customservice_conversation_state']='2';
			$savedata['customservice_conversation_star']=$star;
			$savedata['customservice_conversation_star2']=$star2;
			$savedata['customservice_conversation_solved']=$solved;
			if($customservice_conversation->where($where)->save($savedata)){
			    $arr['state']='0';
			}
			else{
			    $arr['state']='80002';//会话已评价
			}
		    }
		else{
		    $arr['state']='80002';//会话已评价
		}
		}
		else{
		    $arr['state']='80000';//会话不存在
		}
	    }
	    
	    
	}
	$this->ajaxReturn($arr);
    }
    
    
    
    
}
