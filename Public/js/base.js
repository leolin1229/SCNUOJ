/*
	Author: KidLet
	Date:   2013-03-08

	For SCNU OJ Base Javascript lib
*/

function $(str) {
	if ( typeof str=="string" )
		return document.getElementById(str);
	else
		return str;
}

function bind(node, eventType, bindFunction) 
{ 
	if ( !(node=$(node)) )  return false;

	if ( node.addEventListener )
	{
		//W3C
		node.addEventListener ( eventType, bindFunction, false );

		return true;
	}
	else if ( node.attachEvent )
	{
		//IE
		node['e'+eventType+bindFunction] = bindFunction;
		node[eventType+bindFunction] = function(){node['e'+eventType+bindFunction]( window.event );}
		node.attachEvent( 'on'+eventType, node[eventType+bindFunction] );
		return true;
	}
}

function getElementsByClassName(node,classname) {
  if (node.getElementsByClassName) { // use native implementation if available
    return node.getElementsByClassName(classname);
  } 
  else {
    return (function getElementsByClass(searchClass,node) {
        if ( node == null )
          node = document;
        var classElements = [],
            els = node.getElementsByTagName("*"),
            elsLen = els.length,
            pattern = new RegExp("(^|\\s)"+searchClass+"(\\s|$)"), i, j;

        for (i = 0, j = 0; i < elsLen; i++) {
          if ( pattern.test(els[i].className) ) {
              classElements[j] = els[i];
              j++;
          }
        }
        return classElements;
    })(classname, node);
  }
}