<?php

namespace Admin\Model;

/**
 * Node
 * 节点模型
 */
class NodeModel extends CommonModel {

    protected $_validate = array(
    	 // 节点名称不能为空
        array('title', 'require', '节点标题不能为空！', 1, 'regex', 3),
        // 节点名称不能大于32个字符
        array('title', '0,32', '节点标题不能超过32个字符！', 1, 'length', 3),
        // 节点名称不能为空
        array('name', 'require', '节点名称不能为空！', 1, 'regex', 3),
        // 节点名称不能大于32个字符
        array('name', '0,32', '节点名称不能超过32个字符！', 1, 'length', 3),

        // 状态
        array('status', '0,1', '无效的状态！', 1, 'in', 3),

    );

    protected $_auto = array(
        // 创建时间
        array('created_at', 'time', 1, 'function'),
        // 更新时间
        array('updated_at', 'time', 3, 'function')
    );
}
