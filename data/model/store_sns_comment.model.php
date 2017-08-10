<?php
/**
 * 店铺动态评论
 *
 *
 *
 * * @运维舫 (c) 2015-2018 ywf Inc. (http://www.shopnc.club)
 * @license    http://www.sho p.club
 * @link       唯一论坛：www.shopnc.club
 * @since      运维舫提供技术支持 授权请购买shopnc授权
 */
defined('ShopNC_CLUB') or exit('Access Invalid!');

class store_sns_commentModel extends Model {
    public function __construct(){
        parent::__construct('store_sns_comment');
    }

    /**
     * 店铺动态评论列表
     *
     * @param array $condition
     * @param string $field
     * @param string $order
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getStoreSnsCommentList($condition, $field = '*', $order = 'scomm_id desc', $limit = 0, $page = 0) {
        return $this->where($condition)->field($field)->order($order)->limit($limit)->page($page)->select();
    }

    /**
     * 店铺评论数量
     * @param array $condition
     * @return array
     */
    public function getStoreSnsCommentCount($condition) {
        return $this->where($condition)->count();
    }

    /**
     * 获取单条评论
     *
     * @param array $condition
     * @param string $field
     * @return array
     */
    public function getStoreSnsCommentInfo($condition, $field = '*') {
        return $this->where($condition)->field($field)->find();
    }

    /**
     * 保存店铺评论
     *
     * @param array $insert
     * @return boolean
     */
    public function saveStoreSnsComment($insert) {
        return $this->insert($insert);
    }

    public function editStoreSnsComment($update, $condition) {
        return $this->where($condition)->update($update);
    }

    /**
     * 删除店铺动态评论
     *
     * @param array $condition
     * @return boolean
     */
    public function delStoreSnsComment($condition) {
        return $this->where($condition)->delete();
    }
}