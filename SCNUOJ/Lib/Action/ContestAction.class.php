<?php
class ContestAction extends Action{
	/* Table think_contest_list: 
	ID,Title,Begin time,Duration,Type=0(private) =1(public),Status=0(pending) =1(running) =2(ended)
	manager,password(public=NULL)
	*/

	// 比赛列表
	public function contestList(){
		$list1 = A('Public')->isLogin();
		$this->assign('link1', $list1['link1']);
		$this->assign('link2', $list1['link2']);

		$contest_list = M('contest_list');
		$count = $contest_list->count();
		$list = $contest_list->select();
		import("ORG.Util.Page");
		$p = new Page($count,15);
		$page = $p->show();

		for($i = 0; $i < count($list); $i++) {
			if(time() < $list[$i]['begin_time']) {
				$list[$i]['status'] = '<b style="color:#990099">Pending</b>';
			} elseif (time() > $list[$i]['end_time']) {
				$list[$i]['status'] = '<b style="color:	green">Ended</b>';
			} else $list[$i]['status'] = '<b style="color:red">Running</b>';
			// 时间戳转换
			$list[$i]['begin_time'] = date('Y-m-d G:i:s', $list[$i]['begin_time']);
			$days = (int)($list[$i]['length'] / (24*60));
			$hours = (int)($list[$i]['length'] % (24*60) / 60);
			$minutes = (int)($list[$i]['length'] % (24*60) % 60);
			$list[$i]['length'] = '';
			if($days) {
				$list[$i]['length'] = $days.'天 ';
			}
			$list[$i]['length'] .= sprintf('%02d', $hours).':'.sprintf('%02d', $minutes).':00';
			$list[$i]['end_time'] = date('Y-m-d G:i:s', $list[$i]['end_time']);

			if($list[$i]['type']==0) {
				$list[$i]['type'] = '<b style="color:red">Private</b>';
			} else $list[$i]['type'] = '<b style="color:green">Public</b>';
		}

		$this->assign('page', $page);
		$this->assign('list', $list);
		$this->display();
	}

	// 比赛题目
	public function overView() {
		$list1 = A('Public')->isLogin();
		$this->assign('link1', $list1['link1']);
		$this->assign('link2', $list1['link2']);

		$contest_list = M('contest_list');
		$cid = $_GET['cid'];
		$clist = $contest_list->where('id='.$cid)->find();
		// 页面不存在
		if(!$clist) {
			$this->display('Public:error');
			return ;
		}

		// 比赛不公开
		if($clist['type']==0) {
			// TODO...

		} else { // 比赛公开
			$list = array();
			$cnt = 0;
			$problem = M('problem');
			$solution = M('solution');
			for($i = $clist['pid1']; $i <= $clist['pid2']; $i++) {
				$list[$cnt] = $problem->where('problem_id='.$i)->find();
				$list[$cnt]['proid'] = array();
				$list[$cnt]['proid'] = chr(65+$i-$clist['pid1']);
				$list[$cnt]['tag'] = array();
				$list[$cnt]['tag'] = '';
				$condition = array();
				$condition['user_id'] = $_SESSION['user_id'];
				$condition['problem_id'] = $i;
				$condition['contest_id'] = $cid;
				if($solution->where($condition)->count() > 0) {
					$list[$cnt]['tag'] = '<img src="/Public/Picture/tried.gif">';
					$condition['result'] = "Accepted";
					if($solution->where($condition)->count() > 0) {
						$list[$cnt]['tag'] = '<img src="/Public/Picture/ac.gif">';
					}
				}
				$cnt++;
			}
			$this->assign('list', $list);
			$this->assign('head', $clist['title']);
			$this->assign('pid1', $clist['pid1']);
			$this->assign('cid', $cid);
			$this->assign('pro_id', $pro_id);
			$this->assign('begin_time',$clist['begin_time']);
			$this->assign('length',$clist['length']);

			$this->display();
		}
	}

