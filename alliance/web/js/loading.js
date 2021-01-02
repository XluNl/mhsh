/*弹出地图选地址部分的函数*/
$(".oppen_map_select").click(function() {
  $(".select_map_wrp")[0].style.display = "block";
  showmap();
});
$(".close_map_select").click(function() {
  $(".select_map_wrp")[0].style.display = "none";
});


//获取屏幕基本参数
var sHeight = document.documentElement.scrollHeight;
var sWidth = document.documentElement.scrollWidth;
var wHeight = document.documentElement.clientHeight;
var wWidth = document.documentElement.clientWidth;

var top_bar = 55;
var selected_wrp = $('.selected_wrp').outerHeight(true);
var map_wrp_height = wHeight - selected_wrp - top_bar-111;
//地图部分参数
$(".map_wrp")[0].style.cssText = 'position:fixed;left:0px;top:' + (top_bar) + 'px;width:' + (wWidth) + 'px;height:' + (map_wrp_height) + 'px;';
