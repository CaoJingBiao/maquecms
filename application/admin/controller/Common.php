<?php
namespace app\admin\controller;

use think\Controller;
use think\Request;
use think\facade\Config;
use app\common\Oss;

class Common extends Controller{

    protected $request;
    public $className,$result;

    public function __construct($before = true){

          parent::__construct();

    }

    #空操作#
    public function _empty($name){

       echo "Not Data";

    }
  

    public function index(){
       
      echo "公共模块";

    }


  #文件上传#
  final public function fileupload(){

    if(request()->ispost()){
     
      $editorid=$this->request->param('editorid'); // 百度编辑器
      $width=$this->request->param('width'); //图片宽度
      $height=$this->request->param('height'); //图片高度
      $other=$this->request->param('other'); // 其他
      $isvalidate=$this->request->param('isvalidate'); // 格式验证
      $notwater=$this->request->param('notwater'); //任何参数不带水印
      $media =$this->request->param('media'); //媒体类型，文件后缀
      $type=$this->request->param('type');
      $size=$this->request->param('size');
      $catalog=$this->request->param("catalog","file");

      if(isset($editorid)){ //百度编辑器
        $file = request()->file('upfile');
      }else{
        $file = request()->file('file');
      }

      //文件上传路径
      //$file_up_path=ROOT_PATH . 'public' . DS . 'uploads'. DS .$catalog;
      $Env=new \think\facade\Env;

      $file_up_path= $Env::get('root_path').'/public/uploads/';
      
      //创建目录
      if(!is_dir($file_up_path)) mkdir($file_up_path,0777,true); 

      /*if(!empty($validate) && empty($isvalidate)){//验证
        $info = $file->validate(['size'=>15678000,'ext'=>$validate])->move($file_up_path);
      }else{
        $info = $file->move($file_up_path);
      }*/


      $validate = array();

      if(!empty($media)){

        $validate['ext'] = $media;
          
      } else if(!empty(Config::get('site.upload.file')) && empty($media)){
         $validate['ext'] = Config::get('site.upload.file');
      }

      if(!empty($size)){

        $validate['size'] = $size;
          
      }

      $info = $file->validate($validate)->move($file_up_path);
     
      //规则验证失败~
      if($info=== false){
        $this->error("上传验证出错");
      }

      $path="./uploads/" .str_replace('\\','/',$info->getSaveName());  

      //如果是图片
      if(in_array($info->getExtension(), ['jpeg','jpg','png','gif'] )){
          //修改图片宽高
          if(!empty($width) || !empty($height)){
            $image = \think\Image::open($path);
            $image->thumb($width, $height,\think\Image::THUMB_CENTER)->save($path);
          }

          $water=Config::get('site.water');

         //水印

        if($water['water']!='0' && empty($notwater)){
         
          if($water['water']=='img'){

              $image = \think\Image::open($path);
              $image->water(".".$water['wate_path'],$water['wate_position'],$water['wate_transparent'])->save($path);
          
          }else if($water['water']=='text'){

              $image = \think\Image::open($path);
              
              $font='PlutoBlack.otf';

              $image->text($water['wate_text'],$font,$water['text_size'],$water['text_color'],$water['wate_position'])->save($path);
              

          }

        }

      }


      //上传到oss
      if(Config::get('upload.type')=='OSS'){

          $Oss=new Oss;
          $filename=md5($path).".".$info->getExtension();
          $res=$Oss->LoadFile($filename,'.'.$path);
          $url=$res['oss-request-url'];
         
      }

      $path=substr($path,1);
     
      //编辑器上传
      if(!empty($editorid) ){

          if($info){

              $json=array(
                'originalName'=>$info->getInfo()['name'],
                'name'=>$info->getSaveName(),
                'url'=>isset($url)?$url:'/../../../../../..'.$path,
                'size'=>$info->getInfo()['size'],
                'type'=>".".$info->getExtension(),
                'state'=>"SUCCESS"
              );

              
              
          }else{ //失败提醒

              $data=array(
                'state'=>"ERROR"
              );

              echo json_encode($data);exit;
          }
        
      } else { //正常上传
          
          $json=array(
            'path'=>isset($url)?$url:$path,
            'status'=>'1',
            'filename'=>$info->getInfo()['name'], //文件原来的名称
            'msg' =>'上传成功~'
          );

      }

        //return json($file->getError());
        if(isset($url)){
          unset($info);
          unlink(".".$path);
        }
        echo json_encode($json);exit;


      }else{
        $this->error("当前页面不存在~");
      }
  
  }

  #删除文件#
  final public function removefile($file=""){
    
    $config=Config::get('site');

    if($config['delete']=='1'){
      
      if($config['type']=='本地'){
        @unlink($file);
      }else if($config['type']=='qiniu'){

      }else if($config['type']=='OSS'){

      }

    }

  }

  public function _request($method){

    if( strtolower($this->request->method()) == strtolower($method)) return $this;

    return $this->error('请求方式错误！');

  }

  public function _model($name='', $model="app\\common\\model\\"){

    
    if($name == '')  $name = $this->modelName;

    $className = $model.$name;
            
    $this->className = new $className ();

    return $this;

  }
  
  //页码
  public function _getPage($field=''){

    return  $this->className->getPage($this->request->param(),$field); 

  }

  public function _get(){

      return  $this->className->get($this->request->param()); 
  }
  //添加
  public function _add($data = ''){

      $param = $this->request->param();


      if(isset($param['status'])){
          $param['status'] = empty($param['status']) ? '0' : $param['status'];
      }

      if(!empty($data)){
          $param = array_merge($param,$data);
      }      

      $res= $this->className->allowField(true)->save($param);

      if($res == true){
          $this->success("添加成功~",url('lists'));
      } 

      $this->error("添加失败，请重试！"); 

  }
  //更新
  public function _update($data=''){

    $param = $this->request->param();

    if(!empty($data)){
        $param = array_merge($param,$data);
    }

    $res = $this->className->update($param);

    if($res == true){
        $this->success('信息修改成功');
    }

    $this->error('信息修改失败,请稍后再试！');

  }
  //删除
  public function _del(){

    $res = $this->className->destroy($this->request->param());

    if($res == true){
        $this->success('删除成功');
    }

    $this->error('删除失败,请稍后再试！');

  }
  //调用方法
  public function _action($func){

    if(empty($this->className) || empty($func) ) return true;

    $this->result = call_user_func(array($this->className, $fun));
    return $this;
  }

  public function _result($handle = false){
    
    if($handle === true){
        return $this->result;
    } 

    if( $this->result !== false){
        $this->success('操作成功~');
    }

    $this->error('操作失败~');
     
  }

  #数据列表#
    public function lists(){
   
        if($this->request->isGet()){

            if($this->request->isAjax()){
            
                return $this->_model()->_getPage();
             
            }
            
          return $this->fetch();

        }

    }

    #添加数据#
    public function add(){
        
        if($this->request->isGet()){

            return $this->fetch();

        }else{

            $this->_model()->_add();  

        }
    }
    
    #修改数据#
    public function edit(){
   
        if($this->request->isGet()){

            if($this->request->isAjax()){

                return  $this->_model()->_get();

            }

            return $this->fetch();

        }else if($this->request->isPut()){ 

            $this->_model()->_update();
        }
    }

    #删除数据#
    public function del(){

        if($this->request->isDelete()){
          
          $this->_model()->_del();

        }
    }

    #修改状态#
    public function up(){

        if($this->request->isPut()){

            $this->_model()->_update();

        }
    }


}
