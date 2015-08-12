<?php
namespace Home\Controller;
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
          $arr=$common->where($where)->field('common_icdcode')->select();  
          if($arr){
            $ret=array();
            $count=count($arr);
            for($i=0;$i<$count;$i++){
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
        $where['therapy_medicinecode']=array('in',$medicineId);
        
        if($therapy->where($where)->find()){
            return true;
        }
        else{
            return false;
        }
    }
    /** 
    * 函数getMedicineCode,得到药品ID（可能返回多个）
    * 
    * 得到药品ID（可能返回多个），数组
    * 
    * @param string 药品名或商品名
    * @return array(string) 药品id
    */ 
    private function getMedicineCode($name){
        $medicine=M('medicine');
        $condition['medicine_productname'] = $name;
        $condition['medicine_approvedrugname'] = $name;
        $condition['_logic'] = 'OR';
        $arr= $medicine->where($condition)->field('distinct medicine_code as medicine_code')->select();
        if($arr){
            $ret=array();
            $count=count($arr);
            for($i=0;$i<$count;$i++){
                $ret[$i]=$arr[$i]['medicine_code'];
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
    public  function getTabooId($name){
        $taboo=M('taboo');
        $where['taboo_name']=$name;
        $arr=$taboo->where($where)->field('taboo_id')->select();

        if($arr){
            $ret=array();
            $count=count($arr);
            for($i=0;$i<$count;$i++){
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
        return $taboo->where($where)->getField("taboo_name"); 
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
        
        $arr=$taboodrug->where($where)->field('taboodrug_tabooid')->select();
        if($arr){
            $tmparr=array();
            $count=count($arr);
            for($i=0;$i<$count;$i++){
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
    * @return string(如果为null则为禁忌)
    */ 
    private function checkPair($typeArr1,$typeArr2){
        $incompatibility=M('incompatibility');
        $where['incompatibility_medclass1']=array('in',$typeArr1);
        $where['incompatibility_medclass2']=array('in',$typeArr2);
        if($incompatibility->where($where)->find()){
            return $r['incompatibility_describe'];
        }
        else{
            
            $where['incompatibility_medclass1']=array('in',$typeArr2);
            $where['incompatibility_medclass2']=array('in',$typeArr1);
            $r=$incompatibility->where($where)->find();
            if($r){
                if($r['incompatibility_describe'])
                    return $r['incompatibility_describe'];
                else
                    return '';
            }
            else
                return null;
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
        $ret=$medicineclass->where($where)->getField("medicineclass_parentid");
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
    private function getTypeArr($medicineCode){
        $medicineclass=M('medicineclass');
        $where['medicineclass_classname']=$medicineCode;
        $where['medicineclass_type']=1;
        $ret=$medicineclass->where($where)->field('medicineclass_parentid')->select();
        
        $nowindex=0;
        $typeArr=array();
        
        $count=count($ret);
        for($i=0;$i<$count;$i++){
            $typeArr[$nowindex]=$ret[$i]['medicineclass_parentid'];
            
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
    * 函数record,记录未记录的
    * 
    * 记录未记录的
    * 
    * @param string 类型
    * @param string 搜索内容
    * @return 
    */ 
    private function record($type,$text){
        $norecord=M('norecord');
        $adddata['norecord_type']=$type;
        $adddata['norecord_text']=$text;
        $adddata['norecord_state']=0;
        $adddata['norecord_count']=1;
        if($norecord->add($adddata)){
            //添加成功
        }
        else{
            //添加失败
            $where['norecord_type']=$type;
            $where['norecord_text']=$text;
            $savedata['norecord_state']=0;
            $norecord->where($where)->save($savedata);
            $norecord->where($where)->setInc('norecord_count');
        }
        
        
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
            $count=count($arr);
            for($i=0;$i<$count;$i++){
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
            $arr= $medicine->where($condition)->field("
                                   medicine_id as medicine_id,
                                   medicine_code as medicine_code,
                                   medicine_productname as medicine_productname,
                                   medicine_approvedrugname as medicine_approvedrugname")->order('CONVERT( medicine_productname USING gbk )')->limit('0,'.self::$maxShow)->select();
        }
        else{
            $arr= $medicine->where($condition)->field("
                                   medicine_id as medicine_id,
                                   medicine_code as medicine_code,
                                   medicine_productname as medicine_productname,
                                   medicine_approvedrugname as medicine_approvedrugname")->order('CONVERT( medicine_productname USING gbk )')->select();
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
    * 函数getcommonname，得到诊断名列表
    * 
    * 得到诊断名列表
    * 
    * @param string 诊断名(post.name)
    * @return json
    */ 
    public function getcommonname(){
        $common=M('common');
        $where['common_name']=array('like',I('post.name').'%');
        $arr['count']=$common->where($where)->count('distinct common_name');
        $arr['list']=$common->where($where)->field('distinct common_name as name')->order('name asc')->limit('0,50')->select();
        $this->ajaxReturn($arr);
    }
    
    
    
    /** 
    * 函数getInspectParentId,得到检查项目类别父级
    * 
    * 得到检查项目类别父级
    * 
    * @param string 检查项目关系ID
    * @return string
    */
    private function getInspectParentId($relationid){
        $auxinspect_inspect_relation=M('auxinspect_inspect_relation');
        $where['auxinspect_inspect_relation_id']=$relationid;
        $r=$auxinspect_inspect_relation->field('auxinspect_inspect_relation_parentid')->where($where)->find();
        return $r['auxinspect_inspect_relation_parentid'];
    }
    
    
    /** 
    * 函数getInspectTypeArr,得到检查项目类别组库
    * 
    * 检查检查项目
    * 
    * @param string 类型(0:组库 1:检查)
    * @param string 节点id（组库id或者检查项目id）
    * @return array()
    */ 
    private function getInspectTypeArr($type,$nodeid){
        $auxinspect_inspect_relation=M('auxinspect_inspect_relation');
        $where['auxinspect_inspect_relation_type']=$type;
        $where['auxinspect_inspect_relation_nodeid']=$nodeid;
        $r=$auxinspect_inspect_relation->where($where)->field('auxinspect_inspect_relation_id')->select();
        $arr=array();
        $j=0;
        $count=count($r);
        for($i=0;$i<$count;$i++){
            $now=$r[$i]['auxinspect_inspect_relation_id'];
            $parent=$this->getInspectParentId($now);
            while($parent&&$parent!='-1'){
                $arr[$j]=$parent;
                $j++;
                $parent=$this->getInspectParentId($parent);
            }
        }
        return $arr;
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
        $auxinspectocommon=M('auxinspect_inspect_relation_commonn');
        $where['auxinspect_inspect_relation_common_name']=$commonName;
       
        $result=array();
        $j=0;
        $count=count($inspectIdArr);
        for($i=0;$i<$count;$i++){
            
            $typeArr=$this->getInspectTypeArr('1',$inspectIdArr[$i]);
            
            
           
            
            $where['auxinspect_inspect_relation_common_relationid']=array('in',$typeArr);
            if($auxinspectocommon->where($where)->find()){
                //适应的
            }
            else{
                $result[$j]=$inspectIdArr[$i];
                $j++;
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
            $arr=$inspect->where($condition)->order('CONVERT( inspect_name USING gbk )')->limit('0,'.self::$maxShow)->select();
        }
        else{
            $arr=$inspect->where($condition)->order('CONVERT( inspect_name USING gbk )')->select();
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
            $arr= $medicine->where($condition)->field("'medicine' as type,
                                   medicine_id as medicine_id,
                                   medicine_code as medicine_code,
                                   medicine_productname as medicine_productname,
                                   medicine_approvedrugname as medicine_approvedrugname")->order('CONVERT( medicine_productname USING gbk )')->limit('0,'.self::$maxShow)->select();
        }
        else{
            $arr1= $medicine->where($condition)->field("'medicine' as type,
                                   medicine_id as medicine_id,
                                   medicine_code as medicine_code,
                                   medicine_productname as medicine_productname,
                                   medicine_approvedrugname as medicine_approvedrugname")->order('CONVERT( medicine_productname USING gbk )')->select();
            
            $inspect=M('inspect');
            $where['inspect_name']=array('LIKE',$name);
            $count1=$inspect->where($where)->count(1);
            if($count1+$count>self::$maxShow){
                $arr2=$inspect->field("'inspect' as type,
                                   inspect_id as inspect_id,
                                   inspect_name as inspect_name")->where($where)->order('CONVERT( inspect_name USING gbk )')->limit('0,'.(self::$maxShow-$count))->select();
            }
            else{
                $arr2=$inspect->field("'inspect' as type,
                                   inspect_id as inspect_id,
                                   inspect_name as inspect_name")->where($where)->order('CONVERT( inspect_name USING gbk )')->select();
            }
            if($arr1&&$arr2)
                $arr=array_merge($arr1,$arr2);
            else{
                if($arr1){
                    $arr=$arr1;
                }
                else{
                    $arr=$arr2;
                }
            }
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
    * 订单是否已支付
    * @param string 订单号
    * @return BOOL
    */ 
    private function checkOrderPaied($oid){
        $Order=M('order');
        $OrderWhere['order_id']=$oid;
        $OrderWhere['order_uid']=session('id');
        $OrderWhere['order_state']='1';
        $OrderWhere['order_type']='0';
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
    * @param string 年龄（post.age）    示例：18
    * @return json
    */ 
    public function analyze(){
        $arr=array();
        if(session('id')){
            
            $oid=I('post.oid');
            
            if(!$this->checkOrderPaied($oid)){
                $arr['state']='10000';
                $this->ajaxReturn($arr);
                return;
            }
            $relationship= $this->getOidAnalyze($oid);
            if($relationship){
                echo $relationship;
                return;
            }
           
            $preg='/^(0|[1-9]\d|100)$/';
            $age=I('post.age');
            if( !preg_match($preg,$age)){
                $arr['state']='2000';
                $this->ajaxReturn($arr);
                return;
            }
            
            
            
            
            $relationship=I('post.relationship');
            //$relationship='我的父母';
   
            $taboo=I('post.taboo');
           // $taboo='妊娠,糖尿病';
            $taboo=explode(',',$taboo);
             
            $tabooIdArr=array();
            $tabooNameArr=array();
            $j=0;
            
            $tabooCount=count($taboo);
            
            for($i=0;$i<$tabooCount;$i++){
                $tmpArr=$this->getTabooId($taboo[$i]);
                
                $tmpcount=count($tmpArr);
                if($tmpArr!=null&&$tmpcount==1){
                    $tabooNameArr[$j]=$taboo[$i];
                    $tabooIdArr[$j]=$tmpArr[0];
                    $j++;
                }
            }
            $notfound=I('post.notfound');
            //$notfound='哈哈,呵呵,笑一个';
            $notfoundArr=explode(',',$notfound);
           
            
            $notfoundArr=array();
            $notfoundArrIndex=0;
            
            $medicine=I('post.medicine');
           // $medicine='阿司匹林,黄连素,泰诺,红霉素眼膏';
            for($i=0;$i<count($notfoundArr);$i++){
                $a=$this->getMedicineCode($notfoundArr[$i]);
                if($a){
                    if($medicine!='')
                        $medicine=$medicine.','.$notfoundArr[$i];
                    else{
                        $medicine=$notfoundArr[$i];
                    }
                }
                else{
                    $notfoundArr[$notfoundArrIndex]=$notfoundArr[$i];
                    $notfoundArrIndex++;
                }
            }
            if($notfoundArrIndex>0){
                if(count($notfoundArr)>0){
                    $notfound=$notfoundArr[0];
                }
                else
                     $notfound='';
                for($i=0;$i<count($notfoundArr);$i++){
                    $notfound=$notfound.','.$notfoundArr[$i];
                }
            }
            
            
            
            $medicine=explode(',',$medicine);
            
            $medicineIdArr=array();
            $medicineNameArr=array();
            
            
            $j=0;
            for($i=0;$i<count($medicine);$i++){
                $medicineId=$this->getMedicineCode($medicine[$i]);
                if($medicineId!=null&&count($medicineId)==1){
                    $medicineIdArr[$j]=$medicineId[0];
                    $medicineNameArr[$j]=$medicine[$i];
                    $j++;
                }
            }
            
            $zhenduan=I('post.zhenduan');
            $zhenduan='iiif';
            $ICDArr=array();
            $ICDArr=$this->getICDfromCommonname($zhenduan);
            if($ICDArr==null){
                $this->record('1',$zhenduan);
                $ICDArr=array();
            }
                
            
            $inspect=I('post.inspect');
           // $inspect='血常规,尿常规';
             
            $notfoundArr=array();
            $notfoundArrIndex=0;
            for($i=0;$i<count($notfoundArr);$i++){
                $a=$this->getInspectId($notfoundArr[$i]);
                if($a){
                    if($inspect!='')
                        $inspect=$inspect.','.$notfoundArr[$i];
                    else{
                        $inspect=$notfoundArr[$i];
                    }
                }
                else{
                    $notfoundArr[$notfoundArrIndex]=$notfoundArr[$i];
                    $notfoundArrIndex++;
                }
            }
            
            
            if($notfoundArrIndex>0){
                if(count($notfoundArr)>0){
                    $notfound=$notfoundArr[0];
                }
                else
                     $notfound='';
                for($i=0;$i<count($notfoundArr);$i++){
                    $notfound=$notfound.','.$notfoundArr[$i];
                }
            }
            
            
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
                    $des=$this->checkPair($typeArr[$i],$typeArr[$k]);
                    if($des){
                        $tt=array();
                        $tt['mid1']=$medicineIdArr[$i];
                        $tt['mid1name']=$medicineNameArr[$i];
                        
                        $tt['mid2']=$medicineIdArr[$k];
                        $tt['mid2name']=$medicineNameArr[$k];
                        
                        $tt['des']=$des;
                        
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
                                                   .$arr['PairArr'][$i]['mid2name'].'】同时使用,'.$arr['PairArr'][$i]['des'].';\r\n');
                    $j++;
                }
                
            }
           
            $medicine='';
            
            $count=count($medicineNameArr);
            for($i=0;$i<$count;$i++){
                if($i==0){
                    $medicine=$medicineNameArr[$i];
                }
                else{
                    $medicine=$medicine.','.$medicineNameArr[$i];
                }
            }
            
            $inspect='';
            
            $count=count($inspectNameArr);
            for($i=0;$i<$count;$i++){
                if($i==0){
                    $inspect=$inspectNameArr[$i];
                }
                else{
                    $inspect=$inspect.','.$inspectNameArr[$i];
                }
            }
            
            $taboo='';
            
            $count=count($tabooNameArr);
            for($i=0;$i<$count;$i++){
                if($i==0){
                    $taboo=$tabooNameArr[$i];
                }
                else{
                    $taboo=$taboo.','.$tabooNameArr[$i];
                }
            }
            
           
            
            
            $autoanalyzerecord=M('autoanalyzerecord');
            $addData['autoanalyzerecord_uid']=session('id');
            $addData['autoanalyzerecord_time']=time();
            $addData['autoanalyzerecord_relationship']=$relationship;
            $addData['autoanalyzerecord_medicine']=$medicine;
            $addData['autoanalyzerecord_inspect']=$inspect;
            $addData['autoanalyzerecord_zhenduan']=$zhenduan;
            $addData['autoanalyzerecord_notfound']=$notfound;
            $addData['autoanalyzerecord_taboo']=$taboo;
            $addData['autoanalyzerecord_age']=$age;
            $addData['autoanalyzerecord_orderid']=$oid;
            
            $rrresult=$arr;
            $rrrjson=array();
            foreach($arr as $key=>$value){
                $rrrjson[$key] = urlencode($value);
            }
            $rrresult=json_encode($rrrjson);
            $rrresult=urldecode($rrresult);
            
            $addData['autoanalyzerecord_result']= $rrresult;   
            $autoanalyzerecord->add($addData);
        
            if($notfound!=''){
                $notfoundArr=explode(',',$notfound);
                for($i=0;$i<count($notfoundArr);$i++)
                    $this->record('0',$notfoundArr[$i]);
            }
            
        }
        else{
            $arr['state']='2001';
        }
        $this->ajaxReturn($arr);
    }
    
    
    
    /** 
    * 函数 gettaboolist,得到禁忌症列表
    * 
    * 得到禁忌症列表
    * @return irritability  禁忌症
    *         others        其他
    */ 
    public function gettaboolist(){
        $taboo=M('taboo');
        $where['taboo_type']='过敏';
        $arr['irritability']=$taboo->where($where)->field(array('taboo_id',
                                                                'taboo_name',
                                                                'taboo_type'))->order('CONVERT( taboo_name USING gbk )')->select();
        $where['taboo_type']=array('neq','过敏');
        $where['taboo_name']=array('not in',array('孕妇','婴幼儿'));
        $arr['others']=$taboo->where($where)->field(array('taboo_id',
                                                                'taboo_name',
                                                                'taboo_type'))->order('CONVERT( taboo_name USING gbk )')->select();
        $this->ajaxReturn($arr);
    }
    
   
    
    
    
}

