<?php
namespace Admin\Controller;
use Think\Controller;
class AnalyzeController extends Controller {
    public function index(){
      
      
    }
    
    
    private static $maxShow=50;//药品搜索最大数量
    
    
    /** 
    * 函数getICDfromCommonname,得到通用名对应的ICD数组
    * 
    * 得到通用名对应的ICD数组，函数接受通用名，返回ICD编码
    * 
    * @param string 通用名 
    * @return array() 为null时未找到，否则返回array of string，为一系列的ICD编码
    */ 
    private function getICDfromCommonname($name){
          $common=M('common');
          $where['common_name']=$name;
          $arr=$common->field('common_icdcode')->where($where)->select();
          if($arr){
            $ret=array();
            for($i=0;$i<count($arr);$i++){
                $ret[$i]=$arr[$i]['common_icdcode'];
            }
            return $ret;
          }
          else
            return $arr;
    }
    /** 
    * 函数checkComparable,检查适应症
    * 
    * 检查适应症,返回"是适应的"的真假
    * 
    * @param array() ICD编码数组
    * @param array() 通用名
    * @return BOOL 是适应的
    */ 
    private function checkComparable($ICDArr,$medicineId){
        $therapy=M('therapy');
        
        $where['therapy_icdcode']=array('in',$ICDArr);
        $where['therapy_medicineid']=array('in',$medicineId);
        
        if($therapy->where($where)->find()){
            return true;
        }
        else{
            return false;
        }
    }
    /** 
    * 函数getMedicineId,得到药品ID（可能返回多个）
    * 
    * 得到药品ID（可能返回多个），数组
    * 
    * @param string 药品名或商品名
    * @return array(string) 药品id
    */ 
    private function getMedicineId($name){
        $medicine=M('medicine');
        $condition['medicine_productname'] = $name;
        $condition['medicine_approvedrugname'] = $name;
        $condition['_logic'] = 'OR';
        $arr= $medicine->field('medicine_id')->where($condition)->select();
        if($arr){
            $ret=array();
            for($i=0;$i<count($arr);$i++){
                $ret[$i]=$arr[$i]['medicine_id'];
            }
            return $ret;
        }
        else
            return $arr;
    }
    
    
     /** 
    * 函数getTabooId,得到禁忌症ID
    * 
    * 得到药品ID（可能返回多个），数组
    * 
    * @param string 禁忌症名
    * @return array(string) 禁忌症id
    */ 
    private function getTabooId($name){
        $taboo=M('taboo');
        $where['taboo_name']=$name;
        $arr=$taboo->field('taboo_id')->where($where)->select();
        
        if($arr){
            $ret=array();
            for($i=0;$i<count($arr);$i++){
                $ret[$i]=$arr[$i]['taboo_id'];
            }
            return $ret;
        }
        else
            return $arr;
    }
    
    
     /** 
    * 函数getTabooNameFromId,得到禁忌症名
    * 
    * 
    * @param string 禁忌症id
    * @return string 禁忌症名
    */ 
     private function getTabooNameFromId($id){
        $taboo=M('taboo');
        $where['taboo_id']=$id;
        $r=$taboo->field('taboo_name')->where($where)->find();
        return $r['taboo_name'];
    }
    
    
    
    
    
    
     /** 
    * 函数checkForbid,检查禁忌症
    * 
    * 检查禁忌症，返回"是禁忌的"的真假
    * 
    * @param string 禁忌症ID
    * @param string 药品id
    * @return array()
    */ 
    private function checkForbid($tabooId,$medicineId){
        $taboodrug=M('taboodrug');
        //$tabooId=$this->getTabooId($disease);
        //if(!$tabooId)//找不到禁忌症
        //    return false;
       
        $where['taboodrug_tabooid']=array('in',$tabooId);
        //$where['taboodrug_medicineid']=$medicineId;
        
        $typeArr=$this->getTypeArr($medicineId);
        if($typeArr==null||count($typeArr)==0){
            return false;
        }
        
        $where['taboodrug_medicineclassid']=array('in',$typeArr);
        
        $arr=$taboodrug->field('taboodrug_tabooid')->where($where)->select();
        if($arr){
            $tmparr=array();
            for($i=0;$i<count($arr);$i++){
                $tmparr[$i]=$arr[$i]['taboodrug_tabooid'];
            }
            return $tmparr;
        }
        else{
            return null;
        }
    }
    
