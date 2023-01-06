<?php
/**
 * eaterplanet 商城系统
 *
 * ==========================================================================
 * @link      https://e-p.io/
 * @copyright Copyright (c) 2019-2023 Dejavu Tech.
 * @license   https://e-p.io/license
 * ==========================================================================
 *
 * @author    Albert.Z
 *
 */
namespace Seller\Controller;
use Seller\Model\TransportModel;
class TransportController extends CommonController{

	protected function _initialize(){
		parent::_initialize();
			$this->breadcrumb1='商品管理';
			$this->breadcrumb2='运费模板';
	}

	function index(){
		$model = new TransportModel();
		$where = array('seller_id' => SELLERUID);

		$transportList = $model->getTransportList($where);

		$list=$transportList['list'];

		if (!empty($list) && is_array($list)){
			$transport = array();
			foreach ($list as $v) {
				if (!array_key_exists($v['id'],$transport)){
					$transport[$v['id']] = $v['title'];
				}
			}
			$extend = $model->getExtendList(array('transport_id'=>array('in',array_keys($transport))));
            // 整理
            if (!empty($extend)) {
                $tmp_extend = array();
                foreach ($extend as $val) {
                    $tmp_extend[$val['transport_id']]['data'][] = $val;
                    if ($val['is_default'] == 1) {
                        $tmp_extend[$val['transport_id']]['price'] = $val['sprice'];
                    }
                }
                $extend = $tmp_extend;
            }
		}

		$data['list']=$list;
		if(isset($extend))
		$data['extend']=$extend;
		$this->output=$data;
		$this->assign('page',$transportList['page']);// 赋值分页输出
		$this->display();
	}

	function clone_data(){

		$id = intval($_GET['id']);
		$model = new TransportModel();
		$transport = $model->getTransportInfo(array('id'=>$id));
		unset($transport['id']);
		$transport['title'] .= '的副本';
		$transport['update_time'] = time();

	    try {
            $model->startTrans();
            $insert = $model->addTransport($transport);
            if ($insert) {
        		$extend	= $model->getExtendList(array('transport_id'=>$id));
        		foreach ($extend as $k=>$v) {
        			foreach ($v as $key=>$value) {
        				$extend[$k]['transport_id'] = $insert;
        			}
        			unset($extend[$k]['id']);
        		}
        		$insert = $model->addExtend($extend);
            }
            if (!$insert) throw new Exception('操作失败');
            $model->commit();

			$this->redirect('Transport/index');
           	die;
        }catch (Exception $e){
            $model->rollback();
            $this->error('复制失败');
        }
	}

