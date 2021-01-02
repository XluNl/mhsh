//remark 参数
var editRemarkObj=null;

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

	/*添加到购物车的函数*/
	$('.add-to-cart').on('click', function() {
		//增加数量
		var goodid = $(this).parent('.price_cart').parent('.info').parent('.item').attr("goodid");
		if (cartOpterate(goodid,"plus",1)) {
			cartLocalOpterate(goodid,"plus",1);
		}
		else {
			return;
		}
		//添加动画
	  var cartIcon = $('.shopping-cart');
	  var imgtodrag = $(this).parent('.price_cart').parent('.info').parent('.item').find("img").eq(0);
	  if (imgtodrag) {
	    var imgclone = imgtodrag.clone()
	      .offset({
	        top: imgtodrag.offset().top,
	        left: imgtodrag.offset().left
	      })
	      .css({
	        'opacity': '0.5',
	        'position': 'absolute',
	        'height': '110px',
	        'width': '110px',
	        'z-index': '100'
	      })
	      .appendTo($('body'))
	      .animate({
	        'top': cartIcon.offset().top + 10,
	        'left': cartIcon.offset().left + 10,
	        'width': 75,
	        'height': 75
	      }, 1000, 'easeInOutExpo');

	    imgclone.animate({
	      'width': 0,
	      'height': 0
	    }, function() {
	      $(this).detach()
	    });
	  }
	  $('.shopping-cart').find('.goods_number').addClass('on');
	  //$('.shopping-cart').find('.goods_number span').html(parseInt($('.shopping-cart').find('.goods_number span').html()==""?"0":$('.shopping-cart').find('.goods_number span').html())+1);
	  $('.shopping-cart').find('.goods_number span').html(cart["goods_total"]);
	  $(this).find(".single_gooods_number").addClass('on');
	  //$(this).find(".single_gooods_number").html(parseInt($(this).find(".single_gooods_number").html()==""?"0":$(this).find(".single_gooods_number").html())+1);
	  $(this).find(".single_gooods_number").html(cart["goods_list"][goodid]["num"]);
	});
}
initGoodItemJs();

function initCart() {
	
}
