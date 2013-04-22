<?php
class SolutionAction extends Action{
	public function index(){
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);
		
		$solution = M("solution");
		$count = $solution->where('contest_id is null')->select();
		$count = count($count);
		import("ORG.Util.Page");
		$p = new Page($count,15);//第二参数：每页行数
		$page = $p->show();
		$list = $solution->where('contest_id is null')->order('solution_id DESC')->limit($p->firstRow.','.$p->listRows)->select();

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
				$list[$i]['share'] = '<a target="_blank" href="__APP__/Problem/showCode?solution_id='.$list[$i]['solution_id'].'">'.$list[$i]['solution_id'].'</a>';
			} else $list[$i]['share'] = $list[$i]['solution_id'];
		}

		// 赋值
		$this->assign('page', $page);
		$this->assign('list', $list);

		$this->display("showStatus");
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
		$solution = M("solution");
		if(!$this->chkBlank($_GET['user'])){
			$condition['user_id'] = $_GET['user'];
		} else $_GET['user'] = null;
		if(!$this->chkBlank($_GET['problem'])){
			$condition['problem_id'] = $_GET['problem'];
		} else $_GET['problem'] = null;
		if($_GET['result']!="All"){
			$condition['result'] = $_GET['result'];
		}
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
				$list[$i]['resultColor'] = '<a class="Result-CE">Compile Error</a>';
			} else $list[$i]['resultColor'] = '<span class="Result-Other">' . $list[$i]['result'] . '</span>';

			if ($list[$i]['is_share']=="Yes" || $list[$i]['user_id']==$_SESSION['user_id']){
				$list[$i]['share'] = '<a href="__APP__/Problem/showCode?solution_id='.$list[$i]['solution_id'].'">'.$list[$i]['solution_id'].'</a>';
			} else $list[$i]['share'] = $list[$i]['solution_id'];
		}
		$this->assign('page',$page);
		$this->assign('list',$list);
		$this->assign('uid',$_GET['user']);
		$this->assign('pid',$_GET['problem']);
		$this->assign('res',$_GET['result']);

		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		$this->display("showStatus");
	}

	//提交页面
	public function solutionSubmit(){
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);
		
		$pid = $_GET ['pid'];
		$this->assign('pid',$pid);
		$this->display();
	}
	//提交处理
	public function submit(){
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
		$condition['problem_id'] = $_POST['pid'];
		$condition['user_id'] = $_SESSION['user_id'];
		$condition['judgetime'] = date('Y-m-d H:i:s');
		$condition['result'] = "Pending";
		$condition['code_length'] = strlen($condition['source']);
		$condition['language'] = $_POST['language'];
		$condition['is_share'] = $_POST['is_share'];
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

		// 
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

		$this->redirect('__APP__/Solution/index');
	}
	// 代理提交(抓取用)
	public function submit2(){
		if($_SESSION['user_id']==null){
			$this->display('Public:error');
			return ;
		}
		
		// solution
		$solution = M("solution");
		$condition['source'] = html_entity_decode($_POST['src']);
		$condition['problem_id'] = $_POST['pid'];
		$condition['user_id'] = $_SESSION['user_id'];
		$condition['judgetime'] = date('Y-m-d H:i:s');
		$tt = $condition['judgetime'];
		$condition['result'] = "Pending";
		$condition['code_length'] = strlen($condition['source']);
		$condition['language'] = $_POST['language'];
		$condition['is_share'] = $_POST['is_share'];
		$solution_id = $solution->add($condition);

		//code
		$src_code = M("source_code");
		$condition['solution_id'] = $solution_id;
		$src_code->add($condition);

		// 获取cookie
		$hduid = 'leolin';
		$hdupass = '8899644';
		$cookie = dirname(__FILE__).'/cookie.txt';
		$post = array(
			'username' => $hduid,
			'userpass' => $hdupass,
			'login' => 'Sign In'
			);
		$curl = curl_init('http://acm.hdu.edu.cn/userloginex.php?action=login');
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_COOKIEJAR, $cookie); // Cookie
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
		curl_exec($curl);
		curl_close($curl);

		// 使用上面保存的cookies再次访问
		$curl = curl_init('http://acm.hdu.edu.cn/submit.php?action=submit');
		$post = array(
			'check' => '0',
			'problemid' => $_POST['pid'],
			'language' => '0',
			'usercode' =>  html_entity_decode($_POST['src'])
			);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_COOKIEFILE, $cookie); // Cookie
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
		$res = curl_exec($curl);
		curl_close($curl);

		// 获取结果
		$codeResult = 'Queuing';//Compiling
		$codeTime = 0;
		$codeMem = 0;
		$codeLen = 0;
		// while ($codeResult=='Queuing' || $codeResult=='Compiling'){
		// for($i=0;$i<3;$i++){
		// 	sleep(1);
		header('Content-type:text/html;charset=utf-8');
		$url = 'http://acm.hdu.edu.cn/status.php?first=&pid='.$_POST['pid'].'&user='.$hduid.'&lang=0&status=0';
		$lines_string = file_get_contents($url);
		$lines_string = iconv("gb2312", "utf-8//IGNORE", $lines_string);
		$query1 = '/<input\stype=submit\svalue=Go\sclass=button40\sstyle="height:22px;margin-top:-3px"><\/center><\/form><\/td><\/tr><tr\salign=center\s><td\sheight=22px>(.*?)<\/td>/';
		if(preg_match($query1, $lines_string, $match)){
			$num = htmlspecialchars($match[1]);
			$query2 = '/<tr\salign=center\s><td\sheight=22px>'.$num.'<\/td><td>(.*?)<\/td><td><font\scolor=red>(.*?)<\/font><\/td><td><a(.*?)>(.*?)<\/a><\/td><td>(.*?)MS<\/td><td>(.*?)K<\/td><td>(.*?)B<\/td><td>G++/';
			if(preg_match($query2, $lines_string, $match)){
				$codeResult = $match[2];
				$codeTime = (int)$match[5];
				$codeMem = (int)$match[6];
				$codeLen = (int)$match[7];
			}
		}
		// }
		// 更新solution表
		$solution = M('solution');
		$con['judgetime'] = $tt;
		$tmp = $solution->where($con)->find();
		$tmp['result'] = $codeResult;
		$tmp['runtime'] = $codeTime;
		$tmp['memory'] = $codeMem;
		$tmp['code_length'] = $codeLen;
		$solution->where($con)->save($tmp);

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

		$this->redirect('__APP__/Solution/index');
	}



	public function ce_error() {
		$ce = M("compileinfo");
		$condition['solution_id'] = $_GET['sid'];
		$result = $ce->where($condition)->find();
		if($result==null) {
			$list2 = A('Public')->isLogin();
			$this->assign('link1', $list2['link1']);
			$this->assign('link2', $list2['link2']);
			$this->display("notExist");
			return ;
		}
		$solution = M("solution");
		$solo = $solution->where($condition)->find();
		$this->assign('sid',$result['solution_id']);
		$this->assign('uid',$solo['user_id']);
		$this->assign('error',$result['error']);
		$this->assign('pid',$solo['problem_id']);
		$this->assign('res',$solo['result']);
		$this->display();
	}
}
?>