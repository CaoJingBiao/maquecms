<?php
namespace app\common\controller;

use \think\controller;
use \Lcobucci\JWT\Builder;
use \Lcobucci\JWT\Parser;
use \Lcobucci\JWT\Signer\Hmac\Sha256;


class Token extends Controller{

	//秘钥
	public $secret = '麻雀内容管理';


	//设置 token
    public function SetToken($id){
        
        $builder = new Builder();
		$signer  = new Sha256();

		//设置header和payload，以下的字段都可以自定义
		$builder->setIssuer("www.baidu.com") //发布者
		        ->setAudience("www.baidu.com") //接收者
		        ->setId("麻雀内容管理", true) //对当前token设置的标识
		        ->setIssuedAt(time()) //token创建时间
		        ->setExpiration(time() + 60*60*24) //过期时间
		        ->setNotBefore(time()) //当前时间在这个时间前，token不能使用
		        ->set('id', $id); //自定义数据

		//设置签名
		$builder->sign($signer, $this->secret);
		//获取加密后的token，转为字符串
		return (string)$builder->getToken();

    } 

    //验证过 token
    public function GetToken($token, $field=''){

    	$signer  = new Sha256();

    	try {
		    //解析token
		    $parse = (new Parser())->parse($token);
		    //验证token合法性
		    if (!$parse->verify($signer, $this->secret)) {
		       return 'token 错误';
		    }

		    //验证是否已经过期
		    if ($parse->isExpired()) {
		        return 'token 已过期';
		    }

		
		    if(empty($field)){
		    	return $parse->getClaims();
		    }

		    return $parse->getClaim($field);


		} catch (Exception $e) {
		   return 'token 错误';
		}


    }



}
