<?php
class UserAction extends Action {
	
	function checkId($data) {   // 检查注册ID
		for ($i = 0; $i < strlen($data); $i++) {
			if ('0' <= $data[$i] && $data[$i] <= '9') continue;
			if ('a' <= $data[$i] && $data[$i] <= 'z') continue;
			if ('A' <= $data[$i] && $data[$i] <= 'Z') continue;
			if ($data[$i] == '_') continue;
			return false;
		}
		return true;
	}
	function checkLength($data) {   // 检查密码长度
		if(strlen($data) < 6 || strlen($data) > 15) {
			return false;
		} else return true;
	}
	function checkEmail($data) {   // 检查Email
		if ($data == null)
			return true;
		if (preg_match ( "/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/", $data))
			return true;
		return false;
	}
	function getIp() {
		return $_SERVER['REMOTE_ADDR'];
	}
	function getTime() {
		return date('Y-m-d H:i:s');
	}

	// logout
	public function logout() {
		//在这里删除用户登录信息
		$_SESSION['user_id'] = null;
		header("Location: ".$_SERVER['HTTP_REFERER']);
	}
	public function action() {
		$act = $_GET['action'];

	}
	public function checkLogin() {
		if($_SESSION['user_id']!=null) {
			$this->display('Public:error');
			return ;
		}
		$condition['user_id'] = $_POST['user_id'];
		$condition['password'] = md5($_POST['password']);
		$user = M('user');
		$result = $user->where($condition)->find();
		if ($result) {
			// 在这里记录用户已登录的信息
			$_SESSION['user_id'] = $_POST['user_id'];
			if($_POST['flag']=="1") {
				header("Location: ".$_SERVER['HTTP_REFERER']);
			} else if($_POST['flag']=="0"){
				$url = $_COOKIE['ref'];
				header("location: ".$url);
			}
		} else {
			$list2 = A('Public')->isLogin();
			$this->assign('link1', $list2['link1']);
			$this->assign('link2', $list2['link2']);
			$this->assign('info', '用户名/密码错误!！');
			if($_SERVER['HTTP_REFERER']!="http://127.0.0.1".__APP__."/User/checkLogin") {
				setcookie('ref',$_SERVER['HTTP_REFERER']);
			}
			$this->display();
		}
	}

	// Register
	public function checkRegister() {

		$info = '';
		if ($_POST['user_id'] == null || $_POST['user_id']=='' || !isset($_POST['user_id']) )
			$info = $info . '用户名不能为空！<br />';

		if ($_POST['password'] == null || $_POST['repassword'] == null || !isset($_POST['password']))
			$info = $info . '密码不能为空！<br />';
		else if ($_POST['password'] != $_POST['repassword'])
		 	$info = $info . '两次输入的密码不一致！<br />';

		if ( $info!="" )
		{
			$list2 = A('Public')->isLogin();
			$this->assign('link1', $list2['link1']);
			$this->assign('link2', $list2['link2']);

			$this->assign ('info', $info);
			$this->display ('register');
			return ;
		}
			

		$user = M('User');
		$condition['user_id'] = $_POST['user_id'];
		if ($user->where($condition)->find())
			$info = $info . '用户名已存在！<br />';
			//$this->ajaxReturn('','用户名已存在！',0);
		else if ($this->checkId($_POST['user_id']) == false)
			$info = $info . '用户名只能包含数字、英文字母以及下划线！<br />';
			//$this->ajaxReturn('','用户名只能包含数字、英文字母以及下划线！',0);
		if ($this->checkLength($_POST['password']) == false)
			$info = $info . '密码长度必须为6 - 15位！';
			//$this->ajaxReturn('','密码长度必须为6 - 15位！',0);
		if ($this->checkEmail($_POST['email']) == false)
			$info = $info . '用户邮箱不合法！';
			//$this->ajaxReturn('','用户邮箱不合法！',0);

		if ( $info!="" )
		{
			$list2 = A('Public')->isLogin();
			$this->assign('link1', $list2['link1']);
			$this->assign('link2', $list2['link2']);

			$this->assign ('info', $info);
			$this->display ('register');
			return ;
		}

		$condition['user_id'] = $_POST['user_id'];
		$condition['password'] = md5($_POST['password']);
		$condition['school'] = $_POST['school'];
		$condition['email'] = $_POST['email'];
		$condition['motto'] = $_POST['motto'];
		$condition['is_share'] = $_POST['is_share'];

		$condition['ip'] = $this->getIp();
		$condition['reg_time'] = $this->getTime();
		$condition['ac_ratio'] = '0.00';

		$result = $user->add($condition);
		if ($result) {
			$_SESSION['user_id'] = $_POST['user_id'];
			header("location: " . __APP__ );
			//$this->ajaxReturn('','',1);
		}
		else {
			$this->display ('Public:error');
			//$this->ajaxReturn('','注册失败！',0);
		}
	}

