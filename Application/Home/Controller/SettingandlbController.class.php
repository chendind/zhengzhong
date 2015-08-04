<?php
namespace Home\Controller;
use Think\Controller;
class SettingandlbController extends Controller {
    /***
     *
     *获得轮播图
     */
    public function getlb(){
        $tablelb=M('lb');
      //  $arr['lb']=$tablelb->field('lb_id,lb_time,lb_img,lb_title')->order('lb_rank')->select();
          $arr['lb']=$tablelb->order('lb_rank')->select();
          
          $arr['count']=$tablelb->count();
          $arr['state']='0';
        echo json_encode($arr);
    }
    public function getcontact(){
        $tablesetting=M('setting');
        $where['setting_id']=array(array('egt','1'),array('elt','7'),'AND');
        $arr['contact']=$tablesetting->where($where)->select();
        $arr['state']='0';
        echo json_encode($arr);
       // echo json_encode($this->getmoneysmart());
       // echo json_encode($this->getmoneydoctor());
       /// echo json_encode($this->getstatement());
    }
    
    
    /**
     *获得智能分析的费用
     *返回string
     */
    private  function  getmoneysmart(){
        $tablesetting=M('setting');
        $where['setting_name']="money_smart";
        $arr=$tablesetting->where($where)->getField("setting_values");
        return $arr;
    }
    
    
    
    /****
     *获得人工咨询的费用
     *
     *
     */
    private  function  getmoneydoctor(){
        $tablesetting=M('setting');
        $where['setting_name']="money_doctor";
        $arr=$tablesetting->where($where)->getField("setting_values");
        return $arr;
    }
    
    /****
     *获得免责声明
     *
     *
     */
    private  function  getstatement(){
        $tablesetting=M('setting');
        $where['setting_name']="statement";
        $arr=$tablesetting->where($where)->getField("setting_values");
        return $arr;
    }
    

}