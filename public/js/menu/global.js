// JavaScript Document

function setMenu(evt)
{
	menu_title_Obj.innerHTML = evt.data.name;
	
	menu_left_Obj.innerHTML = '';//锟斤拷锟皆拷锟斤拷锟斤拷锟斤拷械慕诘锟?
	for(key in menu_child[evt.data.id].items)
	{
		var locationHTML = ' > '+ evt.data.name;
		locationHTML = locationHTML + ' > ' + menu_child[evt.data.id].items[key].name; 
		var dt = document.createElement('dt');
		//li.className = 'focus';
		dt.style.cursor = "pointer";
		var a = document.createElement('a');
		a.innerHTML = menu_child[evt.data.id].items[key].name;
		$(a).bind('click',{t_id:menu_child[evt.data.id].items[key].id,t_title:menu_child[evt.data.id].items[key].name,t_url:menu_child[evt.data.id].items[key].url,t_isClosed:1,location:locationHTML},AddTab);//t_id,t_title,t_url,t_isClosed
		dt.appendChild(a);
		
		//AddEvents(li,Foo);
		menu_left_Obj.appendChild(dt);
	}
}
//锟斤拷取页锟斤拷实锟绞达拷小
function getPageSize(){ 
    
    var xScroll, yScroll; 
    
    if (window.innerHeight && window.scrollMaxY) {    
        xScroll = document.body.scrollWidth; 
        yScroll = window.innerHeight + window.scrollMaxY; 
    } else if (document.body.scrollHeight > document.body.offsetHeight){ // all but Explorer Mac 
        xScroll = document.body.scrollWidth; 
        yScroll = document.body.scrollHeight; 
    } else { // Explorer Mac...would also work in Explorer 6 Strict, Mozilla and Safari 
        xScroll = document.body.offsetWidth; 
        yScroll = document.body.offsetHeight; 
    } 
    
    var windowWidth, windowHeight; 
    if (self.innerHeight) {    // all except Explorer 
        windowWidth = self.innerWidth; 
        windowHeight = self.innerHeight; 
    } else if (document.documentElement && document.documentElement.clientHeight) { // Explorer 6 Strict Mode 
        windowWidth = document.documentElement.clientWidth; 
        windowHeight = document.documentElement.clientHeight; 
    } else if (document.body) { // other Explorers 
        windowWidth = document.body.clientWidth; 
        windowHeight = document.body.clientHeight; 
    }    
    
    // for small pages with total height less then height of the viewport 
    if(yScroll < windowHeight){ 
        pageHeight = windowHeight; 
    } else { 
        pageHeight = yScroll; 
    } 

    // for small pages with total width less then width of the viewport 
    if(xScroll < windowWidth){    
        pageWidth = windowWidth; 
    } else { 
        pageWidth = xScroll; 
    } 

    arrayPageSize = new Array(pageWidth,pageHeight,windowWidth,windowHeight) 
    return arrayPageSize; 
}

//删锟斤拷锟斤拷页
function selectAll(){
	var obj = document.getElementsByName("rid[]");
	for (var i=0;i<obj.length;i++){
		//if (obj[i].name == "rid"){
			obj[i].checked = true;
		//}
	}
}

function unselectAll(){
	var obj = document.getElementsByName("rid[]");
	for (var i=0;i<obj.length;i++){
		//if (obj[i].name == "rid"){
			if (obj[i].checked==true) obj[i].checked = false;
			else obj[i].checked = true;
		//}
	}
}

function getSelectItem(){
	var obj = document.getElementsByName("rid[]");
	var selected = 0;
	for (var i=0;i<obj.length;i++){
		if (obj[i].checked==true) selected = selected + 1;
	}
	return selected;
}

