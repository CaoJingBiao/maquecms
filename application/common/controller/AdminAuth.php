<?php
namespace app\common\controller;

use think\Controller;
use think\Request;
use think\facade\Session;
use think\facade\Cache;
use app\common\model\AdminAction;
use app\common\model\AdminLog;
use app\common\model\AdminRole;

class AdminAuth extends Controller{


    public function run(Request $request){

        if($_SERVER['SCRIPT_NAME'] != "/".config('site.adminpath')){
           abort(404, '文件不存在');
        }

        $controller = $this->request->controller(true);

        if($controller == 'login'){
            return true;
        }

        if( (Session::get('admin')=="" && administrator !='麻雀cms' ) ||  Cache::get('admin_'.Session::get('admin.adminid')) != session_id() && !empty(Cache::get('admin_'.Session::get('admin.adminid'))) && Session::get('admin.cas') == '1' ) {
            $this->error("请先登录",url('login/index'));
            exit;
        }
  

        $action = $this->request->action(true);

        $map=array(
          'controller'=>$controller,
          'action'=>$action,
        );

        $title=AdminAction::where($map)->cache(true)->value('name');

        if($this->request->isGet()){
          $method="访问";
          if(!empty($this->request->param())){
            $param=" , 参数：".json_encode($this->request->param());
          }
        }
        if($this->request->ispost()){
          $method="提交";
        }

        $param = '';

        if($this->request->isajax()){
          
          $method="操作";
          
          if(!empty($this->request->param())){
            $param=" , 参数：".json_encode($this->request->param());
          }
        }

        $content="[".$method."_".$title."] , 控制器：".$controller."/".$action.$param;

        if(Session::get('admin.adminid') != "1" &&  !defined(administrator) ){
          

          $this->isauth($map);

        }


        if(!defined(administrator)){

            $log=array(
                'adminid'=>  session('admin.adminid'),
                'name'=>  session('admin.nickname'),
                'content'=>$content,
                'ip' => $this->request->ip(),
                'addtime'=>date('Y-m-d H:i:s',time())
            );

            AdminLog::insert($log);

        }

        header('Powered-By:麻雀cms');
        
    }

    #权限验证#
    private function isauth($data){

      $roleid=session('admin.roleid');

      $auth=Cache::get('role_auth_'.$roleid);

        if(empty($auth)){

          $map=array(
            'roleid'=>$roleid,
            'status'=>'1'
          );

          $auth=AdminRole::where($map)->value('auth');
       
          Cache::set('role_auth_'.$roleid,$auth);
        }

        $actionid = AdminAction::where('action',$data['action'])->cache()->value('actionid'); 

        if(strpos($auth,$actionid) === false){

          $this->error("访问权限不足~");

        }

        /*if(!is_array($auth[$data['controller']]) || !in_array($data['action'],$auth[$data['controller']]) ){
            $this->error("访问权限不足~");
        }*/

    }

}
