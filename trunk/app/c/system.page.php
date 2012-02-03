<?php 
/**
 * 系统设置
 * @author 齐迹  email:smpssadmin@gmail.com
 *
 */
class c_system extends base_c {
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
		return $this->render('system/index.html',$this->params);
	}
	
	function pagesetting($inPath){
		//$action = array('all'=>0,'action'=>array('system_index','system_setting'),'menu'=>array('system'=>array('index'=>'系统信息','setting'=>'系统配置','rights'=>'权限配置')));
		//echo serialize($action);
		//var_dump($this->params);
		return $this->render('system/setting.html',$this->params);
	}
	
	function pagerights($inPath){
		$url = $this->getUrlParams($inPath);
		$groupObj = new m_group();
		$gid = (int)$url['gid'];
		$this->params['gid'] = $gid;
		if(!$gid){
			$this->params['group'] = $groupObj->select()->items;
			return $this->render('system/rights.html',$this->params);
		}else{
			if(!$_POST){
				if($gid){
					$this->params['rights'] = $groupObj->selectOne("gid = {$gid}");
					$this->params['action'] = unserialize($this->params['rights']['action_code']);
					print_r($this->params['action']);
					return $this->render('system/rightsshow.html',$this->params);
				}
				$this->ShowMsg("用户组不存在！");
			}else{
				$action_code = $this->creatRights($_POST);
				$groupObj->update("gid = {$gid}","action_code = '{$action_code}'");
				$cacheName = "action_code_group_".$gid;
				$cache = SCache::getCacheEngine('file');
				$cache->init(array("dir"=>SlightPHP::$appDir."/cache","depth"=>3));
				$rs = $cache->del($cacheName);
				$this->ShowMsg("编辑成功！",$this->createUrl('/system/rights'),'',1);
			}
		}
	}
	
	function pageaddrights($inPath){
		if(!$_POST['group_name']){
			return $this->render('system/addrights.html',$this->params);
		}else{
			$item = array();
			$item['group_name'] = base_Utils::shtmlspecialchars($_POST['group_name']);
			if($item['group_name']){
				$groupObj = new m_group();
				$res = $groupObj->selectOne("group_name='{$item['group_name']}'",'gid');
				if($res) $this->ShowMsg('用户组名称已经存在！');
				$item['action_code'] = $this->creatRights($_POST);
				$rs = $groupObj->insert($item);
				if($rs){
					$this->ShowMsg('添加成功',$this->createUrl('/system/rights'),'',1);
				}else{
					$this->ShowMsg('添加失败，请重试！错误原因：'.$groupObj->getError());
				}
			}
			$this->ShowMsg('用户组名称不能够为空！');
		}
	}
	
	private function creatRights($post){
		$post = (array)base_Utils::shtmlspecialchars($post);
		$action = $menu = array();
		foreach ($post as $key=>$val){
			if(in_array($key, array('system','account','member','category','goods','purchase','sale','statistics'))){
				$_temp = array();
				foreach ($val as $v){
					$vArr = explode(':', $v);
					$_temp[$vArr[1]] = $vArr[0];
					$action[] = $key.'_'.$vArr[1];
				}
				$menu[$key] = $_temp;
			}
		}
		return serialize(array('all'=>0,'action'=>$action,'menu'=>$menu));
	}
}
?>