<?php
namespace Admin\Controller;

/**
 * NodesController
 * 节点信息
 */
class NodesController extends CommonController {
    /**
     * 节点列表
     * @return
     */
	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='权限管理';
			$this->breadcrumb2='节点管理';
	}
	
    public function index() {
        $nodeService = D('Node', 'Service');
        $nodes = $nodeService->getNodes();

        foreach ($nodes as $key => $node) {
            $nodes[$key]['type'] = $nodeService->getNodeType($node['level']);
        }
        $this->assign('nodes', $nodes);
        $this->assign('rows_count', count($nodes));
        $this->display();
    }
	
	/**
     * 添加节点
     * @return
     */
    public function add() {
        $this->assign('nodes', D('Node', 'Service')->getNodes());
        $this->display();
    }
    
 	/**
     * 创建节点
     * @return
     */
    public function create() {
        if (!isset($_POST['node'])) {
            return $this->errorReturn('无效的操作！');
        }

        $result = D('Node', 'Service')->addNode($_POST['node']);
        if (!$result['status']) {
        	$status = array('status'=>'back','message'=>$result['data']['error']);
            $this->osc_alert($status);
        }
        $status = array('status'=>'success','message'=>'添加节点成功！','jump'=>U('Nodes/add'));
        $this->osc_alert($status);
    }
    
    /**
     * 切换节点状态
     * @return
     */
    public function toggleStatus() {
    	$data = array('state' => 0);
    	
        $nodeService = D('Node', 'Service');
        if (!isset($_GET['id'])
            || !$nodeService->existNode($_GET['id'])) {
	        $data['msg'] = '无效的操作！';				
			$this->ajaxReturn($data);
		
        }

        if (!$_GET['status']) {
            $nodeService->setStatus($_GET['id'], 1);
        } else {
            $nodeService->setStatus($_GET['id'], 0);
        }

        $info = $_GET['status'] ? '禁用成功！' : '启用成功！';
        
        $data['state'] = 1;
        $data['msg'] = $info;				
		$this->ajaxReturn($data);
    }
}
