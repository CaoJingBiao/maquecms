<?php
namespace app\admin\controller;

use think\Db;
use think\facade\Cache;
use think\facade\Session;
use app\admin\model\AuthModel;
use app\common\model\AdminList;
use app\common\model\AdminRole;
use app\common\model\AdminLog;
use app\common\model\AdminAction;

#@管理员管理@#
class Auth extends Common{

	public $model;

    protected function initialize(){

        $this->model = new \app\admin\model\AuthModel();
    }

	#管理员列表#
	public function adminlist(){
	
		if($this->request->isGet()){

			if($this->request->isAjax()){
              
                return $this->_model('AdminList')->_getPage('username|nickname');
             
            }
			
			return $this->fetch();
		}

	}

	#添加管理员#
	public function adminadd(){

		if($this->request->isGet()){

			if($this->request->isAjax()){
				return AdminRole::where('status','1')->order('px desc')->select();
			}
	
			return $this->fetch();

		}else{

			$param = $this->request->param();
			$param['password'] = md5($param['password']);

			$this->_model('AdminList')->_add($param); 
			
		}
	}
	#编辑管理员信息#
	public function adminedit(){

		if($this->request->isGet()){

			$adminid=$this->request->param('adminid');

			if($adminid=="1"){
				$this->error("超级管理员不可修改！！");
			}

			if($this->request->isAjax()){

				$adminid = $this->request->param('adminid');

                $info =  AdminList::get($adminid)->hidden(['password']); 

                $info['rolelist'] = AdminRole::where('status','1')->order('px desc')->select();

                return $info;
			}

			return $this->fetch();

		}else if($this->request->isput()){

			$param = $this->request->param();
			
			if(empty($param['password'])){
				unset($param['password']);
			}else{
				$param['password'] = md5($param['password']);
			}

			$this->_model('AdminList')->_update($param); 
			
		}
	}


	#修改管理员状态#
	public function adminup(){

		if($this->request->isput()){

			$this->_model('AdminList')->_update(); 

		}

	}

	#删除管理员#
	public function admindel(){

		if($this->request->isdelete()){

			$this->_model('AdminList')->_del(); 

		}
		
	}

	#角色列表#
	public function rolelist(){

		if($this->request->isGet()){

			if($this->request->isAjax()){
     
                return $this->_model('AdminRole')->_getPage('name');
             
            }

			return $this->fetch();
		}

	}

	#添加角色#
	public function roleadd(){

		if($this->request->isGet()){

			if($this->request->isAjax()){

				$AdminAction = new AdminAction();
                
                return  $AdminAction->ActionGroupList();

			}
			
			return $this->fetch();

		}else{

			$data=$this->request->post();
			
			$AdminRole = new AdminRole();

			$AdminAction = new AdminAction();

			if($AdminRole::where('name',$data['name'])->count() > 0 ){
				$this->error("当前角色名称已经存在~");
			}

			$auth = ',';

			foreach ($data['auth'] as $k => $v) {
				if(count($v['list']) > 0){
					foreach ($v['list'] as $key => $val) {
						$auth .= $val.',';
						$data['auth'][$k]['list'][$key] = AdminAction::where('actionid',$val)->field('actionid,name,controller,action,type')->find();
					}
				}else{
					unset($data['auth'][$k]);
				}

			}

			$data['menu'] = array(
				'menu' => $data['auth'],
				'value' => $auth,
			);

			$data['menu'] = json_encode($data['menu']);

			$data['auth'] = $auth;
			
			$data['status'] = empty($data['status']) ? '0' : $data['status'];

            $AdminRole = new AdminRole();

            $res= $AdminRole->allowField(true)->save($data);

			if($res==true){

				$this->success("角色信息添加成功~",url('rolelist'));

			}
				
			$this->error("角色信息添加失败!");
		

			
		}
	}

