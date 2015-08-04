<?php
namespace Home\Controller;
use Think\Controller;
class NewsController extends Controller {
    public function getNews()
    {
        $News=M("News");
        $from=I("post.from");
        $num=I("post.num");
        $detail=$News->order("news_time desc")->limit($from,$num)->select();
        $cou=$News->count();
        if($detail)
        {
            $arr["detail"]=$detail;
            $arr["count"]=$cou;
            $arr["state"]="0";//success
        }
        else
        {
            $arr["state"]="30088";//network error
        }
        echo json_encode($arr);
    }
}
?>