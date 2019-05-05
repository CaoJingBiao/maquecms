<?php
namespace app\common\model;

use think\Db;
use think\Model;

class CommonModel extends Model{

  protected $autoWriteTimestamp = 'timestamp';
  protected $createTime = 'addtime';
  protected $updateTime = 'uptime';
  protected $type = [
        'status'    =>  'integer',
  ];


    /*
  * 分页接口,默认获取表名
  * @access public
  * @param  string    $param 分页请求条件
  * @param  string    $key   like查询字段
  * @param  string    $order 默认排序方式
  * @param  string    $time  默认时间查询字段
  * @return mixed
  */
  public  function getPage($param,$key="",$time="addtime",$order="addtime desc"){

      $page=!empty($param['page'])?$param['page']:"1";
      $limit=!empty($param['limit'])?$param['limit']:"10";

      $map="";

      unset($param['page']);
      unset($param['limit']);

      //时间查询
      if(!empty($param['start']) && empty($param['end'])){
        
        $map[]=[$time,'>=',$param['start']];
        

      } else if(!empty($param['end']) && empty($param['start'])){

        $map[]=[$time,'<=',$param['end']];
        

      } else if(!empty($param['start']) && !empty($param['end'])){

        $map[]=[$time,'between',[$param['start'],$param['end']]];
        
      }


      //关键字查询
      if(!empty($param['keyword'])){
        $map[]=array($key,'like',"%".$param['keyword']."%");
        unset($param['keyword']);
      }

      unset($param['end']);
      unset($param['start']);

      if(!empty($param)){
        foreach ($param as $k => $v) {
          if(!empty($v)){
            $map[]=array($k,'=',$v);
          }
        }
      }

      //状态查询
      /*if(!empty($param['status'])){
        $map[]=array('status','=',$param['status']);
      }*/

      $table = ($this->db(false))->getTable();

      $data=array(
        'data'=>Db::table($table)->where($map)->page("$page,$limit")->order($order)->select(),
        'code'=>"0",
        'count'=>Db::table($table)->where($map)->count(),
        'msg'=>"请求成功"
      );


      return $data;
  

    }


}