<?php
class UserModel extends Model{
	protected $_validate = array(
		array("user_id","require","用户名不能为空！"),
		array("user_id","checkId","用户名只能包含数字、英文字母以及下划线！",0,'callback'),
		//array("user_id","unique","用户名已存在！"),
		//array("nick","require","昵称不能为空！"),
		array("password","require","密码不能为空！"),
		array("password","checkLength","密码长度至少6位！",0,'callback',2),
		array("password","repassword","两次密码输入不一致！",0,'confirm'),
		array("email","checkEmail","邮箱不合法！",0,'callback'),
		);
	protected $_auto = array(
		array("password","md5",3,'function'),
		array("reg_time","getTime",3,'callback'),
		array("ip","getIp",3,'callback'),
		array("nick","getNick",3,'callback'),
		);
	function checkId($data) {
		for ($i = 0; $i < strlen($data); $i++) {
			if ('0' <= $data[$i] && $data[$i] <= '9') continue;
			if ('a' <= $data[$i] && $data[$i] <= 'z') continue;
			if ('A' <= $data[$i] && $data[$i] <= 'Z') continue;
			if ($data[$i] == '_') continue;
			return false;
		}
		return true;
	}
	function checkLength($data) {
		if(strlen($data) < 6 || strlen($data) > 15) {
			return false;
		} else return true;
	}
	function checkEmail($data) {
		if ($data == null)
			return true;
		if (preg_match ( "/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/", $data))
			return true;
		return false;
	}
	function getIp() {
		return $_SERVER['REMOTE_ADDR'];
	}
	function getNick() {
		return $_POST['user_id'];
	}
	function getTime() {
		return date('Y-m-d H:i:s');
	}
}