<?php
//problemlist，showproblem，showcode
class ProblemAction extends Action{

	//题目列表页面+ac排序
	public function problemList(){
		$list1 = A('Public')->isLogin();
		$this->assign('link1', $list1['link1']);
		$this->assign('link2', $list1['link2']);

		$problem = M("problem");
		$count = $problem->count();
		import("ORG.Util.Page");
		$p = new Page($count,15);
		$page = $p->show();
		if($_GET['order']=="asc"){
			$this->assign('type',"desc");
			$this->assign('arrow','↑');
			$list2 = $problem->order('accepted asc')->limit($p->firstRow.','.$p->listRows)->select();	
		} else if($_GET['order']=="desc"){
			$this->assign('type',"asc");
			$this->assign('arrow','↓');
			$list2 = $problem->order('accepted desc')->limit($p->firstRow.','.$p->listRows)->select();	
		} else if(!isset($_GET['order'])){
			$this->assign('type',"asc");
			$list2 = $problem->order('problem_id')->limit($p->firstRow.','.$p->listRows)->select();	
		}

		// 标记题目是否AC
		$outList = array();
		$cnt = 0;
		if ($_SESSION['user_id']) {
			$solution = M('solution');
			for ($i = 0; $i < count($list2); $i++) {
				if($list2[$i]['is_open']==1)continue;
				$con1['problem_id'] = $list2[$i]['problem_id'];
				$con1['user_id'] = $_SESSION['user_id'];
				$con2['problem_id'] = $list2[$i]['problem_id'];
				$con2['user_id'] = $_SESSION['user_id'];
				$con2['result'] = 'Accepted';
				
				$list2[$i]['tag'] = '';
				if ($solution->where($con1)->count() > 0) {
					$list2[$i]['tag'] = '<img src="/Public/Picture/tried.gif">';
					if ($solution->where($con2)->count() > 0)
						$list2[$i]['tag'] = '<img src="/Public/Picture/ac.gif">';
				}
				$outList[$cnt++] = $list2[$i];
			}
		} else {
			for($i = 0; $i < count($list2); $i++) {
				if($list2[$i]['is_open']==1)continue;
				else {
					$outList[$cnt++] = $list2[$i];
				}
			}
		}

		$this->assign('page',$page);
		$this->assign('list2',$outList);
		$leo = $p->firstRow;
		$this->assign('rank',$leo);
		$this->display();
	}
	//problemList 题目+题号查询
	public function search(){
		$list1 = A('Public')->isLogin();
		$this->assign('link1', $list1['link1']);
		$this->assign('link2', $list1['link2']);
		
		$search = $_GET['problem'];
		$problem = M('problem');
		if(is_numeric($search)) {
			$condition['problem_id|title'] = array(intval($search),array('like','%'.$search.'%'),'_multi'=>true);
		} else $condition['title'] = array('like','%'.$search.'%');
		$count = $problem->where($condition)->count();
		/*分页*/
		import("ORG.Util.Page");
		$p = new Page($count,15);
		$page = $p->show();
		$list = $problem->where($condition)->order('problem_id')->limit($p->firstRow.','.$p->listRows)->select();
		$this->assign('page',$page);
		$this->assign('list',$list);
		$this->display('problemList');
	}
	// 题目题号是否空
	public function is_nothing($str){
		for($i=0;$i<strlen($str);$i++){
			if($str[$i]!=' ')return false;
		}
		return true;
	}
	
	//添加题目页面（管理员权限）
	public function addProblem(){
		if($_SESSION['user_id']!="admin"){
			$this->display('Public:error');
			return ;
		}
		$this->display();
	}
	//添加题目处理
	public function addProcess(){
		$problem = D("Problem");
		if($problem->create())
		{
			$problem->description = html_entity_decode($this->_POST ("description",""));
			$problem->input = html_entity_decode($this->_POST ("input",""));
			$problem->output = html_entity_decode($this->_POST ("output",""));
			$problem->sample_input = html_entity_decode($this->_POST ("sample_input",""));
			$problem->sample_output = html_entity_decode($this->_POST ("sample_output",""));
			$problem->hint = html_entity_decode($this->_POST ("hint",""));
			if($pid = $problem->add())
			{
				$data = M("testdata");
				$data->problem_id = $pid;
				$data->input = html_entity_decode($this->_POST ("testin",""));
				$data->output = html_entity_decode($this->_POST ("testout",""));
				$data->update_time = date('Y-m-d H:i:s');
				$data->add();
				$this->success("添加成功！");
			} else 
				$this->error("添加失败！");
		} 
		else 
		{
			$this->error($problem->getError());
			//exit($problem->getError());
		}
	}

