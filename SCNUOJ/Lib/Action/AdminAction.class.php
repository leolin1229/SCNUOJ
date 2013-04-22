<?php
// addProblem,updateProblem
class AdminAction extends Action{
	public function index(){
		if($_SESSION['user_id']!="admin"){
			$this->display('Public:error');
			return ;
		}
		$this->display();
	}
	public function addProblem(){
		if($_SESSION['user_id']!="admin"){
			$this->display('Public:error');
			return ;
		}
		$problem = M("problem");
		$cnt = $problem->count();
		$pid = $cnt+1000;
		$this->assign('pid', $pid);
		$this->display();
	}
	public function addProcess(){
		$problem = M('problem');
		$condition['title'] = $_POST['title'];
		$condition['description'] = $_POST['description'];
		$condition['time_limit'] = intval($_POST['time']);
		$condition['memory_limit'] = intval($_POST['memory']);
		$condition['input'] = $_POST['input'];
		$condition['output'] = $_POST['output'];
		$condition['sample_input'] = $_POST['sin'];
		$condition['sample_output'] = $_POST['sout'];
		$condition['hint'] = $_POST['hint'];
		$condition['from'] = $_POST['source'];

		$result = $problem->add($condition);
		if($result==null){
			echo "!!!";
		} else {
			$this->display('addProblem');
		}
	}
	public function updateProblem(){
		if($_SESSION['user_id']!="admin"){
			$this->display('Public:error');
			return ;
		}
		$this->display();
	}
	public function update(){
		$problem = M('problem');
		header('Content-type:text/html;charset=utf-8');
		$start = intval($_POST['hdu-text1']);
		$end = intval($_POST['hdu-text2']);
		for($i=$start; $i<=$end; $i++){
			$url='http://acm.hdu.edu.cn/showproblem.php?pid='.$i; 
			$lines_string = file_get_contents($url);
			$lines_string = iconv("gb2312", "utf-8//IGNORE", $lines_string);
			$title = '/<h1.*>(.*)<\/h1>/is';
			if(preg_match($title, $lines_string, $match)){
				$condition['title'] = htmlspecialchars($match[1]);
			}
			$time = '/Time\sLimit:\s2000\/(.*)\sMS/';
			if(preg_match($time, $lines_string, $match)){
				$condition['time_limit'] = intval($match[1]);
			}
			$memory = '/Memory\sLimit:\s65536\/(.*)\sK/';
			if(preg_match($memory, $lines_string, $match)){
				$condition['memory_limit'] = intval($match[1]);
			}
			$desc = '/Problem\sDescription<\/div>\s<div\sclass=panel_content>(.*)<\/div><div\sclass=panel_bottom>&nbsp;<\/div><br><div\sclass=panel_title\salign=left>Input/is';
			if(preg_match($desc, $lines_string, $match)){
				$flag1 = str_ireplace('../../data/images', 'http://acm.hdu.edu.cn/data/images', $match[1]);
				$flag2 = str_ireplace('/data/images', 'http://acm.hdu.edu.cn/data/images', $match[1]);
				if($flag1!=$match[1]){
					$condition['description'] = htmlspecialchars($flag1);
				} else if($flag2!=$match[1]){
					$condition['description'] = htmlspecialchars($flag2);
				} else {
					$condition['description'] = htmlspecialchars($match[1]);
				}
			}
			$input = '/Input<\/div>\s<div\sclass=panel_content>(.*)<\/div><div\sclass=panel_bottom>&nbsp;<\/div><br><div\sclass=panel_title\salign=left>Output/is';
			if(preg_match($input, $lines_string, $match)){
				$condition['input'] = htmlspecialchars($match[1]);
			}
			$output = '/Output<\/div>\s<div\sclass=panel_content>(.*)<\/div><div\sclass=panel_bottom>&nbsp;<\/div><br><div\sclass=panel_title\salign=left>Sample\sInput/is';
			if(preg_match($output, $lines_string, $match)){
				$condition['output'] = htmlspecialchars($match[1]);
			}
			$sin = '/Sample\sInput<\/div><div\sclass=panel_content><pre>(.*)<\/pre><\/div><div\sclass=panel_bottom>&nbsp;<\/div><br><div\sclass=panel_title\salign=left>Sample\sOutput/is';
			if(preg_match($sin, $lines_string, $match)){
				$condition['sample_input'] = htmlspecialchars($match[1]);
			}
			$sout = '/Sample\sOutput<\/div><div\sclass=panel_content><pre>(.*)<\/pre>/is';
			if(preg_match($sout, $lines_string, $match)){
				$condition['sample_output'] = htmlspecialchars($match[1]);
			}
			$hint = '/Hint<\/div><i\sstyle=\'font-size:1px\'>\s<\/i>(.*)<\/div><\/pre>/is';
			if(preg_match($hint, $lines_string, $match)){
				$condition['hint'] = htmlspecialchars($match[1]);
			}
			$from = '/Source<\/div>\s<div\sclass=panel_content>\s<a.+>\s(.*)\s<\/a>\s<\/div>/is';
			if(preg_match($from, $lines_string, $match)){
				$condition['from'] = htmlspecialchars($match[1]);
			}
			$result = $problem->add($condition);
		}
		$this->display('index');
	}
	public function testTest(){
		//$testData = M("testdata");
		//$condition['problem_id'] = 1017;
		$this->display();

	}
	public function test() {
		echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
		if ($_FILES["file"]["type"] == "text/plain") {
  			if ($_FILES["file"]["error"] > 0) {
    			echo "Return Code: " . $_FILES["file"]["error"] . "<br>";
    		} else {
    			if (file_exists("Public/data/" . $_FILES["file"]["name"])) {  
      				echo $_FILES["file"]["name"] . " already exists. ";  
      			} else {  
      				$id = move_uploaded_file($_FILES["file"]["tmp_name"],  "Public/data/" . $_FILES["file"]["name"]);  
      				echo "Stored in: " . "/Public/data/" . $_FILES["file"]["name"]."<br>";
      				echo $id;  
      			}  
    		}
  		} else {
  			echo "Invalid file";
  		}
	}
}