	// 比赛详细题目(题号参数从0开始)
	public function showProblem() {
		$list1 = A('Public')->isLogin();
		$this->assign('link1', $list1['link1']);
		$this->assign('link2', $list1['link2']);

		$contest_list = M('contest_list');
		$cid = $_GET['cid'];
		$clist = $contest_list->where('id='.$cid)->find();
		// 页面不存在
		if(!$clist) {
			$this->display('Public:error');
			return ;
		}

		$problem = M("problem");
		$pid = $clist['pid1'] + $_GET['pid'];
		$pro = $problem->where('problem_id='.$pid)->find();//find()返回一维数组，select()返回二维数组
		if($pid < $clist['pid1'] || $pid > $clist['pid2'] || !$pro) {
			$this->display('Public:error');
			return ;
		}
		$this->assign('pid',$_GET['pid']);
		$this->assign('title',$pro['title']);
		$this->assign('desc',html_entity_decode($pro['description']));
		$this->assign('sin',html_entity_decode($pro['sample_input']));
		$this->assign('sout',html_entity_decode($pro['sample_output']));
		$this->assign('hint',html_entity_decode($pro['hint']));
		$this->assign('from',html_entity_decode($pro['problem_id']));
		$this->assign('time',html_entity_decode($pro['time_limit']));
		$this->assign('memory',html_entity_decode($pro['memory_limit']));
		$this->assign('ac',$pro['accepted']);
		$this->assign('submit',$pro['submit']);
		$this->assign('input',html_entity_decode($pro['input']));
		$this->assign('output',html_entity_decode($pro['output']));
		$this->assign('source',html_entity_decode($pro['from']));

		$this->assign('cid', $cid);
		$this->assign('proid', chr(65+$_GET['pid']));
		$this->assign('head',$clist['title']);
		// 
		$list = array();
		$cnt = 0;
		for($i = $clist['pid1']; $i <= $clist['pid2']; $i++) {
			$list[$cnt] = $problem->where('problem_id='.$i)->find();
			$list[$cnt]['proid'] = array();
			$list[$cnt]['proid'] = chr(65+$i-$clist['pid1']);
			$cnt++;
		}
		$this->assign('list', $list);
		$this->assign('pid1', $clist['pid1']);
		$this->assign('begin_time',$clist['begin_time']);
		$this->assign('length',$clist['length']);
		$this->display();
	}

	//提交页面
	public function solutionSubmit(){
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		$contest_list = M('contest_list');
		$cid = $_GET['cid'];
		$clist = $contest_list->where('id='.$cid)->find();
		// 页面不存在
		if(!$clist) {
			$this->display('Public:error');
			return ;
		}

		// 非比赛时间禁止提交，比赛时开启
		if(time() < $clist['begin_time'] || time() > $clist['end_time']) {
		$this->display('Public:error');
			return ;
		}

		$problem = M("problem");
		$pid = $clist['pid1'] + $_GET['pid'];
		$pro = $problem->where('problem_id='.$pid)->find();
		if($pid < $clist['pid1'] || $pid > $clist['pid2'] || !$pro){
			$this->display('Public:error');
			return ;
		}

		$this->assign('pid', $_GET['pid']);
		$this->assign('cid', $_GET['cid']);
		$this->assign('proid', chr(65+$_GET['pid']));
		$this->assign('begin_time',$clist['begin_time']);
		$this->assign('length',$clist['length']);
		$this->display();
	}

