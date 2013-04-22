/*
	Author: KidLet
	Date:   2013-03-08

	For SCNU OJ Common Javascript lib
*/

// Initiall catch event
(function CatchEvent () {

	var i;

	bind ( $('login-btn'), 'click', Login);
	document.body.onclick=LoginCanceled;


})()


// display or none for login div
function Login () {

	if ($('header-login').style.display=="block")
		$('header-login').style.display = "none";
	else
	{
		$('header-login').style.display = "block";
		$('header-login-username').focus();
	}
		

	return false;
}

// when login div blur
function LoginCanceled (event) {
	var e=event || window.event;
	var originTarget=e.srcElement || e.target
		
	if (!originTarget)
		return ;
	
	Target = originTarget;
	while (Target!==document.body)
	{
		if (Target.id=="header-login-btn-cancel")	break;
		if (Target.id=="header-login" || Target.id=="login-btn")
			return;
		Target = Target.parentNode;
	}

	$('header-login').style.display = "none";

}

