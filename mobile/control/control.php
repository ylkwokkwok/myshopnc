<?php
/**
 * mobile父类
 *
 * @运维舫 (c) 2015-2018 ywf Inc. (http://www.shopnc.club)
 * @license    http://www.sho p.club
 * @link       唯一论坛：www.shopnc.club
 * @since      运维舫提供技术支持 授权请购买shopnc授权
 */



defined('ShopNC_CLUB') or exit('Access Invalid!');

/********************************** 前台control父类 **********************************************/

class mobileControl{

    //客户端类型
    protected $client_type_array = array('android', 'wap', 'wechat', 'ios', 'windows');
    //列表默认分页数
    protected $page = 5;


    public function __construct() {
        Language::read('mobile');

        //分页数处理
        $page = intval($_GET['page']);
        if($page > 0) {
            $this->page = $page;
        }
    }
}

class mobileHomeControl extends mobileControl{
    public function __construct() {
        parent::__construct();
    }

    protected function getMemberIdIfExists()
    {
        $key = $_POST['key'];
        if (empty($key)) {
            $key = $_GET['key'];
        }

        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
        if (empty($mb_user_token_info)) {
            return 0;
        }

        return $mb_user_token_info['member_id'];
    }
}

class mobileMemberControl extends mobileControl{

    protected $member_info = array();

    public function __construct() {
        parent::__construct();
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if (strpos($agent, "MicroMessenger") && $_GET["act"]=='auto') {	
			$this->appId = C('app_weixin_appid');
			$this->appSecret = C('app_weixin_secret');;			
        }else{
			$model_mb_user_token = Model('mb_user_token');
			$key = $_POST['key'];
			if(empty($key)) {
				$key = $_GET['key'];
			}
			$mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
			if(empty($mb_user_token_info)) {
				output_error('请登录', array('login' => '0'));
			}

        $model_member = Model('member');
        $this->member_info = $model_member->getMemberInfoByID($mb_user_token_info['member_id']);

        if(empty($this->member_info)) {
            output_error('请登录', array('login' => '0'));
			} else {
				$this->member_info['client_type'] = $mb_user_token_info['client_type'];
				$this->member_info['openid'] = $mb_user_token_info['openid'];
				$this->member_info['token'] = $mb_user_token_info['token'];
				$level_name = $model_member->getOneMemberGrade($mb_user_token_info['member_id']);
				$this->member_info['level_name'] = $level_name['level_name'];
				//读取卖家信息
				$seller_info = Model('seller')->getSellerInfo(array('member_id'=>$this->member_info['member_id']));
				$this->member_info['store_id'] = $seller_info['store_id'];
			}
        }
    }

    public function getOpenId()
    {
        return $this->member_info['openid'];
    }

    public function setOpenId($openId)
    {
        $this->member_info['openid'] = $openId;
        Model('mb_user_token')->updateMemberOpenId($this->member_info['token'], $openId);
    }
}

class mobileSellerControl extends mobileControl{

    protected $seller_info = array();
    protected $seller_group_info = array();
    protected $member_info = array();
    protected $store_info = array();
    protected $store_grade = array();

    public function __construct() {
        parent::__construct();

        $model_mb_seller_token = Model('mb_seller_token');

        $key = $_POST['key']?$_POST['key']:$_GET['key'];
        if(empty($key)) {
            output_error('请登录', array('login' => '0'));
        }

        $mb_seller_token_info = $model_mb_seller_token->getSellerTokenInfoByToken($key);
        if(empty($mb_seller_token_info)) {
            output_error('请登录', array('login' => '0'));
        }

        $model_seller = Model('seller');
        $model_member = Model('member');
        $model_store = Model('store');
        $model_seller_group = Model('seller_group');

        $this->seller_info = $model_seller->getSellerInfo(array('seller_id' => $mb_seller_token_info['seller_id']));
        $this->member_info = $model_member->getMemberInfoByID($this->seller_info['member_id']);
        $this->store_info = $model_store->getStoreInfoByID($this->seller_info['store_id']);
        $this->seller_group_info = $model_seller_group->getSellerGroupInfo(array('group_id' => $this->seller_info['seller_group_id']));

        // 店铺等级
        if (intval($this->store_info['is_own_shop']) === 1) {
            $this->store_grade = array(
                'sg_id' => '0',
                'sg_name' => '自营店铺专属等级',
                'sg_goods_limit' => '0',
                'sg_album_limit' => '0',
                'sg_space_limit' => '999999999',
                'sg_template_number' => '6',
                'sg_price' => '0.00',
                'sg_description' => '',
                'sg_function' => 'editor_multimedia',
                'sg_sort' => '0',
            );
        } else {
            $store_grade = rkcache('store_grade', true);
            $this->store_grade = $store_grade[$this->store_info['grade_id']];
        }

        if(empty($this->member_info)) {
            output_error('请登录', array('login' => '0'));
        } else {
            $this->seller_info['client_type'] = $mb_seller_token_info['client_type'];
        }
    }
}

/**
 * 积分中心control父类
 */
class mobilePointShopControl extends mobileControl {
    protected $member_info;
    public function __construct(){
        parent::__construct();
    }
    protected function getMemberIdIfExists()
    {
        $key = $_POST['key'];
        if (empty($key)) {
            $key = $_GET['key'];
        }
    
        $model_mb_user_token = Model('mb_user_token');
        $mb_user_token_info = $model_mb_user_token->getMbUserTokenInfoByToken($key);
        if (empty($mb_user_token_info)) {
            return 0;
        }
    
        return $mb_user_token_info['member_id'];
    }
    /**
     * 获得积分中心会员信息包括会员名、ID、会员头像、会员等级、经验值、等级进度、积分、已领代金券、已兑换礼品、礼品购物车
     */
    public function pointshopMInfo($is_return = false){
//         if($_SESSION['is_login'] == '1'){
            $model_member = Model('member');
            if (!$this->member_info){
                //查询会员信息
                $member_infotmp = $model_member->getMemberInfoByID($_SESSION['member_id']);
            } else {
                $member_infotmp = $this->member_info;
            }
            $member_infotmp['member_exppoints'] = intval($member_infotmp['member_exppoints']);

            //当前登录会员等级信息
            $membergrade_info = $model_member->getOneMemberGrade($member_infotmp['member_exppoints'],true);
            $member_info = array_merge($member_infotmp,$membergrade_info);
//             Tpl::output('member_info',$member_info);

            //查询已兑换并可以使用的代金券数量
            $model_voucher = Model('voucher');
            $vouchercount = $model_voucher->getCurrentAvailableVoucherCount($_SESSION['member_id']);
//             Tpl::output('vouchercount',$vouchercount);

            //购物车兑换商品数
            $pointcart_count = Model('pointcart')->countPointCart($_SESSION['member_id']);
//             Tpl::output('pointcart_count',$pointcart_count);

            //查询已兑换商品数(未取消订单)
            $pointordercount = Model('pointorder')->getMemberPointsOrderGoodsCount($_SESSION['member_id']);
//             Tpl::output('pointordercount',$pointordercount);
            if ($is_return){
                return array('member_info'=>$member_info,'vouchercount'=>$vouchercount,'pointcart_count'=>$pointcart_count,'pointordercount'=>$pointordercount);
            }
//         }
    }
}