<?php
namespace app\common\model;
use think\Db;
use think\helper\Time;
use think\Model;

/*数据统计*/
class UserListModel extends Model{

	#主要的办法#
	public static function main($table,$day="7",$start="",$end="",$num="1"){
   

      if(!empty($day)){

        $firsttime=Time::daysAgo($day-1);

        $lasttime=strtotime(date('Y-m-d',strtotime('+1 day')))-1; //明天凌晨时间戳

      }else if(!empty($start) && !empty($end)){ //只有开始时间

        $firsttime=strtotime($start);
        $lasttime=strtotime($end)+60*60*24;

      }

      $data = [];
      
      $time = $firsttime+60*60*24*($num-1);

      while ($time < $lasttime) {
        
        $start=date('Y-m-d 00:00:00',$time); 
        
        $end=date('Y-m-d 23:59:59',$time); 
        
        //array_push($data['day'],date('m-d',$time));

          $map = array(
            ['addtime', 'between time', [$start,$end] ],
          );

          print_r( UserList::get('1') );exit;
          
          $count=Db::name($table)->where($map)->count();

          array_push($data, $count);

          $num++;

          $time = $firsttime+60*60*24*($num-1);
  
        }
        
        return  json_encode($data);


    }


}