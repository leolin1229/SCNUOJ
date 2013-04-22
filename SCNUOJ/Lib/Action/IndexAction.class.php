<?php
// 本类由系统自动生成，仅供测试用途
class IndexAction extends Action {
	function index() 
	{
		// 注册信息
		$list1 = A('Public')->isLogin();
		$this->assign('link1', $list1['link1']);
		$this->assign('link2', $list1['link2']);

		// Realtime Ranklist
		$solution = M("solution");
		$list2 = $solution->where('contest_id is null')->order('solution_id DESC')->limit(10)->select();
		for ($i = 0; $i < count($list2); $i++) {

			if ($list2[$i]['result'] == 'Accepted')
				$list2[$i]['resultColor'] = '<span class="Result-AC">Accepted</span>';
			else if ($list2[$i]['result'] == 'Presentation Error')
				$list2[$i]['resultColor'] = '<span class="Result-PE">Presentation Error</span>';
			else if ($list2[$i]['result'] == 'Pending')
				$list2[$i]['resultColor'] = '<span class="Result-Pend">Pending</span>';
			else if ($list2[$i]['result']=='Compile Error')
				$list2[$i]['resultColor'] = '<span class="Result-CE">Compile Error</span>';
			else
				$list2[$i]['resultColor'] = '<span class="Result-Other">' . $list2[$i]['result'] . '</span>';
		}
    	$this->assign('list2', $list2);

		// Top 10
		$user = M('user');
		$list3 = $user->order('solved DESC')->limit(10)->select();
		$rank = '1';
    	$this->assign('list3', $list3);
    	$this->assign('rank', $rank);

    	// Recent Contest
    	$rcinfo = M('contestinfo');
    	$rclist = $rcinfo->select();
    	$this->assign('rclist', $rclist);
    	
		$this->display();
	}
}
?>
