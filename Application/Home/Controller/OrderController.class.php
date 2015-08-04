<?php
namespace Home\Controller;
use Think\Controller;
class OrderController extends Controller
{
    //函数createOrder创建订单 请求参数：type
    public function createOrder()
    {
        $type=I('post.type');//订单的类别：智能分析、智能分析后聊天和直接聊天
        
        if(session('id'))
        {
            $uid=session('id');
            if($this->check_islegal($type))
            {
                $price=$this->orderPrice($type);
                $Order=M('order');
                $condition['order_uid']=$uid;
                $condition['order_requesttime']=time();
                $condition['order_state']='0';
                $condition['order_type']=$type;
                $condition['order_price']=$price;
                $a=$Order->add($condition);
                if($a)
                {
                    $arr['state']='0';//创建订单成功
                    $arr['oid']=$a;
                    
                }
                else
                {
                    $arr['state']='30022';//创建订单失败
                }
                
            }
            else
            {
                $arr['state']='30021';//种类格式有问题
            }
        }
        else
        {
            $arr['state']='30020';//未登录
        }
        echo json_encode($arr);
    }
    public function createOrder1()
    {
        $Order=M('order');
                $condition['order_uid']='1000005';
                $condition['order_requesttime']=time();
                $condition['order_state']='0';
                $condition['order_type']='2';
                $condition['order_price']='0';
                $a=$Order->add($condition);
                if($a)
                {
                    $arr['state']='0';//创建订单成功
                    $arr['oid']=$a;
                    
                }
                else
                {
                    $arr['state']='30022';//创建订单失败
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
            $where['order_uid']=$uid;
            
            $arr['details']=$Order->where($where)->field("order_id,order_requesttime,order_receivetime,order_price,order_payway,order_type")->order('order_requesttime desc')->limit($from,$num)->select();
            $arr['state']='0';//成功
        }
        else
        {
            $arr['state']='30016';//未登录
        }
        echo json_encode($arr);
    }
    
    //函数getHistoryOrderDetail() 请求参数；orderid
    public function getHistoryOrderDetail()
    {
        if(session('id'))
        {
            $orderid=I('post.orderid');
            $Order=M('order');
            $Autoanalyzerecord=M('autoanalyzerecord');
            $where['order_id']=$orderid;
            $arr['orderDetail']=$Order->where($where)->field("order_id,order_requesttime,order_receivetime,order_price,order_payway,order_type")->find();
            
            $autoWhere['autoanalyzerecord_orderid']=$orderid;
            $arr['autoanalyzeDetail']=$Autoanalyzerecord->where($autoWhere)->find();
            $arr['state']='0';//成功
        }
        else
        {
            $arr['state']='30016';//未登录
        }
        echo json_encode($arr);
    }
    
    private function check_islegal($string){
    
       return true;
    }
    private function orderPrice($type)
    {
        $Setting=M('setting');
        switch($type)
        {
            case '0':
                $name='money_smart';
                break;
            case '1':
                $name='money_doctor';
                break;
            default:
                break;
        }
        $where['setting_name']=$name;
        $price=$Setting->where($where)->getField('setting_values');
        if($type=='2')
        {
            $price='5';
        }
        return $price;
    }
    
    
    
    
    
    
    
    
    
    
    /** 
    * 函数 freepay,分享免费
    * 分享免费
    * @param string 订单号(post.oid)
    * @return 
    */
    public function freepay(){
        if(!session('id')){
	    $arr['state']='2001';
	}
	else{
	    $oid=I('post.oid');
	    
            $User=M('user');
            $order=M('order');
            
            $order->startTrans();
            
            $userWhere['user_id']=session('id');
            $userWhere['user_searchcount']=array('EGT',1);
            
            if($User->where($userWhere)->setDec('user_searchcount')){
                //正常
                $orderWhere['order_id']=$oid;
                $orderWhere['order_uid']=session('id');
                $orderWhere['order_state']=0;
                $orderWhere['order_type']=0;
                $orderSaveData['order_receivetime']=time();
                $orderSaveData['order_state']=1;
                $orderSaveData['order_payway']=1;
                if($order->where($orderWhere)->save($orderSaveData)){
                    $order->commit();
                    $arr['state']='0';
                }
                else{
                    $arr['state']='30023';
                    $order->rollback();
                }
            }
            else{
                //免费次数没了
                $arr['state']='30023';
                $order->rollback();
            }
            
	    
	    
	}
	$this->ajaxReturn($arr);
    }
    
    
}
?>