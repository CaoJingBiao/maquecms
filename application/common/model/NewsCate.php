<?php
namespace app\common\model;

use think\Model;

class NewsCate extends CommonModel{

  	protected $pk = 'cateid';
  	public $arr=array();

  	#所有分类数据#
	public function GetCateListData($pid = '0'){

		$catelist = NewsCate::where('pid',$pid)->order('catepx desc')->field('cateid,pid,catepx,catethumb,pid,catename,url,template,addtime,isnav,status')->select()->toarray();

		self::getcatelist($catelist);
			
		return $this->arr;
	}
	#分类数据#
	private function getcatelist($list,$num="0"){
		
		if(is_array($list)){

			$num++;

			foreach ($list as $k => $v) {

				$v['catename'] = html_entity_decode(str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;",$num))."|------ ".$v['catename'];

				array_push($this->arr,$v);

				$arr = NewsCate::where('pid',$v['cateid'])->order('catepx desc')->field('cateid,pid,catepx,catethumb,pid,catename,url,template,addtime,isnav,status')->select()->toarray();

				$list[$k]["list"]=self::getcatelist($arr,$num);
				
			}

			return $list;

		}

		return $list;
	}	
	

}