	#编辑角色信息#
	public function roleedit(){

		if($this->request->isGet()){

			if($this->request->isAjax()){

                $roleid=$this->request->param('roleid');

                $info =  AdminRole::get($roleid); 

                $AdminAction = new AdminAction();
                
                $info['actionList'] = $AdminAction->ActionGroupList();

				return $info; 

            }

			return $this->fetch();

		}else if($this->request->isput()){

			$data=$this->request->put();
			
			$AdminRole = new AdminRole();

			$AdminAction = new AdminAction();

			$map = array(
				['name','=',$data['name']],
    			['roleid','neq',$data['roleid']]
			);

			if($AdminRole::where($map)->count() > 0 ){
				$this->error("当前角色名称已经存在~");
			}

			$auth = ',';

			foreach ($data['auth'] as $k => $v) {
				if(count($v['list']) > 0){
					foreach ($v['list'] as $key => $val) {
						$auth .= $val.',';
						$data['auth'][$k]['list'][$key] = AdminAction::where('actionid',$val)->field('actionid,name,controller,action,type')->find();
					}
				}else{
					unset($data['auth'][$k]);
				}

			}

			$data['menu'] = array(
				'menu' => $data['auth'],
				'value' => $auth,
			);

			$data['menu'] = json_encode($data['menu']);

			$data['auth'] = $auth;
			
			$data['status'] = empty($data['status']) ? '0' : $data['status'];

            $AdminRole = new AdminRole();

            $res = AdminRole::update($data);

			if($res == true){

				Cache::rm('role_auth_'.$data['roleid']);

				$this->success("角色信息修改成功~",url('rolelist'));

			}
				
			$this->error("角色信息修改失败!");
			
		}
	}


	#删除角色#
	public function roledel(){


		if($this->request->isdelete()){

			$this->_model('AdminRole')->_del();

		}
		
	}

	#修改角色状态#
	public function roleup(){

		if($this->request->isput()){

			$this->_model('AdminRole')->_update();

		}

	}

	#权限列表#
	public function actionlist(){

		if($this->request->isGet()){

			if($this->request->isAjax()){

                return $this->_model('AdminAction')->_getPage('name|controller|action');
             
            }

			return $this->fetch();
		}

	}

	#添加权限信息#
	public function actionadd(){

		if($this->request->isGet()){

			$this->assign('list',$this->model->ActionList(['status'=>'1','pid'=>'0'],"actionid,name"));
			return $this->fetch();

		}else{

			$data=$this->request->post();


			$res=$this->model->ActionAdd($data);

			if($res==true){
				$this->success("权限信息添加成功~",url('actionlist'));
			}
				
			$this->error("权限信息添加失败!");
			
		}

		/*if($this->request->isGet()){

            return $this->fetch();

        }else{

            $post=$this->request->post();

            $post['status'] = empty($post['status']) ? '0' : $post['status'];

            $BannerList = new BannerList();

            $res= $BannerList->allowField(true)->save($post);

            if($res == true){
                $this->success("添加成功~",url('lists'));
            } 

            $this->error("添加失败，请重试！"); 

        }*/

	}

	#编辑权限信息#
	public function actionedit(){

		if($this->request->isGet()){

			$actionid=$this->request->param('actionid');

			$info = $this->model->ActionInfo($actionid);

			if(empty($info)){
				$this->error("当前权限信息不存在~");
			}
			$this->assign('info',$info);

			$this->assign('list',$this->model->ActionList(['status'=>'1','pid'=>'0'],"actionid,name"));

			return $this->fetch();

		}else if($this->request->isput()){

			$data=$this->request->put();
	
			$res = $this->model->ActionEdit($data);

			if($res==true){
				$this->success("权限信息修改成功~",url('actionlist'));
			}
				
			$this->error("权限信息修改失败!");
			
		}
	}

	#删除权限信息#
	public function actiondel(){

		if($this->request->isdelete()){

			$actionid=$this->request->delete('actionid');

			$res = $this->model->ActionDel($actionid);

			if($res==true){
				$this->success("权限信息删除成功~",url('actionlist'));
			}
				
			$this->error("权限信息删除失败!");
			
		}

		
	}

	#修改权限状态#
	public function actionup(){

		if($this->request->isPut()){

			$this->_model('AdminAction')->_update();

        }

	}

	#管理员日志#
	public function loglist(){

		if($this->request->isGet()){

			if($this->request->isAjax()){

                return $this->_model('AdminLog')->_getPage();
             
            }

			return $this->fetch();
		}
	}

