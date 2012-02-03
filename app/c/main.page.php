<?php 
/**
 * 登录
 * @author 齐迹  email:smpssadmin@gmail.com
 *
 */
class c_main extends base_c {
	
	function pageindex($inPath){
		if(!$this->isLogin())
			return $this->pagelogin($inPath);
		return $this->render('main/index.html',$this->params);
	}
	
	function pagelogin($inPath) {
		$urlParams = $this->getUrlParams($inPath);
		if(!$_POST){
			$this->params['head_title'] = "smpss";
			return $this->render("main/login.html",$this->params);
		}else{
			$_POST = base_Utils::shtmlspecialchars($_POST);
			session_start();
			if(SCaptcha::check($_POST['captcha'])){
				$modelAdmin = new m_admin();
				$loginInfo = $modelAdmin->checkLogin($_POST['username'],$_POST['pwd'],(int)$_POST['timeout']);
				if($loginInfo){
					$this->redirect($this->createUrl('/'));
				}else{
					$this->ShowMsg("用户名或者密码错误！");
				}
			}else{
				$this->ShowMsg("验证码错误！");
			}
		}
	}
	
	function pagelogout($inPath) {
		$cookie['key'] = '';
		base_Utils::ssetcookie($cookie,-1);
		return $this->ShowMsg("成功退出！",'','','1');
	}
	
	function pagecaptcha(){
		session_start();
		$cap = new SCaptcha();
		$code = $cap->CreateImage();
	}
}
?>