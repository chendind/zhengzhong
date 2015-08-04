<?php
namespace Home\Controller;
use Think\Controller;
class LetterController extends Controller {
    /**
     *获得私信列表
     *参数 start
     *参数 show
     */
    public function getletter(){
       if(!session('id')){
            $arr['state']="2001";//登录过期
        }
       else{
            $tableletter=M('letter');
            $start=I('post.start');
            $show=I('post.show');
            $where['letter_uid']=session('id');
            $where['letter_isdel']='0';
            
            $arr['letter']=$tableletter->field('letter_id,letter_title,letter_content,letter_time,letter_state')->where($where)->order('letter_time')->limit($start,$show)->select();
            $arr['count']=$tableletter->where($where)->count();
            $arr['state']="0";//成功
            
        }
        echo json_encode($arr);
    }
    
    
    
    
    
    /***
     *设置私信已读
     *参数id
     *
     */
    public function setread(){
        if(!session('id')){
            $arr['state']='2001';
        }
        else{
            $tableletter=M('letter');
            
            $where['letter_id']=I('post.id');
            $state['letter_state']='1';
           
            $tableletter->where($where)->save($state);
                $arr['state']='0';
            
            
            
            
        }
        echo json_encode($arr);
    }
    
    /**
     *
     *删除私信
     *参数：id
     *
     */
    public function setdel(){
      if(!session('id')){
         $arr['state']='2001';
      }
      else{
         $tableletter=M('letter');
         $where['letter_id']=I('post.id');
         $isdel['letter_isdel']='1';
         $tableletter->where($where)->save($isdel);
      //   $arr['ddd']=$tableletter->where($where)->find();
         $arr['state']='0';
      }
      echo json_encode($arr);
    }
    
    /**
     *
     *得到未读数量、
     *
     */
    public function getunreadcount(){
      session('id',1000000);
      if(!session('id')){
         $arr['state']='2001';
      }
      else{
         $tableletter=M('letter');
         $where['letter_id']=I('post.id');
         $where['letter_state']=0;
         $where['letter_isdel']=0;
        
         $arr['state']='0';
         $arr['count']=$tableletter->where($where)->count();
      }
      echo json_encode($arr);
    }
    
    
    
}