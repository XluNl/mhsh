//水平滑动
//获取整个框的width
var allwidth=$(".menu_recommend").width();//整个宽度
var liwidth=$(".container li").eq(0).width();//单个内容宽度
var screenwidth=$("html body").width();//屏幕宽度
var firstli=$(".container li").eq(0).offset().left;//初始位置
var leftwidth=(screenwidth-(firstli*2)-liwidth)/2+firstli;//左右空出的位置
 $(".view_more .allnum").text($(".container1 li").length);
var distanceli=firstli//li之间的距离
function scrollpic1(){
	var fatherbox=$(".container1").offset().left;//父框
    var nowfirstli=$(".container1 li").eq(0).offset().left;//当前第一个元素相对位置
	var key=Math.abs(fatherbox)/liwidth;
	if(key<0.5){
		$(".view_more .nownum1").text(1);
	}else{
	   $(".view_more .nownum1").text(" ");
	   $(".view_more .nownum1").text(parseInt(key.toFixed(0))+1);
		
	}
}
var playtime = setInterval(scrollpic1,100);

function scrollpic2(){
	var fatherbox=$(".container2").offset().left;//父框
    var nowfirstli=$(".container2 li").eq(0).offset().left;//当前第一个元素相对位置
	var key=Math.abs(fatherbox)/liwidth;
	if(key<0.5){
		$(".view_more .nownum2").text(1);
	}else{
	   $(".view_more .nownum2").text(" ");
	   $(".view_more .nownum2").text(parseInt(key.toFixed(0))+1);
		
	}
}
var playtime = setInterval(scrollpic2,100);