	public function statistic()
	{
		$list1 = A('Public')->isLogin();
		$this->assign('link1', $list1['link1']);
		$this->assign('link2', $list1['link2']);

		$solution = M("solution");
		$pid = $_GET['pid'];
		$this->assign('pid',$pid);
		$condition['problem_id']=$pid;
		$problem = M('problem');
		$res = $problem->where($condition)->find();
		if($res==null){
			$this->display('Public:error');
			return ;
		}
		$submit = $solution->where($condition)->count();	//submit数
		$this->assign('submit',$submit);

		$condition['result'] = 'Accepted';

		$AC = $solution->where($condition)->count();	//AC数
		$this->assign('AC',$AC);


		$condition['result'] = 'Wrong Answer';
		$WA = $solution->where($condition)->count();	//WA数
		$this->assign('WA',$WA);


		$condition['result'] = 'Time Limit Exceeded';
		$TLE = $solution->where($condition)->count();	//TLE数
		$this->assign('TLE',$TLE);

		$condition['result'] = 'Presentation Error';
		$PE = $solution->where($condition)->count();	//PE数
		$this->assign('PE', $PE);

		$condition['result'] = 'Compile Error';
		$CE = $solution->where($condition)->count();	//CE数
		$this->assign('CE', $CE);

		$condition['result'] = 'Memory Limit Exceeded';
		$MLE = $solution->where($condition)->count();	//MLE数
		$this->assign('MLE', $MLE);

		$condition['result'] = 'Runtime Error';
		$RE = $solution->where($condition)->count();	//RE数
		$this->assign('RE', $RE);

		$condition['result'] = 'Output Limit Exceeded';
		$OLE = $solution->where($condition)->count();	//OLE数
		$this->assign('OLE', $OLE);

		import("ORG.Util.Page");
		$condition['result'] = 'Accepted';
		$condition['problem_id'] = $pid;
		$count = $solution->where($condition)->count();
		$p = new Page($count,20);

		$list = $solution->where($condition)->order('runtime,memory,code_length,solution_id')->limit($p->firstRow.','.$p->listRows)->select();
		$page = $p->show();
		for ($i = 0; $i < count($list); $i++) {

			if ($list[$i]['is_share']=="Yes" || $list[$i]['user_id']==$_SESSION['user_id']){
				$list[$i]['share'] = '<a target="_blank" href="__APP__/Problem/showCode?solution_id='.$list[$i]['solution_id'].'">'.$list[$i]['solution_id'].'</a>';
			} else $list[$i]['share'] = $list[$i]['solution_id'];

			
		}
		// 赋值赋值
    	$this->assign('page', $page);
    	$this->assign('list', $list);
		$this->display();
	}

	//题目显示
	public function showProblem(){
		$list = A('Public')->isLogin();
		$this->assign('link1', $list['link1']);
		$this->assign('link2', $list['link2']);

		$problem = M("problem");
		$pid = $_GET['pid'];
		$pro = $problem->where('problem_id='.$pid)->find();//find()返回一维数组，select()返回二维数组
		if($pro==null){
			$this->display('Public:error');
			return ;
		}
		$this->assign('pid',$pro['problem_id']);
		$this->assign('title',$pro['title']);
		$this->assign('desc',$pro['description']);
		$this->assign('sin',$pro['sample_input']);
		$this->assign('sout',$pro['sample_output']);
		$this->assign('hint',$pro['hint']);
		$this->assign('from',$pro['problem_id']);
		$this->assign('time',$pro['time_limit']);
		$this->assign('memory',$pro['memory_limit']);
		$this->assign('ac',$pro['accepted']);
		$this->assign('submit',$pro['submit']);
		$this->assign('input',$pro['input']);
		$this->assign('output',$pro['output']);
		$this->assign('source',$pro['from']);
		$this->display();
	}
	//高亮代码显示
	public function showCode(){
		$list = A('Public')->isLogin();
		$this->assign('link1', $list['link1']);
		$this->assign('link2', $list['link2']);
		
		$sid = $_GET['solution_id'];
		$code = M("source_code");
		$solution = M("solution");
		$solo = $solution->where('solution_id='.$sid)->find();
		if($solo==null){
			$this->display('Public:error');
			return ;
		} else if($_SESSION['user_id']==null && $solo['is_share']=='No'){
			$this->display('Public:error');
			return ;
		} else if($_SESSION['user_id']!=$solo['user_id'] && $solo['is_share']=="No"){
			$this->display('Public:error');
			return ;
		}

		$src = $code->where('solution_id='.$sid)->find();
		$this->assign('src',htmlentities($src['source']));

		$uid = $solo['user_id'];
		$pid = $solo['problem_id'];
		$runtime = $solo['runtime'];
		$memory = $solo['memory'];
		$result = $solo['result'];
		$lang = $solo['language'];
		$length = $solo['code_length'];
		$judgetime = $solo['judgetime'];
		$this->assign('uid',$uid);
		$this->assign('pid',$pid);
		$this->assign('runtime',$runtime);
		$this->assign('memory',$memory);
		$this->assign('result',$result);
		$this->assign('language',$lang);
		$this->assign('length',$length);
		$this->assign('judgetime',$judgetime);

		$this->display();
	}
}