function sameArr(Arr1,Arr2){//锟叫讹拷锟斤拷锟斤拷锟斤拷锟斤拷锟角凤拷锟斤拷锟斤拷同锟斤拷元锟截ｏ拷锟斤拷锟斤拷锟角硷拷锟斤拷锟斤拷
	if(Arr1 == Arr2){
		//alert("a=b");
		return true;
	}
	if(Arr1.length != Arr2.length){
		//alert("length not same");
		return false;
	}
	
	for(i=0;i<Arr1.length;i++)
	{
		if(!in_array(Arr1[i],Arr2)){
			return false;
		}
	}
	return true;
}

function in_array(e,Arr)
{
	for(i=0;i<this.length;i++)
	{
		if(this[i] == e)
		return true;
	}
	return false;
}
//去锟斤拷js锟斤拷锟斤拷锟叫碉拷锟截革拷值
function ljq_toObject(a) { 
	var o = {}; 
	for (var i=0, j=a.length; i<j; i=i+1) { // 锟斤拷锟斤拷锟揭碉拷锟斤拷锟斤拷锟斤拷, YUI源锟斤拷锟斤拷锟斤拷i<a.length 
		o[a[i]] = true; 
	} 
	return o; 
}
function ljq_keys(o) { 
	var a=[], i; 
	for (i in o) { 
		if (o.hasOwnProperty(i)) { // 锟斤拷锟斤拷, YUI源锟斤拷锟斤拷锟斤拷lang.hasOwnProperty(o, i) 
			a.push(i); 
		} 
	} 
	return a; 
}
function jsArrUniq(a) {//只锟斤拷要锟斤拷锟矫憋拷锟斤拷锟斤拷
	return ljq_keys(ljq_toObject(a)); 
}

function func_exists(fun){ 
	try{
		fun = eval(fun+";");
		if(fun && typeof(fun)=="function"){ 
			return true;
		}else{ 
			return false;
		}
	}catch(e){
		return false;
	} 
}
//alert(func_exists('ajax_prepare'));
var canCallNext = true;//锟角凤拷锟斤拷锟街达拷锟斤拷锟揭伙拷锟絘jax
function ajax_update(controller,datas,passby){
	jQuery.ajax({
		type: "post",
		//url: "http://www.wechatzf2local.com.cn/new.php?action=gettopic",
		url: "index.php?d=admindir&c="+controller+"&m=ajaxupdate",//?action=info&pre=6&member=1&article=1&yesterday=1&online=1
		data:datas,
		
		beforeSend: function(XMLHttpRequest){
			func_exists('ajax_prepare') && ajax_prepare(passby);
			canCallNext = false;
			//ShowLoading();
		},
		success: function(data, textStatus){
			func_exists('ajax_callback') && ajax_callback(data,passby);
		},
		
		complete: function(XMLHttpRequest, textStatus){
			canCallNext = true;
			XMLHttpRequest = null;
			func_exists('ajax_complete') && ajax_complete(passby);
			//HideLoading();
		},
		error: function(){
			canCallNext = true;
			func_exists('ajax_error') && ajax_error(passby);
		//锟斤拷锟斤拷锟斤拷?锟斤拷
		}
	});

}
function ajax_add(controller,func,datas,passby){
	jQuery.ajax({
		type: "post",
		//url: "http://www.wechatzf2local.com.cn/new.php?action=gettopic",
		url: "index.php?d=admindir&c="+controller+"&m="+func,//?action=info&pre=6&member=1&article=1&yesterday=1&online=1
		data:datas,
		
		beforeSend: function(XMLHttpRequest){
			func_exists('ajax_prepare') && ajax_prepare(passby);
			canCallNext = false;
			//ShowLoading();
		},
		success: function(data, textStatus){
			func_exists('ajax_callback') && ajax_callback(data,passby);
		},
		
		complete: function(XMLHttpRequest, textStatus){
			canCallNext = true;
			XMLHttpRequest = null;
			func_exists('ajax_complete') && ajax_complete(passby);
			//HideLoading();
		},
		error: function(){
			canCallNext = true;
			func_exists('ajax_error') && ajax_error(passby);
		//锟斤拷锟斤拷锟斤拷?锟斤拷
		}
	});

}
