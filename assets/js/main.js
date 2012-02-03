//菜单收缩
var subMenu = function(me){
	this.me = me;
	this.menu = this.me.children("dt");
	this.Open = function(){
		this.menu.click(function(){
			var box = $(this).next("dd");					 
			if(box.is(":hidden")) {
				box.show();	
				$(this).addClass("open hover");
			} else {
				box.hide();	
				$(this).removeClass("open hover");
			}				 
		})	
	}
	this.Open();	
}
new subMenu($("#left_nav"));
//
function iFrameHeight() {   
	var ifm= document.getElementById("iframepage");
	var subWeb = document.frames ? document.frames["iframepage"].document : ifm.contentDocument;   
	if(ifm != null && subWeb != null) {
	   ifm.height = subWeb.body.scrollHeight;
	}
}
//
$(document).ready(function(){
	$('li a').click(function() {
		$('li').removeClass('select');
		$(this).parent().addClass('select');
	});
});
