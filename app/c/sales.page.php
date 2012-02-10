<?php
/**
 * 销售管理
 * @author 齐迹  email:smpssadmin@gmail.com
 *
 */
class c_sales extends base_c {
	function __construct($inPath) {
		parent::__construct ();
		if (self::isLogin () === false) {
			$this->ShowMsg ( "请先登录！", $this->createUrl ( "/main/index" ) );
		}
		if (self::checkRights ( $inPath ) === false) {
			//$this->ShowMsg("您无权操作！",$this->createUrl("/system/index"));
		}
	}
	
	function pageindex($inPath) {
		$url = $this->getUrlParams ( $inPath );
		return $this->render ( 'sale/index.html', $this->params );
	}
	
	function pagesales($inPath) {
		$url = $this->getUrlParams ( $inPath );
		session_start ();
		$info = $_SESSION ['goodsInfo'];
		if ($url ['ac'] == "del") {
			$_SESSION ['goodsInfo'] = "";
			$this->ShowMsg ( "操作成功！", $this->createUrl ( "/sales/sales" ), 2, 1 );
		}
		if ($_POST) {
			$goods_sn = base_Utils::getStr ( $_POST ['goods_sn'] );
			$num = ( float ) $_POST ['num'] ? ( float ) $_POST ['num'] : 1;
			$ishas = 0;
			if (is_array ( $info )) {
				foreach ( $info as $k => $v ) {
					if ($v ['goods_sn'] == $goods_sn) {
						$info [$k] ['num'] += $num;
						$ishas = 1;
						if ($info [$k] ['num'] > $v ['stock']) {
							$info [$k] ['num'] -= $num;
							$this->ShowMsg ( "该商品库存不足！", $this->createUrl ( "/sales/sales" ) );
						}
					}
				}
			}
			if (! $ishas) {
				$goodsObj = new m_goods ();
				$goods = $goodsObj->getSalePrice ( $goods_sn );
				if (! $goods)
					$this->ShowMsg ( "商品信息不存在", $this->createUrl ( "/sales/sales" ), 1 );
				if ($num > $goods ['stock'])
					$this->ShowMsg ( "该商品库存不足！", $this->createUrl ( "/sales/sales" ) );
				$goods ['num'] = $num;
				$info [] = $goods;
			}
			$_SESSION ['goodsInfo'] = $info;
			$this->redirect ( $this->createUrl ( "/sales/sales" ) );
		}
		$total = $discount = 0;
		if (is_array ( $info )) {
			foreach ( $info as $v ) {
				$total += $v ['num'] * $v ['out_price'];
				$discount += $v ['num'] * $v ['p_discount'];
			}
		}
		$this->params ['total'] = $total;
		$this->params ['discount'] = $discount;
		$this->params ['info'] = $info;
		//print_r($info);
		return $this->render ( 'sale/sales.html', $this->params );
	}
	