    /** 
    * 函数checkPair,检查配伍禁忌
    * 
    * 检查配伍禁忌，返回"是禁忌的"的真假
    * 
    * @param array(string) 类型数组
    * @param array(string) 类型数组
    * @return BOOL
    */ 
    private function checkPair($typeArr1,$typeArr2){
        $incompatibility=M('incompatibility');
        $where['incompatibility_medclass1']=array('in',$typeArr1);
        $where['incompatibility_medclass2']=array('in',$typeArr2);
        if($incompatibility->where($where)->find()){
            return true;
        }
        else{
            
            $where['incompatibility_medclass1']=array('in',$typeArr2);
            $where['incompatibility_medclass2']=array('in',$typeArr1);
            if($incompatibility->where($where)->find()){
                return true;
            }
            else
                return false;
        }
    }
    
    
     /** 
    * 函数getParentId,得到药品类型的父级
    * 
    * 得到药品类型的父级id
    * 
    * @param string 类型id
    * @return string
    */ 
    private function getParentId($type){
        $medicineclass=M('medicineclass');
        $where['medicineclass_id']=$type;
        $ret=$medicineclass->field('medicineclass_parentid')->where($where)->find();
        $ret=$ret['medicineclass_parentid'];
        return $ret;
    }
    
    /** 
    * 函数getTypeArr,得到药品类型
    * 
    * 得到药品类型
    * 
    * @param string 类型id
    * @return array(string)
    */ 
    private function getTypeArr($medicineId){
        $medicine_medicineclass=M('medicine_medicineclass');
        $where['medicine_medicineclass_medicineid']=$medicineId;
        $ret=$medicine_medicineclass->field('medicine_medicineclass_medicineclassid')->where($where)->select();
        
        $nowindex=0;
        $typeArr=array();
        
        for($i=0;$i<count($ret);$i++){
            $typeArr[$nowindex]=$ret[$i]['medicine_medicineclass_medicineclassid'];
            
            while(true){
                $parent=$this->getParentId($typeArr[$nowindex]);
                if($parent=='0'){
                    break;
                }
                else{
                    $nowindex++;
                    $typeArr[$nowindex]=$parent;
                }
            }
            $nowindex++;
        }
        
        return $typeArr;
    }
    
    /** 
    * 函数getInspectId,得到检查项目ID
    * 
    * 得到检查项目ID
    * 
    * @param string 项目名
    * @return array(string)
    */ 
    private function getInspectId($name){
        $inspect=M('inspect');
        $where['inspect_name']=$name;
        $arr=$inspect->field('inspect_id')->where($where)->select();
        if($arr){
            $ret=array();
            for($i=0;$i<count($arr);$i++){
                $ret[$i]=$arr[$i]['inspect_id'];
            }
            return $ret;
        }
        else
            return $arr;
    }                                                 

