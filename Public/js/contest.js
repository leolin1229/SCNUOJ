/*
js for contest
leolin
*/
/*ajax获取服务器时间*/
function E(str){
	return document.getElementById(str);
}
var xmlHttp, id1;
function createXMLHttpRequest(){
	if(window.ActiveXObject){
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}
	else if(window.XMLHttpRequest){
		xmlHttp = new XMLHttpRequest();
	}
}
function start(){
	createXMLHttpRequest();
	var url="/SCNUOJ/index.php/Contest/getTime";
	xmlHttp.open("GET",url,true);
	xmlHttp.onreadystatechange = callback;
	xmlHttp.send(null);
}
function callback(){
	if(xmlHttp.readyState == 4){
		if(xmlHttp.status == 200){
			E("showtime").value = xmlHttp.responseText;
			id1 = setTimeout("start()",1000);
			var curTime = parseInt(E("showtime").value);
   			var length = parseInt(E("length").value);
   			var begin_time = parseInt(E("begin_time").value);
   			if(curTime == begin_time+length*60){
   				alert("比赛结束！");
   				stoptime(id1);
   			}
		}
	}
}
start();
