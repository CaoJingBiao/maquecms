<?php
namespace app\admin\model;

use think\Model;
use think\Db;
use traits\controller\Jump;

class BaseModel extends Model{

	use Jump;

	/*
	* 分页接口
	* @access public
	* @param  string    $table 表名
	* @param  string    $param 分页请求条件
	* @param  string    $key   like查询字段
	* @param  string    $order 默认排序方式
	* @param  string    $time  默认时间查询字段
	* @param  string    $type  返回数据类型
	* @return mixed
	*/
    public static function Page($table,$param,$key="",$time="addtime",$order="addtime desc",$return="json"){

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


		$data=array(
			'data'=>Db::name($table)->where($map)->page("$page,$limit")->order($order)->select(),
			'code'=>"0",
			'count'=>Db::name($table)->where($map)->count(),
			'msg'=>"请求成功"
		);

		if($return=="json"){
		
			return json_encode($data);
		}

		return $data;

		

		//echo Db::getLastSql();exit;
		

    }

    /*
	* 添加数据
	* @access public
	* @param  string    $table 表名
	* @param  array     $data  添加的数据
	* @return bool
	*/
    public static function DataIns($table,$data,$addtime=''){

    	if(!empty($addtime)){
    		$data[$addtime] =  date('Y-m-d H:i:s',time());
    	}

    	return Db::name($table)->strict(false)->insert($data);

    }

    /*
	* 更新数据
	* @access public
	* @param  string    $table 表名
	* @param  array     $data  修改的数据
	* @param  array     $where  条件
	* @return bool
	*/
    public static function DataUp($table,$data,$where="",$uptime=""){

    	if(!empty($uptime)){

    		$data[$uptime]=date('Y-m-d H:i:s');

    	}
    	
    	return Db::name($table)->where($where)->strict(false)->update($data);

    }

    /*
	* 删除数据
	* @access public
	* @param  string    $table 表名
	* @param  array     $where  条件
	* @return bool
	*/
    public static function DataDel($table,$where){

    	return Db::name($table)->where($where)->delete();

    }

    /*
	* 主键批量删除数据
	* @access public
	* @param  string    $table 表名
	* @param  array     $keyid 主键id
	* @return bool
	*/
	public static function DataDelKey($table,$keyid){

		return Db::name($table)->delete($keyid);

    }

    #查询数据#
    public static function DataAll($table,$where="",$field="",$order=""){
    	return Db::name($table)->where($where)->field($field)->order($order)->select();
    }

    /*
	* 查询一行数据
	* @access public
	* @param  string    $table 表名
	* @param  array     $where  条件
	* @return bool
	*/
    public static function DataFind($table,$where,$field="*"){

    	return Db::name($table)->where($where)->field($field)->find();

    }

    /*
	* 统计数据数量
	* @access public
	* @param  string    $table 表名
	* @param  array     $where  条件
	* @return bool
	*/
    public static function DataCount($table,$where,$field=""){

    	return Db::name($table)->where($where)->count($field);

    }
    /*
	* 查询单个字段
	* @access public
	* @param  string    $table 表名
	* @param  array     $where  条件
	* @return bool
	*/
    public static function DataValue($table,$where,$field){

    	return Db::name($table)->where($where)->value($field);

    }

    /*
    * 原生批量更新数据
	* @access public
	* @param  string    $table 	表名
	* @param  string    $set  	更新字段和值
	* @param  string    $key  	主键
	* @param  string    $range  更新范围
	* @return bool
    */
    public function query_update($table, $set, $key, $range){

    	$query = "UPDATE ".$table." SET ".$set." WHERE  ".$key." in (".$range.");";

    	return Db::execute($query);

    }

    /*
    * 原生批量删除数据
	* @access public
	* @param  string    $table 	表名
	* @param  string    $key  	主键
	* @param  string    $range  更新范围
	* @return bool
    */
    public function query_delete($table, $set, $key, $range){

    	$query = "DELETE FROM ".$table." WHERE  ".$key." in (".$range.");";

    	return Db::execute($query);

    }

    



    
    
}
