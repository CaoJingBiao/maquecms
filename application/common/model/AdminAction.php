<?php
namespace app\common\model;

use think\Model;

class AdminAction extends CommonModel{

  	protected $pk = 'actionid';

    #所有权限操作分组#
	public function ActionGroupList(){

		$list=AdminAction::where(['type'=>"控制器",'status'=>"1"])->order('px desc')->select()->toarray();
		
		foreach ($list as $k => $v) {
			$list[$k]['list']=AdminAction::where(['type'=>"操作",'status'=>"1",'pid'=>$v['actionid']])->order('px desc')->select()->toarray();
		}

		return $list;
	}


}