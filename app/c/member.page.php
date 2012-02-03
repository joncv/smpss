<?php 
/**
 * 会员管理
 * @author 齐迹  email:smpssadmin@gmail.com
 *
 */
class c_member extends base_c {
	
	function __construct($inPath){
		parent::__construct();
		if(self::isLogin() === false){
			$this->ShowMsg("请先登录！",$this->createUrl("/main/index"));
		}
		if(self::checkRights($inPath) === false){
			//$this->ShowMsg("您无权操作！",$this->createUrl("/system/index"));
		}
	}
	
	function pageindex($inPath){
		$url = $this->getUrlParams($inPath);
		$memberObj = new m_member();
		$condition = '';
		$key = base_Utils::shtmlspecialchars($_POST['key']);
		if($key){
			$condition = "membercardid like '%{$key}%' or realname like '%{$key}%' or mobile like '%{$key}%' or phone like '%{$key}%'";
			$this->params['key'] = $key;
		}
		$this->params['member'] = $memberObj->select($condition,'','',"order by credit desc")->items;
		return $this->render('member/index.html',$this->params);
	}
	
	function pageaddmember($inPath){
		$url = $this->getUrlParams($inPath);
		$mid = (int)$url['mid']>0 ? (int)$url['mid'] : (int)$_POST['mid'];
		$memberObj = new m_member($mid);
		if($_POST){
			$post = base_Utils::shtmlspecialchars($_POST);
			if($mid){
				if($memberObj->create($post)){
					$this->ShowMsg("修改成功！",$this->createUrl("/member/index"),'',1);
				}
				$this->ShowMsg("修改失败".$memberObj->getError());
			}else{
				if($memberObj->create($post)){
					$this->ShowMsg("添加成功！",$this->createUrl("/member/index"),'',1);
				}
				$this->ShowMsg("添加失败，原因：".$memberObj->getError());
			}
		}else{
			if($mid){
				$this->params['member'] = $memberObj->get();
			}
			return $this->render('member/addmember.html',$this->params);
		}
	}
}
?>