	function add(){
		$this->crumbs='新增';
		$this->display('edit');
	}
	function edit(){

		$id = intval($_GET['id']);
		$model = new TransportModel();
		$transport = $model->getTransportInfo(array('id'=>$id));
		$extend = $model->getExtendInfo(array('transport_id'=>$id));

		$data['transport']=$transport;
		$data['extend']=$extend;
		$this->output=$data;


		$this->crumbs='修改';
		$this->display();
	}
	function save(){
		if(IS_POST){

		$trans_info = array();
		$trans_info['title'] 		= $_POST['title'];
		$trans_info['seller_id'] = SELLERUID;


		$trans_info['update_time'] 	= time();

		$model = new TransportModel();

		if (is_numeric($_POST['transport_id'])){
			//编辑时，删除所有附加表信息
			$trans_info['id'] = intval($_POST['transport_id']);
			$transport_id = intval($_POST['transport_id']);
			$model->transUpdate($trans_info);
			$model->delExtend($transport_id);
		}else{
			//新增
			$transport_id = $model->addTransport($trans_info);
		}
		//保存默认运费
		if (is_array($_POST['default']['kd'])){
			$a = $_POST['default']['kd'];
			$trans_list[0]['area_id'] = '';
			$trans_list[0]['area_name'] = '全国';
			$trans_list[0]['snum'] = $a['start'];
			$trans_list[0]['sprice'] = $a['postage'];
			$trans_list[0]['xnum'] = $a['plus'];
			$trans_list[0]['xprice'] = $a['postageplus'];
			$trans_list[0]['is_default'] = 1;
			$trans_list[0]['transport_id'] = $transport_id;
			$trans_list[0]['transport_title'] = $_POST['title'];
			$trans_list[0]['top_area_id'] = '';
		}
		//保存自定义地区的运费设置
		$areas = $_POST['areas']['kd'];
		$special = $_POST['special']['kd'];
		if (is_array($areas) && is_array($special)){
			//$key需要加1，因为快递默认运费占了第一个下标
			foreach ($special as $key=>$value) {
			    if (empty($areas[$key])) continue;
				$areas[$key] = explode('|||',$areas[$key]);
				$trans_list[$key+1]['area_id'] = ','.$areas[$key][0].',';
				$trans_list[$key+1]['area_name'] = $areas[$key][1];
				$trans_list[$key+1]['snum'] = $value['start'];
				$trans_list[$key+1]['sprice'] = $value['postage'];
				$trans_list[$key+1]['xnum'] = $value['plus'];
				$trans_list[$key+1]['xprice'] = $value['postageplus'];
				$trans_list[$key+1]['is_default'] = 2;
				$trans_list[$key+1]['transport_id'] = $transport_id;
				$trans_list[$key+1]['transport_title'] = $_POST['title'];
				//计算省份ID
				$province = array();
				$tmp = explode(',',$areas[$key][0]);
				if (!empty($tmp) && is_array($tmp)){
					$city = $this->getCity();
					foreach ($tmp as $t) {
						if(isset($city[$t])){
							$pid = $city[$t];
							if (!in_array($pid,$province) && !empty($pid))
							$province[] = $pid;
						}

					}
				}
				if (count($province)>0){
					$trans_list[$key+1]['top_area_id'] = ','.implode(',',$province).',';
				}else{
					$trans_list[$key+1]['top_area_id'] = '';
				}
				//$i++;
			}
		}
		$result = $model->addExtend($trans_list);

		if($result){
			$this->success('保存成功',U('Transport/index'));
		}else{
			$this->error('保存失败');
		}

		}
	}
	/**
	 * 获取商家的运费模板列表
	 */
	public function getTransportList()
	{
	    $trans_model = D('Seller/Transport');
	    $list = $trans_model->getStoreTransportList(SELLERUID);
	    $result = array();
	    $result['code'] = empty($list) ? 0 : 1;
	    $result['list'] = $list;
	    echo json_encode($result);
	    die();
	}
	function del(){
		$id = intval($_GET['id']);
		$model = new TransportModel();
		if($model->delTansport(array('id'=>$id))){
		   $this->redirect('Transport/index');
           die;
		}else{
			$this->error('操作失败');
		}
	}

