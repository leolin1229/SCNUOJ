<?php
class PublicAction extends Action{
	// 验证码
	public function verify() {
		import('ORG.Util.Image');
		Image::buildImageVerify();
	}

	// 登录判断
	public function isLogin() {
		if ($_SESSION['user_id'] == null) {    // 游客
			$link1 = '[<a id="login-btn" href="javascript: void(0);">登录</a>]&nbsp';
			$link2 = '[<a href="__APP__/User/register">注册</a>]';
		} else {               // 已登录
			$link1 = '[<a href="__APP__/User/userStatus?uid=' . $_SESSION['user_id'] . '">' . $_SESSION['user_id'] . '</a>]&nbsp';
			$link2 = '[<a href="__APP__/User/logout">注销</a>]';
		}
		$list['link1'] = $link1;
		$list['link2'] = $link2;
		return $list;
	}

	// 关于页面
	public function about() {
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		$this->display();
	}

	// FAndQ
	public function FAndQ() {
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		$this->display();
	}

	// 新手进阶
	public function Beginner(){
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		$this->display("Beginner");
	}

	// 权限出错
	public function error(){
		$list2 = A('Public')->isLogin();
		$this->assign('link1', $list2['link1']);
		$this->assign('link2', $list2['link2']);

		$this->display();
	}
}
?>