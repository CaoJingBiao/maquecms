<?php
namespace app\common\model;
use think\Db;
use think\helper\Time;
use think\Model;

/*数据统计*/
class ChartModel extends Model{

	#主要的办法#
	public static function main($table,$day="7"){

   	  //时间范围
   	  if(is_array($day)){

   	  	$firsttime = strtotime($day[0]);

        $lasttime = strtotime($day[1])+60*60*24;

   	  }else{

   	  	$firsttime = Time::daysAgo($day-1);

        $lasttime = strtotime(date('Y-m-d',strtotime('+1 day')))-1; //明天凌晨时间戳

   	  }


      $data = [];
      
      $num = 1;

      $time = $firsttime;

      while ($time < $lasttime) {
        
        $start=date('Y-m-d 00:00:00',$time); 

        $end=date('Y-m-d 23:59:59',$time); 

          $map = array(
            ['addtime', 'between time', [$start,$end] ],
          );

          $count=Db::name($table)->where($map)->count();

          array_push($data, $count);

          $num++;

          $time = $firsttime+60*60*24*($num-1);
  
        }
        
        return  json_encode($data);


    }

    #时间段#
   public static function time($day='10', $format='m-d'){

   	  //时间范围
   	  if(is_array($day)){

   	  	$firsttime = strtotime($day[0]);

        $lasttime = strtotime($day[1])+60*60*24;

   	  }else{
   	  	
   	  	$firsttime = Time::daysAgo($day-1);


        $lasttime = strtotime(date('Y-m-d',strtotime('+1 day')))-1; //明天凌晨时间戳

   	  }


      $data = [];
      
      $num = 1;

      $time = $firsttime;

      while ($time < $lasttime) {
        
        $date = date($format,$time); 

          array_push($data, $date);

          $num++;

          $time = $firsttime+60*60*24*($num-1);
  
        }
        
        return  json_encode($data);


    }


}