	/**
	 * 返回 市ID => 省ID 对应关系数组
	 *
	 * @return array
	 */
	private function getCity(){
		return array (36=>1,39=>9,40=>2,62=>22,73=>3,74=>3,75=>3,76=>3,77=>3,78=>3,79=>3,80=>3,81=>3,82=>3,83=>3,84=>4,85=>4,86=>4,87=>4,88=>4,89=>4,90=>4,91=>4,92=>4,93=>4,94=>4,95=>5,96=>5,97=>5,98=>5,99=>5,100=>5,101=>5,102=>5,103=>5,104=>5,105=>5,106=>5,107=>6,108=>6,109=>6,110=>6,111=>6,112=>6,113=>6,114=>6,115=>6,116=>6,117=>6,118=>6,119=>6,120=>6,121=>7,122=>7,123=>7,124=>7,125=>7,126=>7,127=>7,128=>7,129=>7,130=>8,131=>8,132=>8,133=>8,134=>8,135=>8,136=>8,137=>8,138=>8,139=>8,140=>8,141=>8,142=>8,162=>10,163=>10,164=>10,165=>10,166=>10,167=>10,168=>10,169=>10,170=>10,171=>10,172=>10,173=>10,174=>10,175=>11,176=>11,177=>11,178=>11,179=>11,180=>11,181=>11,182=>11,183=>11,184=>11,185=>11,186=>12,187=>12,188=>12,189=>12,190=>12,191=>12,192=>12,193=>12,194=>12,195=>12,196=>12,197=>12,198=>12,199=>12,200=>12,201=>12,202=>12,203=>13,204=>13,205=>13,206=>13,207=>13,208=>13,209=>13,210=>13,211=>13,212=>14,213=>14,214=>14,215=>14,216=>14,217=>14,218=>14,219=>14,220=>14,221=>14,222=>14,223=>15,224=>15,225=>15,226=>15,227=>15,228=>15,229=>15,230=>15,231=>15,232=>15,233=>15,234=>15,235=>15,236=>15,237=>15,238=>15,239=>15,240=>16,241=>16,242=>16,243=>16,244=>16,245=>16,246=>16,247=>16,248=>16,249=>16,250=>16,251=>16,252=>16,253=>16,254=>16,255=>16,256=>16,257=>16,258=>17,259=>17,260=>17,261=>17,262=>17,263=>17,264=>17,265=>17,266=>17,267=>17,268=>17,269=>17,270=>17,271=>17,272=>17,273=>17,274=>17,275=>18,276=>18,277=>18,278=>18,279=>18,280=>18,281=>18,282=>18,283=>18,284=>18,285=>18,286=>18,287=>18,288=>18,289=>19,290=>19,291=>19,292=>19,293=>19,294=>19,295=>19,296=>19,297=>19,298=>19,299=>19,300=>19,301=>19,302=>19,303=>19,304=>19,305=>19,306=>19,307=>19,308=>19,309=>19,310=>20,311=>20,312=>20,313=>20,314=>20,315=>20,316=>20,317=>20,318=>20,319=>20,320=>20,321=>20,322=>20,323=>20,324=>21,325=>21,326=>21,327=>21,328=>21,329=>21,330=>21,331=>21,332=>21,333=>21,334=>21,335=>21,336=>21,337=>21,338=>21,339=>21,340=>21,341=>21,342=>21,343=>21,344=>21,385=>23,386=>23,387=>23,388=>23,389=>23,390=>23,391=>23,392=>23,393=>23,394=>23,395=>23,396=>23,397=>23,398=>23,399=>23,400=>23,401=>23,402=>23,403=>23,404=>23,405=>23,406=>24,407=>24,408=>24,409=>24,410=>24,411=>24,412=>24,413=>24,414=>24,415=>25,416=>25,417=>25,418=>25,419=>25,420=>25,421=>25,422=>25,423=>25,424=>25,425=>25,426=>25,427=>25,428=>25,429=>25,430=>25,431=>26,432=>26,433=>26,434=>26,435=>26,436=>26,437=>26,438=>27,439=>27,440=>27,441=>27,442=>27,443=>27,444=>27,445=>27,446=>27,447=>27,448=>28,449=>28,450=>28,451=>28,452=>28,453=>28,454=>28,455=>28,456=>28,457=>28,458=>28,459=>28,460=>28,461=>28,462=>29,463=>29,464=>29,465=>29,466=>29,467=>29,468=>29,469=>29,470=>30,471=>30,472=>30,473=>30,474=>30,475=>31,476=>31,477=>31,478=>31,479=>31,480=>31,481=>31,482=>31,483=>31,484=>31,485=>31,486=>31,487=>31,488=>31,489=>31,490=>31,491=>31,492=>31,493=>32,494=>32,495=>32,496=>32,497=>32,498=>32,499=>32,500=>32,501=>32,502=>32,503=>32,504=>32,505=>32,506=>32,507=>32,508=>32,509=>32,510=>32,511=>32,512=>32,513=>32,514=>32,515=>32,516=>33,517=>33,518=>33,519=>33,520=>33,521=>33,522=>33,523=>33,524=>33,525=>33,526=>33,527=>33,528=>33,529=>33,530=>33,531=>33,532=>33,533=>33,534=>34,45055=>35);
	}

}
?>