	#编辑角色菜单#
	public function rolemenu(){

		if($this->request->isGet()){

			if($this->request->isAjax()){
					
				$roleid=$this->request->param('roleid');

                $info =  AdminRole::get($roleid); 

                $info['menu'] = json_decode($info['menu'],true);

                return $info;

			}


			return $this->fetch();

		}else{

			$data = $this->request->post();
		
			$menu = ',';

			foreach ($data['menu'] as $k => $v) {
				if(count($v['list']) > 0){
					foreach ($v['list'] as $key => $val) {
						$menu .= $val.',';
					}
				}

			}

			$AdminRoleInfo= AdminRole::get($data['roleid']);

			$JsonMenu = json_decode($AdminRoleInfo['menu'],true);

			$data['menu'] = array(
				'menu' => $JsonMenu['menu'],
				'value' => $menu,
			);

			$AdminRoleInfo->menu = json_encode($data['menu']);

			$res = $AdminRoleInfo->save();

			if($res == true){

				Cache::rm('role_menu_'.$post['roleid']);

				$this->success("角色菜单信息修改成功~",url('rolelist'));
			}

			$this->error("角色菜单信息修改失败!");
	
		}
	}


	#刷新权限#
	public function index(){
			
		//exit("请删除本行注释");
		//1.获取所有控制
		$dir="./../application/admin/controller/";
		$data=array();
		$con_arr=array("Common","error","Login"); //排除
		
		foreach (scandir($dir) as $k => $v) {
			if(preg_match('/[A-Za-z]+.php/', $v,$res)/* && !in_array($v,$con_arr)*/){
				//if(!in_array(trim($v),$con_arr)){
				 $controller=substr($v, 0,-4);

				 if(!in_array($controller,$con_arr)){

					//2.获取所有控制器
					
					$cont=file_get_contents("./../application/admin/controller/".$controller.".php");

					preg_match("/#@(.*?)@#/", $cont, $res);

					$map=array(
						'controller'=>$controller,
						'type'=>'控制器'
					);
		
					$actionid=AdminAction::where($map)->value('actionid');
					
					if(empty($actionid)){
						$insert_data=array(
							'pid'=>"0",
							'name'=>$res[1],
							'controller'=>$controller,
							'type'=>'控制器',
							'addtime'=>date('Y-m-d H:i:s')
						);
						$actionid=AdminAction::insertGetId($insert_data);
					}else{
						$up_data=array(
							'name'=>$res[1],
							'uptime'=>date('Y-m-d H:i:s')
						);
						AdminAction::where($map)->update($up_data);
					}

					preg_match_all("/#(.*?)#(?:.|\n|\r\n)(.*?)public function (.*?)\(/", $cont, $arr);

					//preg_match_all("/#(.*?)#\r\n(.*?)public function (.*?)\(/", $cont, $arr);
					if(!empty($arr)){
						for($i=0;$i<count($arr[1]);$i++){
							
							$map2=array(
								'controller'=>$controller,
								'action'=>$arr[3][$i],
								'type'=>'操作',
							);
							$action_count=AdminAction::where($map2)->count();
							if($action_count>0){

								$action_update_data=array(
									'name'=>$arr[1][$i],
									'value'=>$controller."/".$arr[3][$i],
									'uptime'=>date('Y-m-d H:i:s')
								);
								AdminAction::where($map2)->update($action_update_data);
							
							}else{

								$insert_data=array(
									'pid'=>$actionid,
									'name'=>$arr[1][$i],
									'controller'=>$controller,
									'action'=>$arr[3][$i],
									'value'=>$controller."/".$arr[3][$i],
									'type'=>'操作',
									'addtime'=>date('Y-m-d H:i:s')
								);

								AdminAction::insert($insert_data);
							}
							
						}
						
					}

					
					//array_push($data,substr($v, 0,-4));
				}
			}
		}
		$this->success("处理结束");
		
	}

	#修改密码#
    public function modifypd(){
        if($this->request->isGet()){

            return $this->fetch();

        } else {

          $post=$this->request->param();
          
          $res = AdminList::where(['adminid'=>session('admin.adminid')])->update(['password'=>md5($post['pass1'])]);

          if($res==true){

	            Session::clear();

	            $this->success("密码修改成功，请重新登录",url('login/index'));

          }

            $this->error("密码修改失败，请重试！！");

        }


    }
   



}