	//提交处理
	public function submit() {
		$contest_list = M('contest_list');
		$cid = $_POST['cid'];
		$clist = $contest_list->where('id='.$cid)->find();
		// 非比赛时间禁止提交
		if(time() < $clist['begin_time'] || time() > $clist['end_time']) {
			$this->display('Public:error');
			return ;
		}
		// 没有登录禁止提交
		if($_SESSION['user_id']==null){
			$list2 = A('Public')->isLogin();
			$this->assign('link1', $list2['link1']);
			$this->assign('link2', $list2['link2']);
			$this->display('Public:error');
			return ;
		}

		$condition['source'] = $_POST['src'];
		$condition['source'] = html_entity_decode($_POST['src']);
		$solution = M("solution");
		$condition['problem_id'] = $_POST['pid'] + $clist['pid1'];//
		$condition['user_id'] = $_SESSION['user_id'];
		$condition['judgetime'] = date('Y-m-d H:i:s');
		$condition['result'] = "Pending";
		$condition['contest_id'] = $_POST['cid'];//
		$condition['code_length'] = strlen($condition['source']);
		$condition['language'] = $_POST['language'];
		$condition['is_share'] = 'No';
		$solution_id = $solution->add($condition);

		//code
		$src_code = M("source_code");
		$condition['solution_id'] = $solution_id;
		$src_code->add($condition);

		//queue
		$queue = M("queue");
		$condition['solution_id'] = $solution_id;
		$condition['in_date'] = $condition['judgetime'];
		$queue->add($condition);

		// problem
		$problem = M("problem");
		$con['problem_id'] = $_POST['pid'];
		$res = $problem->where($con)->find();
		$cnt = $res['submit'];
		$cnt++;
		$data['submit'] = $cnt;
		$problem->where($con)->save($data);

		// user
		$user = M("user");
		$res = $user->where("user_id="."'".$_SESSION['user_id']."'")->find();
		$cnt = $res['submit'];
		$cnt++;
		$data['submit'] = $cnt;
		$user->where("user_id="."'".$_SESSION['user_id']."'")->save($data);

		// 跳转
		$this->redirect('__URL__/Contest/status?cid='.$_POST['cid']);
	}

