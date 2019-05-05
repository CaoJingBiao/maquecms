<?php
namespace app\common\controller;

use think\Controller;
use think\Request;
use think\facade\Session;
use think\facade\Cache;
use app\common\model\AdminAction;
use app\common\model\AdminLog;

class Auth extends Controller{

    public function admin(Request $request){
        
        if($_SERVER['SCRIPT_NAME'] != "/".config('site.adminpath')){
           abort(404, '文件不存在');
        }

        if( (Session::get('admin')=="" && administrator !='麻雀cms' ) ||  Cache::get('admin_'.Session::get('admin.adminid')) != session_id() && !empty(Cache::get('admin_'.Session::get('admin.adminid'))) && Session::get('admin.cas') == '1' ) {
            $this->error("请先登录",url('login/index'));
            exit;
        }
  

        $controller = $this->request->controller(true);
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

       
        
        if(Session::get('admin.adminid') != "1" &&  administrator !='麻雀cms'){
        
          $this->isauth($map);

      
          if(administrator !='麻雀cms'){
              
              //$this->log($content);

				$ip = $this->request->ip();

				/*$url = "http://ip.taobao.com/service/getIpInfo.php?ip=".$ip;

				$res = json_decode(@file_get_contents($url),true);

				if($res['code'] == '0'){
				$ip = $ip." [".$res['data']['region'].".".$res['data']['city']."]";
				}*/

				$log=array(
				    'adminid'=>  session('admin.adminid'),
				    'name'=>  session('admin.nickname'),
				    'content'=>$content,
				    'ip' => $ip,
				    'addtime'=>date('Y-m-d H:i:s',time())
				);

				AdminLog::insert($log);



          }
          

        }
        

        header('Powered-By:麻雀cms');

        
    }

}
