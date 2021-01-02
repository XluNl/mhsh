    //控制分享按钮的函数

    $("#open_share_").click(function() {
      $(".mask_share")[0].style.display = "block";
    });
    $("#mask").click(function() {
      $(".mask_share")[0].style.display = "none";
    });

    /*弹出购物点击数字输入商品数量的函数*/
    $(".close_remark").click(function() {
      $(".input_goods_number")[0].style.display = "none";
    });

    /*拷贝当前商品详情到弹窗里面*/
    $('.touch_add_nuber').on('click', function() {
      var goodsWrpImg = $(this).parent('.number').parent('.collection_number').parent('.collection_cart').parent('.app').find("img")[0].outerHTML;
      var goodsWrpTitle = $(this).parent('.number').parent('.collection_number').parent('.collection_cart').parent('.app').find("h2")[0].outerHTML;
      if (goodsWrpImg) {
        $(".goods_img")[0].innerHTML = goodsWrpImg;
        $(".goods_title")[0].innerHTML = goodsWrpTitle;
      }
      $("#goods_number_input").val($(this).html());
      $(".input_goods_number")[0].style.display = "block";
    });

    /*确认当前商品的数量*/
	$('.set_num').on('click', function() {
	  var goodid =list_goods[Object.keys(list_goods)[0]]['id'];
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
	  setCartAndItemNum(goodid);
	  $(".input_goods_number")[0].style.display = "none";
	});
	
	/*增加购物车数量的函数plus*/
	$('.item-btn-plus').on('click', function() {
		//增加数量
		var goodid = list_goods[Object.keys(list_goods)[0]]['id'];
		if (cartOpterate(goodid,"plus",1)) {
			cartLocalOpterate(goodid,"plus",1);
		}
		else {
			return;
		}
	  setCartAndItemNum(goodid);
	  return;
	 /* $('.shopping-cart').find('.goods_number span').html(cart["goods_total"]);
	  $(this).find(".single_gooods_number").addClass('on');
	  //$(this).find(".single_gooods_number").html(parseInt($(this).find(".single_gooods_number").html()==""?"0":$(this).find(".single_gooods_number").html())+1);
	  $(this).find(".single_gooods_number").html(cart["goods_list"][goodid]["num"]);*/
	});
	
	/*减少购物车数量的函数*/
	$('.item-btn-minus').on('click', function() {
		//增加数量
		var goodid = list_goods[Object.keys(list_goods)[0]]['id'];
		if (cartOpterate(goodid,"minus",1)) {
			cartLocalOpterate(goodid,"minus",1);
		}
		else {
			return;
		}
	  setCartAndItemNum(goodid);
	  return;
	 /* $('.shopping-cart').find('.goods_number span').html(cart["goods_total"]);
	  $(this).find(".single_gooods_number").addClass('on');
	  //$(this).find(".single_gooods_number").html(parseInt($(this).find(".single_gooods_number").html()==""?"0":$(this).find(".single_gooods_number").html())+1);
	  $(this).find(".single_gooods_number").html(cart["goods_list"][goodid]["num"]);*/
	});
	
	
    //$('.touch_add_nuber').parent('.number').parent('.dispatching_number').parent('.info').parent('.item').children().html()
	function setCartAndItemNum(goodid){
		if (cart["goods_list"][goodid]==undefined) {
			$('.collection_cart .collection_number .number .touch_add_nuber').html("0");
		}
		else {
			$('.collection_cart .collection_number .number .touch_add_nuber').html(cart["goods_list"][goodid]["num"]);
		}
	}
	
	