	// 数据统计
	public function statistic() {
		$list1 = A('Public')->isLogin();
		$this->assign('link1', $list1['link1']);
		$this->assign('link2', $list1['link2']);

		$contest_list = M('contest_list');
		$cid = $_GET['cid'];
		$clist = $contest_list->where('id='.$cid)->find();
		// 页面不存在
		if(!$clist) {
			$this->display('Public:error');
			return ;
		}

		$solution = M("solution");
		$pid = $_GET['pid'] + $clist['pid1'];
		$this->assign('pid',$pid);
		$condition['problem_id'] = $pid;
		$problem = M('problem');
		$res = $problem->where($condition)->find();
		if($pid < $clist['pid1'] || $pid > $clist['pid2'] || !$res){
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
		$condition['contest_id'] = $cid;
		$count = $solution->where($condition)->count();
		$p = new Page($count,20);

		$list = $solution->where($condition)->order('runtime,memory,code_length,solution_id')->limit($p->firstRow.','.$p->listRows)->select();
		$page = $p->show();
		for ($i = 0; $i < count($list); $i++) {

			if ($list[$i]['is_share']=="Yes" || $list[$i]['user_id']==$_SESSION['user_id']){
				$list[$i]['share'] = '<a target="_blank" href="__URL__/showCode?cid=$cid&solution_id='.$list[$i]['solution_id'].'">'.$list[$i]['solution_id'].'</a>';
			} else $list[$i]['share'] = $list[$i]['solution_id'];
			
		}

		// 赋值
    	$this->assign('page', $page);
    	$this->assign('list', $list);
    	$this->assign('pid', $_GET['pid']);
    	$this->assign('cid', $_GET['cid']);
    	$this->assign('proid', chr(65+$_GET['pid']));
    	$this->assign('head',$clist['title']);
		$this->assign('begin_time',$clist['begin_time']);
		$this->assign('length',$clist['length']);
		$this->display();
	}

	// status
	public function status() {
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		$contest_list = M('contest_list');
		$cid = $_GET['cid'];
		$clist = $contest_list->where('id='.$cid)->find();
		// 页面不存在
		if(!$clist) {
			$this->display('Public:error');
			return ;
		}

		$solution = M('solution');
		$list = $solution->where('contest_id='.$_GET['cid'])->select();
		// // 页面不存在
		// if(count($list)) {
		// 	$this->display('Public:error');
		// 	return ;
		// }
		// 题目下拉提示
		$list1 = array();
		$cnt = 0;
		$problem = M('problem');
		for($i = $clist['pid1']; $i <= $clist['pid2']; $i++) {
			$list1[$cnt] = $problem->where('problem_id='.$i)->find();
			$list1[$cnt]['proid'] = array();
			$list1[$cnt]['proid'] = chr(65+$i-$clist['pid1']);
			$cnt++;
		}
		$this->assign('list1', $list1);
		$this->assign('pid1', $clist['pid1']);

		// 结果分页
		$count = count($list);
		import("ORG.Util.Page");
		$p = new Page($count,15);//第二参数：每页行数
		$page = $p->show();
		$list = $solution->where('contest_id='.$_GET['cid'])->order('solution_id DESC')->limit($p->firstRow.','.$p->listRows)->select();

		for ($i = 0; $i < count($list); $i++) {
			if ($list[$i]['result'] == 'Accepted')
				$list[$i]['resultColor'] = '<span class="Result-AC">Accepted</span>';
			else if ($list[$i]['result'] == 'Presentation Error')
				$list[$i]['resultColor'] = '<span class="Result-PE">Presentation Error</span>';
			else if ($list[$i]['result'] == 'Pending')
				$list[$i]['resultColor'] = '<span class="Result-Pend">Pending</span>'; 				
			else if ($list[$i]['result']=='Compile Error'){
				$compile = M("compileinfo");
				$com = $compile->where('solution_id='.$list[$i]['solution_id'])->find();
				$com['error'] = htmlentities($com['error']);
				$list[$i]['resultColor'] = '<a class="result-ce"><div>'.$com['error'].'</div>Compile Error</a>';
			} else $list[$i]['resultColor'] = '<span class="Result-Other">' . $list[$i]['result'] . '</span>';

			if ($list[$i]['is_share']=="Yes" || $list[$i]['user_id']==$_SESSION['user_id']){
				$list[$i]['share'] = '<a target="_blank" href="__URL__/showCode?cid='.$cid.'&solution_id='.$list[$i]['solution_id'].'">'.$list[$i]['solution_id'].'</a>';
			} else $list[$i]['share'] = $list[$i]['solution_id'];
 
			$list[$i]['problem_id'] = $list[$i]['problem_id']-$clist['pid1'];
			$list[$i]['proid'] = array();
			$list[$i]['proid'] = chr(65+$list[$i]['problem_id']);
		}

		// 赋值
		$this->assign('page', $page);
		$this->assign('list', $list);
		$this->assign('cid', $_GET['cid']);
		$this->assign('head',$clist['title']);
		$this->assign('begin_time',$clist['begin_time']);
		$this->assign('length',$clist['length']);
		$this->display();
	}

	//检查是否全空格或者空串,供条件查询search调用
	public function chkBlank($str){
		for($i=0;$i<strlen($str);$i++){
			if($str[$i]!=' ')return false;
		}
		return true;
	}

	//条件查询
	public function search(){
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		// 自动刷新
		echo "<meta http-equiv='refresh' content='10;url='__URL__/search''>";

		$contest_list = M('contest_list');
		$cid = $_GET['cid'];
		$clist = $contest_list->where('id='.$cid)->find();
		// 页面不存在
		if(!$clist) {
			$this->display('Public:error');
			return ;
		}

		$solution = M('solution');
		$list = $solution->where('contest_id='.$_GET['cid'])->select();
		// // 页面不存在
		// if(!$list) {
		// 	$this->display('Public:error');
		// 	return ;
		// }

		// 题目下拉提示
		$list1 = array();
		$cnt = 0;
		$problem = M('problem');
		for($i = $clist['pid1']; $i <= $clist['pid2']; $i++) {
			$list1[$cnt] = $problem->where('problem_id='.$i)->find();
			$list1[$cnt]['proid'] = array();
			$list1[$cnt]['proid'] = chr(65+$i-$clist['pid1']);
			$cnt++;
		}
		$this->assign('list1', $list1);
		$this->assign('pid1', $clist['pid1']);

		$solution = M("solution");
		if(!$this->chkBlank($_GET['user'])){
			$condition['user_id'] = $_GET['user'];
		} else $_GET['user'] = null;
		if(!$this->chkBlank($_GET['problem'])){
			$condition['problem_id'] = $_GET['problem']+$clist['pid1'];
		} else $_GET['problem'] = null;
		if($_GET['result']!="All"){
			$condition['result'] = $_GET['result'];
		}
		$condition['contest_id'] = $_GET['cid'];
		$parameter = 'result=' . urlencode($_GET['result']).'&user_id='.urlencode($_GET['user']).'&problem_id='.urlencode($_GET['problem']);	
		$count = $solution->where($condition)->count();
		import("ORG.Util.Page");
		$p = new Page($count,15,$parameter);
		$page = $p->show();
		$list = $solution->where($condition)->order("solution_id DESC")->limit($p->firstRow . ',' . $p->listRows)->select();
		
		for ($i = 0; $i < count($list); $i++) {
			if ($list[$i]['result'] == 'Accepted')
				$list[$i]['resultColor'] = '<span class="Result-AC">Accepted</span>';
			else if ($list[$i]['result'] == 'Presentation Error')
				$list[$i]['resultColor'] = '<span class="Result-PE">Presentation Error</span>';
			else if ($list[$i]['result'] == 'Pending')
				$list[$i]['resultColor'] = '<span class="Result-Pend">Pending</span>'; 			
			else if ($list[$i]['result']=='Compile Error'){
				$compile = M("compileinfo");
				$com = $compile->where('solution_id='.$list[$i]['solution_id'])->find();
				$com['error'] = htmlentities($com['error']);
				$list[$i]['resultColor'] = '<a class="result-ce"><div>'.$com['error'].'</div>Compile Error</a>';
			} else $list[$i]['resultColor'] = '<span class="Result-Other">' . $list[$i]['result'] . '</span>';

			if ($list[$i]['is_share']=="Yes" || $list[$i]['user_id']==$_SESSION['user_id']){
				$list[$i]['share'] = '<a href="__APP__/Problem/showCode?cid=$cid&solution_id='.$list[$i]['solution_id'].'">'.$list[$i]['solution_id'].'</a>';
			} else $list[$i]['share'] = $list[$i]['solution_id'];
			$list[$i]['problem_id'] = $list[$i]['problem_id']-$clist['pid1'];
			$list[$i]['proid'] = array();
			$list[$i]['proid'] = chr(65+$list[$i]['problem_id']);
		}
		$this->assign('page',$page);
		$this->assign('list',$list);
		$this->assign('uid',$_GET['user']);
		$this->assign('pid',$_GET['problem']);
		$this->assign('res',$_GET['result']);

		$this->assign('head',$clist['title']);
		$this->assign('cid',$cid);
		$this->assign('begin_time',$clist['begin_time']);
		$this->assign('length',$clist['length']);
		$this->display("status");
	}

	// rank
	public function rank() {
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		$contest_list = M('contest_list');
		$cid = $_GET['cid'];
		$clist = $contest_list->where('id='.$cid)->find();
		// 页面不存在
		if(!$clist) {
			$this->display('Public:error');
			return ;
		}

		$solution = M('solution');
		$slist = $solution->where('contest_id='.$_GET['cid'])->select();
		// // 页面不存在
		// if(!$slist) {
		// 	$this->display('Public:error');
		// 	return ;
		// }

		$ptag = array();
		for($i=$clist['pid1']; $i<=$clist['pid2']; $i++){
			$ptag[$i] = '<a href="__URL__/showProblem?cid='.$cid.'&pid='.($i-$clist['pid1']).'">'.chr(65+$i-$clist['pid1']).'</a>';
		}

		$this->assign('ptag', $ptag);
		$this->assign('cid', $_GET['cid']);

		$ranklist = array();
		$map = array();
		$user_num = 0;
		for($i=0; $i<count($slist); $i++) {
			$uid = $slist[$i]['user_id'];
			if(!in_array($uid, $map)) {
				$map[$user_num++] = $uid;
				$ranklist[$uid] = array();
				$ranklist[$uid]['user_id'] = $uid;
				$ranklist[$uid]['solve'] = 0;// AC数
				$ranklist[$uid]['penalty'] = 0;// 罚时(精确到分钟)
			}

			$pid = $slist[$i]['problem_id'] - $clist['pid1'];
			if($slist[$i]['result']=="Accepted") {
				if($ranklist[$uid][$pid*2]==null) {
					$ranklist[$uid][$pid*2] = strtotime($slist[$i]['judgetime'])-$clist['begin_time'];
				} else {
					$ranklist[$uid][$pid*2] = min(strtotime($slist[$i]['judgetime'])-$clist['begin_time'], $ranklist[$slist[$i]['user_id']][$pid*2]);
				}
			} else {
				if($ranklist[$uid][$pid*2+1]==null) {
					$ranklist[$uid][$pid*2+1] = 1;
				} else $ranklist[$uid][$pid*2+1]++;
			}
		}

		for($i=0; $i<$user_num; $i++) {
			$ac_num = 0;
			$sum = 0;
			$uid = $map[$i];
			for($j=$clist['pid1']; $j<=$clist['pid2']; $j++) {
				$pid = $clist['pid1'] - $j;
				if($ranklist[$uid][$pid*2]!=null) {
					$ac_num++;
					$minutes = (int)($ranklist[$uid][$pid*2] / 60);
					$ranklist[$uid]['penalty'] += $minutes;
					$ranklist[$uid][$pid*2] = $this->change($ranklist[$uid][$pid*2]);
				}
				if($ranklist[$uid][$pid*2+1]!=null) {
					$sum += $ranklist[$uid][$pid*2+1];
				}
			}
			$ranklist[$uid]['solve'] = $ac_num;
			if(!$ac_num) {
				$ranklist[$uid]['penalty'] = 0;
			} else {
				$ranklist[$uid]['penalty'] += 20*$sum;
			}
		}
		function cmp($a, $b) {
			if($a['solve'] != $b['solve']) {
				return $a['solve'] < $b['solve'];
			} else if($a['penalty'] != $b['penalty'])
			return $a['penalty'] > $b['penalty'];
			else return 0;
		} 
		usort($ranklist, "cmp");
		// 排序
		$rank = 1;
		$this->assign('rank', $rank);
		$this->assign('list', $ranklist);
		$this->assign('len', $clist['pid2']-$clist['pid1']+1);
		$this->assign('head',$clist['title']);
		$this->assign('begin_time',$clist['begin_time']);
		$this->assign('length',$clist['length']);
		$this->display();
	}

	// 秒数转时：分：秒
	private function change($time) {
		$hours = (int)($time / 3600);
		$minutes = (int)($time % 3600 / 60);
		$seconds = (int)($time % 3600 % 60);
		return sprintf('%02d', $hours).':'.sprintf('%02d', $minutes).':'.sprintf('%02d', $seconds);
	}

	//高亮代码显示
	public function showCode(){
		$list = A('Public')->isLogin();
		$this->assign('link1', $list['link1']);
		$this->assign('link2', $list['link2']);
		
		$contest_list = M('contest_list');
		$cid = $_GET['cid'];
		$clist = $contest_list->where('id='.$cid)->find();
		// 页面不存在
		if(!$clist) {
			$this->display('Public:error');
			return ;
		}

		$solution = M('solution');
		$slist = $solution->where('contest_id='.$_GET['cid'])->select();
		// 页面不存在
		if(!$slist) {
			$this->display('Public:error');
			return ;
		}

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
		$this->assign('ptag',chr($pid-$clist['pid1']+65));
		$this->assign('pid',$pid-$clist['pid1']);
		$this->assign('runtime',$runtime);
		$this->assign('memory',$memory);
		$this->assign('result',$result);
		$this->assign('language',$lang);
		$this->assign('length',$length);
		$this->assign('judgetime',$judgetime);

		$this->assign('head',$clist['title']);
		$this->assign('cid', $cid);
		$this->assign('begin_time',$clist['begin_time']);
		$this->assign('length',$clist['length']);
		$this->display();
	}

	public function getTime(){
		echo time();
	}

}
?>