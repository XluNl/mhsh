

//$('.touch_add_nuber').parent('.number').parent('.dispatching_number').parent('.info').parent('.item').children().html()

/*弹出优惠券的函数*/
/*$(".draw_coupon").click(function() {
  $(".draw_coupon_wrp")[0].style.display = "block";
});
$(".came_back").click(function() {
  $(".draw_coupon_wrp")[0].style.display = "none";
});*/



//获取屏幕基本参数
var sHeight = document.documentElement.scrollHeight;
var sWidth = document.documentElement.scrollWidth;
var wHeight = document.documentElement.clientHeight;
var wWidth = document.documentElement.clientWidth;

var top_bar = 55;
var selected_wrp = $('.selected_wrp').outerHeight(true);
var map_wrp_height = wHeight - selected_wrp - top_bar;
//地图部分参数

$(".shoping_cart_wrp").css("min-height",map_wrp_height+"px")
//$(".map_wrp")[0].style.cssText = 'position:fixed;left:0px;top:' + (top_bar) + 'px;width:' + (wWidth) + 'px;height:' + (map_wrp_height) + 'px;';



//remark 参数
var editRemarkObj=null;
//num 参数
var editNumObj=null;
function initGoodItemJs(){
	/*弹出备注的函数*/
	$(".add_editor").click(function() {
	  editRemarkObj = this;
	  if ($(editRemarkObj).html()!="添加备注") {
		  $("#add_remark_input").val($(editRemarkObj).html());
	  }
	  else {
		  $("#add_remark_input").val("");
	  }
	  $(".add_remark_wrp")[0].style.display = "block";
	});
	$(".close_remark").click(function() {
        $(".add_remark_wrp")[0].style.display = "none";
	});
	$(".set_remark").click(function() {
		var goods_name = $(editRemarkObj).parent('.info').find(".name").eq(0).html();
		var mark = $("#add_remark_input").val();
		var data = {goods_name:goods_name,mark:mark,rand:Math.random()};
		var url = "/restaurant/usrmark/change";
        var err = "备注失败";
        var res = static_ajax(data,url,err);
        if (res!= err) {
        	$(editRemarkObj).html(mark);
        }
        $(".add_remark_wrp")[0].style.display = "none";
	});

	/*弹出购物点击数字输入商品数量的函数*/
	$(".close_remark").click(function() {
	  $(".input_goods_number")[0].style.display = "none";
	});

	/*拷贝当前商品详情到弹窗里面*/
	$('.touch_add_nuber').on('click', function() {
	  editNumObj = this;
	  var goodsWrpImg = $(this).parent('.number').parent('.dispatching_number').parent('.info').parent('.item').children(0).html();
	  var goodsWrpTitle = $(this).parent('.number').parent('.dispatching_number').parent('.info').children(0).children(0).html();
	  if (goodsWrpImg) {
	    $(".goods_img")[0].innerHTML = goodsWrpImg;
	    $(".goods_title")[0].innerHTML = goodsWrpTitle;
	  }
	  $("#goods_number_input").val($(editNumObj).html());
	  $(".input_goods_number")[0].style.display = "block";
	});
	/*确认当前商品的数量*/
	$('.set_num').on('click', function() {
	  var goodid = $(editNumObj).parent('.number').parent('.dispatching_number').parent('.info').parent('.item').attr("goodid");
	  var num = isNum($('#goods_number_input').val()); 
	  if (num==undefined||num<0) {
		  alert('请输入正整数');
		  return;
	  }
	  if (cartOpterate(goodid,"modify",num)) {
			cartLocalOpterate(goodid,"modify",num);
	  }
	  else {
			return;
	  }
	  setCartAndItemNum(editNumObj,goodid);
	  $(".input_goods_number")[0].style.display = "none";
	});
	
	/*增加购物车数量的函数plus*/
	$('.item-btn-plus').on('click', function() {
		//增加数量
		var goodid = $(this).parent('.number').parent('.dispatching_number').parent('.info').parent('.item').attr("goodid");
		if (cartOpterate(goodid,"plus",1)) {
			cartLocalOpterate(goodid,"plus",1);
		}
		else {
			return;
		}
	  setCartAndItemNum(this,goodid);
	  return;
	 /* $('.shopping-cart').find('.goods_number span').html(cart["goods_total"]);
	  $(this).find(".single_gooods_number").addClass('on');
	  //$(this).find(".single_gooods_number").html(parseInt($(this).find(".single_gooods_number").html()==""?"0":$(this).find(".single_gooods_number").html())+1);
	  $(this).find(".single_gooods_number").html(cart["goods_list"][goodid]["num"]);*/
	});
	
	/*减少购物车数量的函数*/
	$('.item-btn-minus').on('click', function() {
		//增加数量
		var goodid = $(this).parent('.number').parent('.dispatching_number').parent('.info').parent('.item').attr("goodid");
		if (cartOpterate(goodid,"minus",1)) {
			cartLocalOpterate(goodid,"minus",1);
		}
		else {
			return;
		}
	  setCartAndItemNum(this,goodid);
	  return;
	 /* $('.shopping-cart').find('.goods_number span').html(cart["goods_total"]);
	  $(this).find(".single_gooods_number").addClass('on');
	  //$(this).find(".single_gooods_number").html(parseInt($(this).find(".single_gooods_number").html()==""?"0":$(this).find(".single_gooods_number").html())+1);
	  $(this).find(".single_gooods_number").html(cart["goods_list"][goodid]["num"]);*/
	});
}

function setCartAndItemNum(obj,goodid){
	if (cart["goods_total"]<=0) {
		$(obj).find(".single_gooods_number").removeClass('on');
	}
	else {
		$(obj).find(".single_gooods_number").addClass('on');
	}
	$('.shopping-cart').find('.goods_number span').html(cart["goods_total"]);
	if (cart["goods_list"][goodid]==undefined) {
		$(obj).parent('.number').find(".touch_add_nuber").html("0");
	}
	else {
		$(obj).parent('.number').find(".touch_add_nuber").html(cart["goods_list"][goodid]["num"]);
		  //$(this).find(".single_gooods_number").html(parseInt($(this).find(".single_gooods_number").html()==""?"0":$(this).find(".single_gooods_number").html())+1);
		//$(obj).find(".single_gooods_number").html(cart["goods_list"][goodid]["num"]);
	}
	$('.choose_pay').find('.total span').html(showPrice(cart["price_total"]));
}


