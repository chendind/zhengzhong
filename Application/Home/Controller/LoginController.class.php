<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {
    private static $client_id='YXA6BbdmUDW7EeWZ7Yky5gX_BA';
    private static $client_secret='YXA69h0j5EIKT2_TxcvJj2O_u3POd3w';
    private static $org_name='jieyao';
    private static $app_name='jieyao';
    //登陆 请求参数：phone,pass
    public function login(){
        $phone=I('post.phone');
        $pass=md5(I('post.pass'));
        $arr=array();
        
        $User=M('user');
        $where['user_phone']=$phone;
        $where['user_password']=$pass;
        $user=$User->where($where)->find();
        
        if($user){
            $arr['state']='0';//成功
            $arr['uid']=$user['user_id'];//成功
            session("id",$user["user_id"]);
            session("phone",$user["user_phone"]);
        }
        else{
            $uWhere['user_phone']=$phone;
            if($User->where($uWhere)->find()){
                $arr['state']='30000';//密码错误
            }
            else{
                $arr['state']='30001';//用户不存在
            }
        }
        
        
        $this->ajaxReturn($arr);
    }
    
    //登出 无请求参数
    public function logout()
    {
        if(session("id"))
        {
            session(null);
            $arr["state"]="30002";//注销成功
        }
        else
        {
            $arr['state']="30090";//已注销
        }
        echo json_encode($arr);
    }
    
    //注册获取验证码 请求参数：phone
    public function registerCheckCode()
    {
        $phone=I("post.phone");
        $Verify=M("verify");
        if($this->check_phone($phone))
        {
            if(!$this->check_phone_exist($phone))
            {
                $conditionv['verify_source']=$phone;
                $conditionv['verify_type']='0';
               $conditionv['verify_time']=array('GT',time()-24*3600);
               $count=$Verify->where($conditionv)->count();
               if($count>=5){
                    $arr['state']="30003";//24小时内发送短信次数不得大于5
                }
                else{
                     $code=randomnumber(6);//生成6位长度数字验证码

                $savedatav['verify_code']=$code;
                $savedatav['verify_time']=time();
                $savedatav['verify_isused']='0';
                $savedatav['verify_type']='0';
                $savedatav['verify_source']=$phone;
                $Verify->add($savedatav);//添加验证码
                $this->sendCodeToPhone($phone,$code);//给手机发送验证码
                $arr['state']='0';//发送成功
                }
            }
            else
            {
                $arr["state"]="30004";//手机号已存在
            }
        }
        else
        {
            $arr["state"]="30005";//手机号不合法
        }
        echo json_encode($arr);
    }
    
    //注册验证码检验 请求参数：phone，code
    public function registerCheck()
    {
        $phone=I("post.phone");
        $checkCode=I("post.code");
        if($this->check_islegal($checkCode))
        {
            $Verify=M("verify");
           $condition['verify_source']=$phone;
           $condition['verify_type']="0";
           $verify=$Verify->where($condition)
                    ->field("verify_id,verify_code,verify_time,verify_isused")->order("verify_time desc")->find();
           if($verify['verify_time']-time()<=86400){
             if($verify['verify_code']==$checkCode&&$verify['verify_isused']=='0'){
                $condition1['verify_id']=$verify['verify_id'];
                $savedata['verify_isused']='1';
                $Verify->where($condition1)->save($savedata);//设置验证码已经使用
                $arr['state']='0';//验证成功
               
             }
             //验证码不正确
             else{
                $arr['state']='30006';
             }
           }
           //验证码时间已经过了
           else{
            $arr['state']='30007';
           }
        }
        else{
            $arr['state']='30008';//不合法的验证码
        }
        echo json_encode($arr);
    }
    
    
    //注册 请求参数：phone，code，password
    public function register()
    {
        $phone=I("post.phone");
        $checkCode=I("post.code");
        $password=md5(I('post.password'));
        if($this->check_islegal($checkCode))
        {
            $Verify=M("verify");
            $condition['verify_type']="0";
            $condition['verify_source']=$phone;
            $verify=$Verify->where($condition)
                    ->field("verify_id,verify_code,verify_time,verify_isused")->order("verify_time desc")->find();
             if($verify['verify_code']==$checkCode)
             {
                if($this->check_phone_exist($phone))
                {
                    $arr['state']='30009';//已注册过，不能再次注册
                }
                else
                {
                    $User=M("user");
                    $conditionv["user_password"]=$password;
                    $conditionv["user_phone"]=$phone;
                    $conditionv['user_registertime']=time();
                    $a=$User->add($conditionv);
                    if($a)
                    {
                     $arr['state']='0';//注册成功
                     $arr['uid']=$a.'';//成功
                        require('easemob.class.php');
                        $options=array();
                        $options['client_id']=self::$client_id;
                        $options['client_secret']=self::$client_secret;
                        $options['org_name']=self::$org_name;
                        $options['app_name']=self::$app_name;
		
                        $easemob=new \easemob($options);
                        
                        $useroption['username']=$a.'';
                        $useroption['password']=md5($password);
                        
                        $easemob->accreditRegister($groupId);
                    }   
                    else
                    {
                        $arr['state']='30010';//注册失败
                    }
                }
             }
             //验证码不正确
             else
             {
                $arr['state']='30006';
             }
        }
        else{
            $arr['state']='30008';//不合法的验证码
        }
        echo json_encode($arr);
    }
    
    
    
    
    //忘记密码获取手机验证码 请求参数：phone
    public function forgetPassCheckCode()
    {
        $phone=I("post.phone");
        $Verify=M("verify");
        if($this->check_phone($phone))
        {
            if($this->check_phone_exist($phone))
            {
                $conditionv['verify_source']=$phone;
                $conditionv['verify_type']='1';
               $conditionv['verify_time']=array('GT',time()-24*3600);
               $count=$Verify->where($conditionv)->count();
               if($count>=5){
                    $arr['state']="30003";//24小时内发送短信次数不得大于5
                }
                else{
                     $code=randomnumber(6);//生成6位长度数字验证码

                $savedatav['verify_code']=$code;
                $savedatav['verify_time']=time();
                $savedatav['verify_isused']='0';
                $savedatav['verify_type']='1';//
                $savedatav['verify_source']=$phone;
                $Verify->add($savedatav);//添加验证码
                $this->sendCodeToPhone($phone,$code);//给手机发送验证码
                $arr['state']='0';//发送成功
                }
            }
            else
            {
                $arr["state"]="30019";//手机号不存在
            }
        }
        else
        {
            $arr["state"]="30005";//手机号不合法
        }
        echo json_encode($arr);
    }
    
    //忘记密码检验手机验证码 请求参数：phone，code
    public function forgetPassCheck()
    {
        $phone=I("post.phone");
        $checkCode=I("post.code");
        
        if($this->check_islegal($checkCode))
        {
            $Verify=M("verify");
           $condition['verify_source']=$phone;
           $condition['verify_type']="1";
           $verify=$Verify->where($condition)->field("verify_id,verify_code,verify_time,verify_isused")->order("verify_time desc")->find();
           if($verify['verify_time']-time()<=86400){
             if($verify['verify_code']==$checkCode&&$verify['verify_isused']=='0'){
                $condition1['verify_id']=$verify['verify_id'];
                $savedata['verify_isused']='1';
                $Verify->where($condition1)->save($savedata);//设置验证码已经使用
                $arr['state']='0';//验证成功
               
             }
             //验证码不正确
             else{
                $arr['state']='30006';
             }
           }
           //验证码时间已经过了
           else{
            $arr['state']='30007';
           }
        }
        else{
            $arr['state']='30008';//不合法的验证码
        }
        echo json_encode($arr);
    }
    
    
    //忘记密码 请求参数：phone，code，password
    public function forgetPass()
    {
        $phone=I("post.phone");
        $checkCode=I("post.code");
        $password=md5(I('post.password'));
        if($this->check_islegal($checkCode))
        {
            $Verify=M("verify");
            $condition['verify_type']="1";
            $condition['verify_source']=$phone;
            $verify=$Verify->where($condition)->field("verify_id,verify_code,verify_time,verify_isused")->order("verify_time desc")->find();
             if($verify['verify_code']==$checkCode)
             {
                if(!$this->check_phone_exist($phone))
                {
                    $arr['state']='30011';//不存在这个手机号
                }
                else
                {
                $User=M("user");
                $savedate["user_password"]=$password;
                $conditionv["user_phone"]=$phone;
                $a=$User->where($conditionv)->save($savedate);
                if($a)
                {
                    $arr['state']='0';//修改密码成功
                }
                else
                {
                    $arr['state']='30012';//修改密码失败
                }
                }
             }
             //验证码不正确
             else
             {
                $arr['state']='30006';
             }
        }
        else{
            $arr['state']='30008';//不合法的验证码
        }
        echo json_encode($arr);
    }
    
    //修改密码 请求参数：oldPass，newPass
    public function changePassword()
   {
      $oldPass=md5(I("post.oldpass"));
      $newPass=md5(I("post.newpass"));
      $User=M("user");
      $uid=session("id");
      if(session("id"))
      {
         if($this->check_islegal($oldPass)&&$this->check_islegal($newPass))
         {  
            $user=$User->where("user_id=$uid")->find();
            if($oldPass==$user["user_password"])
            {
               if($oldPass!=$newPass)
               {
                  $savedata['user_password']=$newPass;
                  $exe=$User->where("user_id=$uid")->save($savedata);
                  if($exe)
                  {
                    $arr["state"]='0';//修改密码成功
                  }
                  else
                  {
                    $arr["state"]='30012';//修改密码失败
                  }
               }
               else
               {
                  $arr['state']='30013';//新密码和旧密码不能一致
               }
            }
            else
            {
               $arr['state']='30014';//密码输入不正确
            }
         }
         else
         {
            $arr["state"]='30015';//不合法字段
         }
      }
      else{
         $arr['state']='30016';//未登录
      }
      echo json_encode($arr);
   }
    
    private function  check_phone($phone){
        $preg='/^(13|14|15|16|17|18|19)\d{9}$/';
      
        return preg_match($preg,$phone);
        
    }
    //验证字段
    private function check_islegal($string){
    
       return true;
    }
    private  function check_phone_exist($phone){
        $User=M("user");
        $condition['user_phone']=$phone;
        $exit=$User->where($condition)->find();
        if($exit){
            return true;
    }
    else{
        return false;
    }

    }
    private function sendCodeToPhone($phone="",$code=""){
    
    $ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://api.weimi.cc/2/sms/send.html");
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, TRUE);
curl_setopt($ch, CURLOPT_SSLVERSION , 3);
curl_setopt($ch, CURLOPT_POST, TRUE);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'uid=5yFm4PETTfxQ&pas=5kcj99m4&cid=fWTVcwa0IPUG&mob='.$phone.'&p1='.$code);
//注意 在正式使用时，请删除cid这个段
$res = curl_exec( $ch );

curl_close( $ch );
   
 }
}