	function pageOut($inPath) {
		session_start ();
		$info = $_SESSION ['goodsInfo'];
		if (! is_array ( $info ))
			$this->ShowMsg ( "没有商品！" );
		$saleObj = new m_sales ();
		$sales = $mem_rs = array ();
		$purchaseObj = new m_purchase ();
		if ($info) {
			$cardid = base_Utils::getStr ( $_POST ['cardid'] );
			if ($cardid) {
				$memberObj = new m_member ();
				$mem_rs = $memberObj->getMemberPrice ( $cardid );
				if (! $mem_rs ['mid'])
					$this->ShowMsg ( "会员卡不存在！" );
				$sales ['mid'] = $mem_rs ['mid'];
				$sales ['membercardid'] = $mem_rs ['membercardid'];
				$sales ['realname'] = $mem_rs ['realname'];
			}
			$order_id = date ( "mdHis", time () ) . base_Utils::random ( 4, 1 );
			foreach ( $info as $k => $v ) {
				$out_amount += sprintf ( "%01.2f", $v ['out_price'] * $v ['num'] ); //总价
				$pro_amount += sprintf ( "%01.2f", $v ['p_discount'] * $v ['num'] ); //促销优惠的总价
				$sales ['order_id'] = $order_id;
				$sales ['goods_id'] = $v ['goods_id'];
				$sales ['cat_id'] = $v ['cat_id'];
				$sales ['goods_sn'] = $v ['goods_sn'];
				$sales ['goods_name'] = $v ['goods_name'];
				$sales ['num'] = $v ['num'];
				$sales ['out_price'] = $v ['out_price'];
				$sales ['p_discount'] = $v ['p_discount']; //促销优惠的金额
				$sales ['price'] = $sales ['out_price'] - $sales ['p_discount'];
				if ($v ['ismemberprice'] == 1 and $mem_rs ['mid']) {
					$sales ['m_discount'] = ($v ['out_price'] - $v ['p_discount']) * (100 - $mem_rs ['discount']) / 100; //会员+促销优惠
					$sales ['m_discount'] = sprintf ( "%01.2f", $sales ['m_discount'] );
					$sales ['price'] = $sales ['out_price'] - $sales ['m_discount'];
					$mem_amount += sprintf ( "%01.2f", $v ['out_price'] * $v ['num'] - $sales ['m_discount'] * $v ['num'] ); //会员+促销总价
				}
				$sales ['dateymd'] = date ( "Y-m-d", time () );
				$sales ['dateline'] = time ();
				if (! $saleObj->insert ( $sales )) {
					$this->ShowMsg ( "添加销售记录错误！" . $saleObj->getError () );
				}
				$purchaseObj->outStock ( $sales ['goods_id'], $v ['num'], sprintf ( "%01.2f", $sales ['price'] * $v ['num'] ) );
			}
			//计算应收金额
			if ($mem_amount > 0) {
				$real_amount = $out_amount - $mem_amount;
			} elseif ($pro_amount > 0) {
				$real_amount = $out_amount - $pro_amount;
			} else {
				$real_amount = $out_amount;
			}
			if ($sales ['mid']) {
				$memberObj->setCredit ( $sales ['mid'] );
			}
		}
		$this->params ['goods'] = $saleObj->select ( "order_id={$order_id}" )->items;
		$this->params ['order_id'] = $order_id;
		$this->params ['out_amount'] = $out_amount;
		$this->params ['real_amount'] = $real_amount;
		$this->params ['pro_amount'] = $pro_amount;
		$this->params ['mem_amount'] = $mem_amount;
		$_SESSION ['goodsInfo'] = "";
		return $this->render ( 'sale/out.html', $this->params );
	}
	/**
	 * 处理退货
	 * @param array $inPath
	 */
	function pagereturn($inPath) {
		$url = $this->getUrlParams ( $inPath );
		if ($_POST) {
			$order_id = base_Utils::getStr ( $_POST ['order_id'] );
			$salesObj = new m_sales ();
			$this->params ['order_id'] = $order_id;
			if ($_POST ['ac'] == 'del') {
				$sidArr = ( array ) $_POST ['sid'];
				$numArr = ( array ) $_POST ['num'];
				$returnArr = array ();
				$i = 0;
				if ($sidArr) {
					foreach ( $sidArr as $k => $v ) {
						$rs = $salesObj->selectOne ( "sid={$v} and order_id={$order_id} and refund_type=0", "num,goods_id,goods_name,price,mid" ); //退过款的商品不能够二次退款
						if (! $rs)
							$this->ShowMsg ( "该订单中没有该商品！" );
						$mid = $rs ['mid'];
						if ($numArr [$k] > 0 and $numArr [$k] <= $rs ['num']) {
							$returnArr [$i] ['sid'] = ( int ) $v;
							$returnArr [$i] ['goods_id'] = $rs ['goods_id'];
							$returnArr [$i] ['num'] = ( float ) $numArr [$k];
							$returnArr [$i] ['refund_type'] = 2;
							$returnArr [$i] ['refund_amount'] = sprintf ( "%01.2f", $rs ['price'] * $numArr [$k] );
							if ($numArr [$k] == $rs ['num']) {
								$returnArr [$i] ['refund_type'] = 1;
							}
							$i ++;
						} else {
							$this->ShowMsg ( "{$rs['goods_name']} 退货数量不正确" );
						}
					}
				}
				$purchaseObj = new m_purchase ();
				//退货操作 1修改库存 2 修改商品销售总价 3更新会员卡积分
				foreach ( $returnArr as $v ) {
					if (! $purchaseObj->backStock ( $v ['goods_id'], $v ['num'], $v ['refund_amount'] )) {
						$this->ShowMsg ( "商品{$v['goods_id']}退款出错" . $purchaseObj->getError () );
					}
				}
				if ($mid) {
					$memberObj = new m_member ();
					$memberObj->setCredit ( $mid );
				}
				$this->ShowMsg ( "退货成功！", $this->createUrl ( "/sales/return" ), 20, 1 );
			}
			$this->params ['list'] = $salesObj->select ( "order_id='{$order_id}'" )->items;
		}
		return $this->render ( 'sale/return.html', $this->params );
	}
}