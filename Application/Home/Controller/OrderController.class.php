<?php
namespace Home\Controller;
use Think\Controller;
class OrderController extends Controller
{
    
    private static $app_id='app_5ib1G080uT0GGSKK';
    
    //函数createOrder创建订单 请求参数：type
    public function createOrder()
    {
        $type=I('post.type');//订单的类别：智能分析、智能分析后聊天和直接聊天
        $link=I('post.link');
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
                
                if($type=='1'){
                    
                    
                    $Linkwhere['order_uid']=$uid;
                    $Linkwhere['order_type']=0;
                    $Linkwhere['order_state']=1;
                    if($Order->where($Linkwhere)->find()){
                        $condition['order_link']=$link;
                    }
                    else{
                        $arr['state']='30025';
                        echo json_encode($arr);
                        return;
                    }
                }
                
                $a=$Order->add($condition);
                if($a)
                {
                    $arr['state']='0';//创建订单成功
                    $arr['oid']=$a;
                    
                    $User=M('user');
                    $uwhere['user_id']=$uid;
                    $arr['left']=$User->where($uwhere)->getField('user_searchcount');
                    
                    require_once('pingpp/vendor/autoload.php');
                    \Pingpp\Pingpp::setApiKey('sk_test_K8abfHzTCmHK1OO4yLG8ivb9');
                    $ch=\Pingpp\Charge::create(array(
                        'order_no'  => $a.'',
                        'amount'    => $price*100,
                        'app'       => array('id' => self::$app_id),
                        'channel'   => 'alipay',
                        'currency'  => 'cny',
                        'client_ip' => get_client_ip(),
                        'subject'   => '订单付款',
                        'body'      => '订单付款'
                    ));
                    
                    $arr['ch_alipay']=json_decode($ch,true);
                    
                    
                    $ch=\Pingpp\Charge::create(array(
                        'order_no'  => $a.'',
                        'amount'    => $price*100,
                        'app'       => array('id' => self::$app_id),
                        'channel'   => 'alipay_wap',
                        'currency'  => 'cny',
                        'client_ip' => get_client_ip(),
                        'subject'   => '订单付款',
                        'body'      => '订单付款',
                        'extra'     =>  array(
                            'success_url' => 'http://www.yourdomain.com/success',
                            'cancel_url' => 'http://www.yourdomain.com/cancel'
                                    )
                    ));
                    
                    $arr['ch_alipay_wap']=json_decode($ch,true);
                    
                    $ch=\Pingpp\Charge::create(array(
                        'order_no'  => $a.'',
                        'amount'    => $price*100,
                        'app'       => array('id' => self::$app_id),
                        'channel'   => 'alipay_qr',
                        'currency'  => 'cny',
                        'client_ip' => get_client_ip(),
                        'subject'   => '订单付款',
                        'body'      => '订单付款',
                        
                    ));
                    
                    $arr['ch_alipay_qr']=json_decode($ch,true);
                    
                    $ch=\Pingpp\Charge::create(array(
                        'order_no'  => $a.'',
                        'amount'    => $price*100,
                        'app'       => array('id' => self::$app_id),
                        'channel'   => 'wx',
                        'currency'  => 'cny',
                        'client_ip' => get_client_ip(),
                        'subject'   => '订单付款',
                        'body'      => '订单付款'
                    ));
                    
                    $arr['ch_wx']=json_decode($ch,true);
                    
                    $ch=\Pingpp\Charge::create(array(
                        'order_no'  => $a.'',
                        'amount'    => $price*100,
                        'app'       => array('id' => self::$app_id),
                        'channel'   => 'wx_pub',
                        'currency'  => 'cny',
                        'client_ip' => get_client_ip(),
                        'subject'   => '订单付款',
                        'body'      => '订单付款',
                        'extra'     =>  array(
                            'open_id' => '212121'
                                    )
                    ));
                    
                    $arr['ch_wx_pub']=json_decode($ch,true);
                    
                    $ch=\Pingpp\Charge::create(array(
                        'order_no'  => $a.'',
                        'amount'    => $price*100,
                        'app'       => array('id' => self::$app_id),
                        'channel'   => 'wx_pub_qr',
                        'currency'  => 'cny',
                        'client_ip' => get_client_ip(),
                        'subject'   => '订单付款',
                        'body'      => '订单付款',
                        'extra'     =>  array(
                            'product_id' => '212121'
                                    )
                    ));
                    
                    $arr['ch_wx_pub_qr']=json_decode($ch,true);
                    
                 
                    
                    
                    
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
    
    //函数getHistoryOrder() 无请求参数
    public function getHistoryOrder()
    {
        if(session('id'))
        {
            $uid=session('id');
            $Order=M('order');
            $where['order_uid']=$uid;
            $where['order_state']='1';
            $arr['count']=$Order->where($where)->count(1);
            
            $arr['details']=$Order->where($where)
                ->field("order_id,order_requesttime,order_receivetime,order_price,order_payway,order_type")
                ->order('order_requesttime desc')
                ->limit($from,$num)
                ->select();
            $arr['state']='0';//成功
        }
        else
        {
            $arr['state']='30016';//未登录
        }
        echo json_encode($arr);
    }
    
    
    //函数getTabooDetail($nameArr) 得到禁忌症详情
    private function getTabooDetail($nameArr){
        $taboo=M('taboo');
        $where['taboo_name']=array('in',$nameArr);
        return $taboo->where($where)->field('taboo_name,taboo_type')->select();
    }
    
    
    
    //函数getHistoryOrderDetail() 请求参数；orderid
    public function getHistoryOrderDetail()
    {
        if(session('id'))
        {
            $orderid=I('post.orderid');
            
            $Order=M('order');
            
            $where['order_id']=$orderid;
            $where['order_uid']=session('id');
            $arr['orderDetail']=$Order->where($where)->field("order_id,order_requesttime,order_receivetime,order_price,order_payway,order_type,order_link")->find();
            if($arr['orderDetail']){
                if($arr['orderDetail']['order_type']=='0'){
                    //智能分析
                    $Autoanalyzerecord=M('autoanalyzerecord');
                    $autoWhere['autoanalyzerecord_orderid']=$orderid;
                    $autoWhere['autoanalyzerecord_uid']=session('id');
                    $arr['autoanalyzeDetail']=$Autoanalyzerecord->where($autoWhere)
                        ->field("autoanalyzerecord_time,autoanalyzerecord_relationship,autoanalyzerecord_age,autoanalyzerecord_medicine,autoanalyzerecord_inspect,autoanalyzerecord_zhenduan,autoanalyzerecord_notfound,autoanalyzerecord_taboo,autoanalyzerecord_result")->find();
                
                    $autoanalyzerecord_taboo=$arr['autoanalyzeDetail']['autoanalyzerecord_taboo'];
                    $taboo=$arr['autoanalyzeDetail']['autoanalyzerecord_taboo'];
                    $taboo=explode(',',$taboo);
                
                    $arr['taboodetail']=$this->getTabooDetail($taboo);
                }
                else if($arr['orderDetail']['order_type']=='1'){
                    //分析后咨询
                    $order_link=$arr['orderDetail']['order_link'];
                    $Autoanalyzerecord=M('autoanalyzerecord');
                    $autoWhere['autoanalyzerecord_orderid']=$order_link;
                    $autoWhere['autoanalyzerecord_uid']=session('id');
                    $arr['autoanalyzeDetail']=$Autoanalyzerecord->where($autoWhere)
                        ->field("autoanalyzerecord_time,autoanalyzerecord_relationship,autoanalyzerecord_age,autoanalyzerecord_medicine,autoanalyzerecord_inspect,autoanalyzerecord_zhenduan,autoanalyzerecord_notfound,autoanalyzerecord_taboo,autoanalyzerecord_result")->find();
               
                    $autoanalyzerecord_taboo=$arr['autoanalyzeDetail']['autoanalyzerecord_taboo'];
                    $taboo=$arr['autoanalyzeDetail']['autoanalyzerecord_taboo'];
                    $taboo=explode(',',$taboo);
                
                    $arr['taboodetail']=$this->getTabooDetail($taboo);
                    
                    $customservice_conversation=M('customservice_conversation');
                    $conversationWhere['customservice_conversation_oid']=$orderid;
                    $arr['conversationdetail']=$customservice_conversation->where($conversationWhere)->find();
                    if($arr['conversationdetail']){
                        $customservice=M('customservice');
                        $customserviceWhere['customservice_id']= $arr['conversationdetail']['customservice_conversation_csid'];
                        $arr['csdetail']=$customservice->where($customserviceWhere)->find();
                    }
                }
                else{
                    //直接咨询
                    $customservice_conversation=M('customservice_conversation');
                    $conversationWhere['customservice_conversation_oid']=$orderid;
                    $arr['conversationdetail']=$customservice_conversation->where($conversationWhere)->find();
                    if($arr['conversationdetail']){
                        $customservice=M('customservice');
                        $customserviceWhere['customservice_id']= $arr['conversationdetail']['customservice_conversation_csid'];
                        $arr['csdetail']=$customservice->where($customserviceWhere)->find();
                    }
                }
                
                
                $arr['state']='0';//成功
            }
            else{
                $arr['state']='30024';//不是你的订单
            }
            
            
            
           
        }
        else
        {
            $arr['state']='30016';//未登录
        }
        echo json_encode($arr);
    }
    //检查订单类型
    private function check_islegal($string){
        if($string=='0'||$string=='1'||$string=='2'){
            return true;
        }
        else{
            return false;
        }
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
            case '2':
                $name='money_doctor';
                break;
            default:
                break;
        }
        $where['setting_name']=$name;
        $price=$Setting->where($where)->getField('setting_values');
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
    
    
    
    
    
    public function paynotice(){
        
        $headers = array(); 
        foreach ($_SERVER as $key => $value) { 
        if ('HTTP_' == substr($key, 0, 5)) { 
            $headers[str_replace('_', '-', substr($key, 5))] = $value; 
            } 
        }
        $raw_data = file_get_contents('php://input');
        // 签名在头部信息的 x-pingplusplus-signature 字段
        $signature = $headers['X-PINGPLUSPLUS-SIGNATURE'];

        // 请从 https://dashboard.pingxx.com 获取「Webhooks 验证 Ping++ 公钥」
        $pub_key_path = "rsa_public_key.pem";
    
        $pub_key=file_get_contents($pub_key_path);

        $result = $this->verify_signature($raw_data, $signature, $pub_key);

        if ($result === 1) {
            //验签成功
            $json=json_decode($raw_data,true);
            $type=$json['type'];
            if($type=='charge.succeeded'){
                //支付成功
                $order_no=$json['data']['object']['order_no'];
                $amount=$json['data']['object']['amount'];
                $time_paid=$json['data']['object']['time_paid'];
                $channel=$json['data']['object']['channel'];
                
                $Order=M('order');
                $where['order_id']=$order_no;
                $where['order_state']=0;
                $savedata['order_receivetime']=$time_paid;
                $savedata['order_price']=$amount/100;
                $savedata['order_state']=1;
                switch($channel){
                    case 'alipay':
                    case 'alipay_wap':
                    case 'alipay_qr':
                        $savedata['order_payway']=2;
                        $Order->where($where)->save($savedata);
                        break;
                    case 'wx':
                    case 'wx_pub':
                    case 'wx_pub_qr':
                        $savedata['order_payway']=3;
                         $Order->where($where)->save($savedata);
                        break;
                    default:
                        break;
                }
            }
        } elseif ($result === 0) {
            //验签失败
        } else {
            //验签出错
        }

    }
    
    
    
    private function verify_signature($raw_data, $signature, $pub_key) {
        $pub_key_contents=$pub_key;
        return  1;
        return openssl_verify($raw_data, base64_decode($signature), $pub_key_contents, OPENSSL_ALGO_SHA256);
    }
    
  
    
}
?>