	// 修改用户信息
	public function setting() {
		$user = M('user');
		$res = $user->find($_GET['uid']);
		if($res==null){
			$this->display('Public:error');
			return ;
		}

		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		$this->assign('uid', $_GET['uid']);

		$this->display();
	}
	public function checkSetting() {

		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		if ($_SESSION['user_id'] == null) {
			$this->assign('errorinfo', '用户没有登录！');
			$this->display('Public:error');
			return ;
			//$this->ajaxReturn('','用户没有登录！',0);
		}
		$user = M('user');
		$condition['user_id'] = $_SESSION['user_id'];
		$condition['password'] = md5($_POST['oldPassword']);
		$result = $user->where($condition)->find();

		if ($result) {
			// 用户名、旧密码正确
			$data = $result;
			if ($_POST['password']) {
				if ($_POST['password'] == $_POST['repassword']) {
					if ($this->checkLength($_POST['password']) == false)
					{
						$this->assign('info', '密码长度必须为6 - 15位');
						$this->display('setting');
						return;
					}
						//$this->ajaxReturn('','密码长度必须为6 - 15位',0);
					else if ($_POST['password'])
						$data['password'] = md5($_POST['password']);
				}
				else {
					$this->assign('info', '两次输入的新密码不一致！');
					$this->display('setting');
					return;
					//$this->ajaxReturn('','两次输入的新密码不一致！',0);
				}
			} // 修改密码
			if ($this->checkEmail($_POST['email']) == false)
			{
				$this->assign('info', '邮箱不合法！');
				$this->display('setting');
				return;
				//$this->ajaxReturn('','邮箱不合法！',0);
			}
				
			else
				$data['email'] = $_POST['email'];
			if ($_POST['school'])
				$data['school'] = $_POST['school'];
			if ($_POST['motto'])
				$data['motto'] = $_POST['motto'];
			$data['is_share'] = $_POST['is_share'];

			$info = $user->save($data); // 更新数据库
			if ($info) {
				header( "location: ". __APP__ . "/User/userStatus?uid=" . $_SESSION['user_id'] );
				//$this->ajaxReturn('','',1);
			} else {
				$this->assign('errorinfo', '更新数据失败！');
				$this->display('Public:error');
				return ;
				//$this->ajaxReturn('','更新数据失败！',0);
			}
		} else {

			$this->assign('info', '旧密码错误');
			$this->display('setting');
			return;
			//$this->ajaxReturn('','旧密码错误！',0);
		}
	} //检测更新信息

    // 查询用户个人信息
	public function userStatus() {

		$list1 = A('Public')->isLogin();
		$this->assign('link1', $list1['link1']);
		$this->assign('link2', $list1['link2']);

		$user = M('user');
		$list2 = $user->find($_GET['uid']);
		if($list2==null){
			$this->display('Public:error');
			return ;
		}
		$list2['ac_ratio'] = number_format($list2['solved']/$list2['submit']*100,2);
		$this->assign('list', $list2);

		// 用户信息
		$solved = $list2['solved'];
		$rank = $user->where('solved>'.$solved)->count();
		$rank++;
		$this->assign('rank', $rank);

		$problem = M('problem');
		$count = $problem->count();
		import('ORG.Util.Page');
		$p = new Page($count,15);
		$page = $p->show();
		$list3 = $problem->select();

		// 判断题目是否被AC
		$solution = M('solution');
		for ($i = 0; $i < count($list3); $i++) {
			$con1['user_id'] = $_GET['uid'];
			$con1['problem_id'] = $list3[$i]['problem_id'];
			$con2['user_id'] = $_GET['uid'];
			$con2['problem_id'] = $list3[$i]['problem_id'];
			$con2['result'] = 'Accepted';

			$list3[$i]['problemColor'] = '<a href="__APP__/Problem/showProblem?pid='.$list3[$i]["problem_id"].'"><span class="badge">'.$list3[$i]['problem_id'].'</span></a>';
			if ($solution->where($con1)->count() > 0)
			{
				$list3[$i]['problemColor'] = '<a href="__APP__/Problem/showProblem?pid='.$list3[$i]["problem_id"].'"><span class="badge badge-important">'.$list3[$i]['problem_id'].'</span></a>';
				if ($solution->where($con2)->count() > 0)
					$list3[$i]['problemColor'] = '<a href="__APP__/Problem/showProblem?pid='.$list3[$i]["problem_id"].'"><span class="badge badge-success">'.$list3[$i]['problem_id'].'</span></a>';
			}
		}
		$this->assign('list3', $list3);

		$this->assign('uid', $_GET['uid']);
		$this->display();
	}

	public function user() {
		$user = M('user');
		$count = $user->count();
		import('ORG.Util.Page');
		$p = new Page($count,15);//第二参数：每页行数
		$page = $p->show();
		$list = $user->order('solved DESC')->limit($p->firstRow.','.$p->listRows)->select();
		$rank = $p->firstRow;
		$rank++;

		// 赋值
    	$this->assign('page', $page);

    	for ($i = 0; $i < count($list); $i++) {
    		$list[$i]['ac_ratio'] = number_format($list[$i]['solved']/$list[$i]['submit']*100,2);
    	}

    	
    	$this->assign('list', $list);
    	$this->assign('rank', $rank);

		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		$this->display('ranklist');
	}
	// 
	public function search() {
		if ($_GET['user'] == null)
		{
			$this->user();
			return;
		}
		$user = M('user');
		$condition['user_id'] = $_GET['user'];
		import('ORG.Util.Page');
		$p = new Page(1,15);
		$page = $p->show;
		$list = $user->where($condition)->select();
		$another = $user->where($condition)->find();
		$solved = $another['solved'];
		$rank = $user->where("solved > '$solved'")->count();
		$rank++;

		$this->assign('page', $page);
		$this->assign('list', $list);
		$this->assign('rank', $rank);

		$list2 = A('Public')->isLogin();
		$this->assign('link1', $link2['link1']);
		$this->assign('link2', $list2['link2']);

		$this->display('ranklist');
	}

	public function register()
	{
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);
		$this->display('register');
	}
}