    /** 
    * 函数getMedicineList,得到药品列表
    * 
    * 得到药品列表
    * 
    * @param string 药品名（post.name）
    * @return json
    */ 
    public function getMedicineList(){
        $name=I('post.name');
        $name='%'.$name.'%';
        $medicine=M('medicine');
        $condition['medicine_productname'] = array('LIKE',$name);
        $condition['medicine_approvedrugname'] = array('LIKE',$name);
        $condition['_logic'] = 'OR';
        
        $count=$medicine->where($condition)->count(1);
        if($count>self::$maxShow){
            $arr= $medicine->field("
                                   medicine_id as medicine_id,
                                   medicine_productname as medicine_productname,
                                   medicine_approvedrugname as medicine_approvedrugname")->where($condition)->order('medicine_id asc')->limit('0,'.self::$maxShow)->select();
        }
        else{
            $arr= $medicine->field("
                                   medicine_id as medicine_id,
                                   medicine_productname as medicine_productname,
                                   medicine_approvedrugname as medicine_approvedrugname")->where($condition)->select();
        }
        
        $ret=array();
        $ret['count']=$count;
        $ret['list']=$arr;
        echo json_encode($ret);
    }
    
    /** 
    * 函数getMedicineDetail,得到药品详情
    * 
    * 得到药品详情
    * 
    * @param id 药品ID（post.id）
    * @return json
    */ 
    public function getMedicineDetail(){
        $id=I('post.id');
        $medicine=M('medicine');
        $where['medicine_id']=$id;
        
        $ret=$medicine->where($where)->find();
        
        $result=array();
        $result['state']='success';
        $result['detail']=$ret;
        echo json_encode($result);
        
    }
    
    
    
    
    
    /** 
    * 函数checkInspect,检查检查项目
    * 
    * 检查检查项目
    * 
    * @param array 检查项目ID
    * @param string 诊断的通用名
    * @return array()
    */ 
    private function checkInspect($inspectIdArr,$commonName){
        $auxinspectocommon=M('auxinspectocommon');
        $where['auxinspectocommon_commonname']=$commonName;
        $count=$auxinspectocommon->where($where)->count();
        $result=$auxinspectocommon->where($where)->select();
        $list=array();
        for($i=0;$i<$count;$i++){
            $list[$i]=$result[$i]['auxinspectocommon_auxinspect'];
        }
        
        
        $result=array();
        $j=0;
        
        $auxinspect_inspect=M('auxinspect_inspect');
        $auxinspect_inspectWhere['auxinspect_auxinspect_id']=array('in',$list);
        for($i=0;$i<count($inspectIdArr);$i++){
            $auxinspect_inspectWhere['auxinspect_inspect_inspect_id']=$inspectIdArr[$i];
            if($auxinspect_inspect->where($auxinspect_inspectWhere)->find()){
                //适应的
            }
            else{
                $result[$j]=$inspectIdArr[$i];
            }
        }
        
        return $result;
        
        
        
    }
    
    
    
    
    
    
    
    
    
    /** 
    * 函数getInspectList,得到检查项目列表
    * 
    * 得到检查项目列表
    * 
    * @param string 项目名（post.name）
    * @return json
    */ 
    public function getInspectList(){
        $name=I('post.name');
        $name='%'.$name.'%';
        $inspect=M('inspect');
        $condition['inspect_name']=array('LIKE',$name);
        
        $count=$inspect->where($condition)->count(1);
        if($count>self::$maxShow){
            $arr=$inspect->where($condition)->order('inspect_id asc')->limit('0,'.self::$maxShow)->select();
        }
        else{
            $arr=$inspect->where($condition)->select();
        }
        
        $ret=array();
        $ret['count']=$count;
        $ret['list']=$arr;
        $this->ajaxReturn($ret);
    }
    
    
    
    
    
    
    /** 
    * 函数getMedicineAndInspectList,得到药品和检查项目列表
    * 
    * 得到药品和检查项目列表
    * 
    * @param string 项目名（post.name）
    * @return json
    */ 
    public function getMedicineAndInspectList(){
        $name=I('post.name');
        $name='%'.$name.'%';
        $medicine=M('medicine');
        $condition['medicine_productname'] = array('LIKE',$name);
        $condition['medicine_approvedrugname'] = array('LIKE',$name);
        $condition['_logic'] = 'OR';
        
        $count=$medicine->where($condition)->count(1);
        if($count>self::$maxShow){
            $arr= $medicine->field("'medicine' as type,
                                   medicine_id as medicine_id,
                                   medicine_productname as medicine_productname,
                                   medicine_approvedrugname as medicine_approvedrugname")->where($condition)->order('medicine_id asc')->limit('0,'.self::$maxShow)->select();
        }
        else{
            $arr1= $medicine->field("'medicine' as type,
                                   medicine_id as medicine_id,
                                   medicine_productname as medicine_productname,
                                   medicine_approvedrugname as medicine_approvedrugname")->where($condition)->select();
            
            $inspect=M('inspect');
            $where['inspect_name']=array('LIKE',$name);
            $count1=$inspect->where($where)->count(1);
            if($count1+$count>self::$maxShow){
                $arr2=$inspect->field("'inspect' as type,
                                   inspect_id as inspect_id,
                                   inspect_name as inspect_name")->where($where)->order('inspect_id asc')->limit('0,'.(self::$maxShow-$count))->select();
            }
            else{
                $arr2=$inspect->field("'inspect' as type,
                                   inspect_id as inspect_id,
                                   inspect_name as inspect_name")->where($where)->select();
            }
            $arr=array_merge($arr1,$arr2);
            $count+=$count1;
            
        }
        
        
        $ret=array();
        $ret['count']=$count;
        $ret['list']=$arr;
        $this->ajaxReturn($ret);
    }
    
    
    
    
    
    
    
    
    
    
    
    /** 
    * 函数 checkOrderPaied,订单是否已支付
    * 
    * 得到检查项目列表
    * @param string 订单号
    * @return BOOL
    */ 
    private function checkOrderPaied($oid){
        $Order=M('order');
        $OrderWhere['order_id']=$oid;
        $OrderWhere['order_uid']=session('id');
        $OrderWhere['order_state']='1';
        if($Order->where($OrderWhere)->find()){
            return true;
        }
        else{
            return false;
        }
    }
    
    /** 
    * 函数 getOidAnalyze,查看订单的分析结果
    * 
    * 查看订单的分析结果
    * @param string 订单号
    * @return string
    */ 
    private function getOidAnalyze($oid){
        $autoanalyzerecord=M('autoanalyzerecord');
        $where['autoanalyzerecord_orderid']=$oid;
        $where['autoanalyzerecord_uid']=session('id');
        $r=$autoanalyzerecord->where($where)->find();
        if($r){
            return $r['autoanalyzerecord_result'];
        }
        else{
            return null;
        }
    }
    
    /** 
    * 函数analyze,智能分析
    * 
    * 得到检查项目列表
    * @param string 订单号（post.oid）  示例：111
    * @param string 关系（post.relationship）  示例：我的父母
    * @param string 禁忌症列表（post.taboo） 以,分割    示例：孕妇,糖尿病
    * @param string 药物列表（post.medicine） 以,分割    示例：阿司匹林,黄连素,泰诺
    * @param string 检查项目列表（post.inspect） 以,分割    示例：血常规,尿常规
    * @param string 无记录列表（post.notfound） 以,分割    示例：哈哈,呵呵,笑一个
    * @param string 诊断（post.zhenduan）    示例：流行性感冒
    * @return json
    */ 
    public function analyze(){
        $arr=array();
        if(session('id')){
            /*
            if(!$this->checkOrderPaied(I('post.oid'))){
                $arr['state']='10000';
                $this->ajaxReturn($arr);
                return;
            }
            $relationship= $this->getOidAnalyze(I('post.oid'));
            if($relationship){
                return $relationship;
            }
           
            */
            
            
            $relationship=I('post.relationship');$relationship='我的父母';
   
            $taboo=I('post.taboo');$taboo='妊娠,糖尿病';
            $taboo=explode(',',$taboo);
            
            $tabooIdArr=array();
            $tabooNameArr=array();
            $j=0;
            for($i=0;$i<count($taboo);$i++){
                $tmpArr=$this->getTabooId($taboo[$i]);
                if($tmpArr!=null&&count($tmpArr)==1){
                    $tabooNameArr[$j]=$taboo[$i];
                    $tabooIdArr[$j]=$tmpArr[0];
                    $j++;
                }
            }
            
            
            $medicine=I('post.medicine');$medicine='阿司匹林,黄连素,泰诺,红霉素眼膏';
            $medicine=explode(',',$medicine);
            
            $medicineIdArr=array();
            $medicineNameArr=array();
            
            
            $j=0;
            for($i=0;$i<count($medicine);$i++){
                $medicineId=$this->getMedicineId($medicine[$i]);
                if($medicineId!=null&&count($medicineId)==1){
                    $medicineIdArr[$j]=$medicineId[0];
                    $medicineNameArr[$j]=$medicine[$i];
                    $j++;
                }
            }
            
            $zhenduan=I('post.zhenduan');$zhenduan='iiif';
            $ICDArr=array();
            $ICDArr=$this->getICDfromCommonname($zhenduan);
            if($ICDArr==null)
                $ICDArr=array();
                
            
            $inspect=I('post.inspect');$inspect='血常规,尿常规';
            $inspect=explode(',',$inspect);
            
            $inspectArr=array();
            $inspectNameArr=array();
            $j=0;
            for($i=0;$i<count($inspect);$i++){
                $inspectId=$this->getInspectId($inspect[$i]);
                if($inspectId!=null&&count($inspectId)==1){
                    $inspectNameArr[$j]=$inspect[$i];
                    $inspectArr[$j]=$inspectId[0];
                    $j++;
                }
            }
            
            $notfound=I('post.notfound');$notfound='哈哈,呵呵,笑一个';
            
            
            
            //1、检查适应症（是否有多开的药）
            $ComparableRet=array();
            $j=0;
            for($i=0;$i<count($medicineIdArr);$i++){
                if($this->checkComparable($ICDArr,$medicineIdArr[$i])){
                    //适应的
                }
                else{
                    //不适应的
                    $tmpArr=array();
                    $tmpArr['medicineId']=$medicineIdArr[$i];
                    $tmpArr['medicineName']=$medicineNameArr[$i];
                    
                    
                    $ComparableRet[$j]=$tmpArr;
                    $j++;
                }
            }
            
            
            //2、检查禁忌症
            $ForbidRet=array();
            $j=0;
            for($i=0;$i<count($medicineIdArr);$i++){
               
                $medicineId=$medicineIdArr[$i]; 
                $t=$this->checkForbid($tabooIdArr,$medicineId);
       
                if($t){
                    //禁忌
                    $tmpArr=array();
                    $tmpArr['medicineId']=$medicineId;
                    
                    for($ll=0;$ll<count($medicineIdArr);$ll++){
                        if($medicineIdArr[$ll]==$medicineId){
                            $tmpArr['medicineName']=$medicineNameArr[$ll];
                            break;
                        }
                    }
            
                    $tt=array();
                    for($k=0;$k<count($t);$k++){
                        //$tt[$k]=$this->getTabooNameFromId($t[$k]);
                        for($ll=0;$ll<count($tabooNameArr);$ll++){
                            if($tabooIdArr[$ll]==$t[$k]){
                                $tt[$k]=$tabooNameArr[$ll];
                                break;
                            }
                        }
                    }
                    $tmpArr['tabooName']=$tt;
                    
                    $ForbidRet[$j]=$tmpArr;
                    $j++;
                }
                else{
                    //无禁忌
                }
            }
          
          
            //3、配伍禁忌
            $PairArr=array();
            $j=0;
            $typeArr=array();
            for($i=0;$i<count($medicineIdArr);$i++){
                $typeArr[$i]=$this->getTypeArr($medicineIdArr[$i]);
            }
     
            for($i=0;$i<count($medicineIdArr)-1;$i++){
                for($k=1;$k<count($medicineIdArr);$k++){
                    if($this->checkPair($typeArr[$i],$typeArr[$k])){
                        $tt=array();
                        $tt['mid1']=$medicineIdArr[$i];
                        $tt['mid1name']=$medicineNameArr[$i];
                        
                        $tt['mid2']=$medicineIdArr[$k];
                        $tt['mid2name']=$medicineNameArr[$k];
                        
                        $PairArr[$j]=$tt;
                        $j++;
                    }
                }
            }
            
            
            //检查项目
            
            $inspectArr=$this->checkInspect($inspectArr,$zhenduan);
            $arr1=array();
            for($i=0;$i<count($inspectArr);$i++){
                $arr1[$i]['inspectId']=$inspectArr[$i];
                $arr1[$i]['inspectName']=$inspectNameArr[$i];
            }
            
            $arr['InspectArr']=$arr1;
          
            $arr['state']='0';
            $arr['ComparableRet']=$ComparableRet;
            $arr['ForbidRet']=$ForbidRet;
            $arr['PairArr']=$PairArr;
            
            
            
            
            $problemCount=count($arr['InspectArr'])+
                count($arr['ComparableRet'])+
                count($arr['ForbidRet'])+
                count($arr['PairArr']);
            $arr['problemCount']=$problemCount;
            if($problemCount==0){
                //没问题
                $arr['result']='恭喜您，您的提交没有异常项目';
            }
            else{
                $arr['result']='您的提交有'.$arr['problemCount'].'个问题。具体如下:\r\n';
                $j=1;
                for($i=0;$i<count($arr['InspectArr']);$i++){
                    $arr['result']=$arr['result'].($j.'.检查项目【'.$arr['InspectArr'][$i]['inspectName'].'】是多开的检查;\r\n');
                    $j++;
                }
                
                for($i=0;$i<count($arr['ComparableRet']);$i++){
                    $arr['result']=$arr['result'].($j.'.药物【'.$arr['ComparableRet'][$i]['medicineName'].'】是多开的药物;\r\n');
                    $j++;
                }
                
                for($i=0;$i<count($arr['ForbidRet']);$i++){
                    
                    $tabooLIST=$arr['ForbidRet'][$i]['tabooName'][0];
                    for($ll=1;$ll<count($arr['ForbidRet'][$i]['tabooName']);$ll++){
                        $tabooLIST=$tabooLIST.'、'.$arr['ForbidRet'][$i]['tabooName'][$ll];
                    }
                    
                    $arr['result']=$arr['result'].($j.'.药物【'.$arr['ForbidRet'][$i]['medicineName'].'】禁用于'.$tabooLIST.';\r\n');
                    $j++;
                }
                
                for($i=0;$i<count($arr['PairArr']);$i++){
                    $arr['result']=$arr['result'].($j.'.药物【'.$arr['PairArr'][$i]['mid1name'].'】不能与药物【'
                                                   .$arr['PairArr'][$i]['mid2name'].'】同时使用;\r\n');
                    $j++;
                }
                
            }
            
            $autoanalyzerecord=M('autoanalyzerecord');
            $addData['autoanalyzerecord_uid']=session('id');
            $addData['autoanalyzerecord_time']=time();
            $addData['autoanalyzerecord_relationship']=I('post.relationship');
            $addData['autoanalyzerecord_medicine']=I('post.medicine');
            $addData['autoanalyzerecord_inspect']=I('post.inspect');
            $addData['autoanalyzerecord_zhenduan']=I('post.zhenduan');
            $addData['autoanalyzerecord_notfound']=I('post.notfound');
            $addData['autoanalyzerecord_taboo']=I('post.taboo');
            $addData['autoanalyzerecord_result']=$arr['result'];
            $addData['autoanalyzerecord_orderid']=I('post.oid');
            $autoanalyzerecord->add($addData);
        }
        else{
            $arr['state']='2001';
        }
        $this->ajaxReturn($arr);
    }
    
    
    
    
    
    
   
    
    
    
}

