    //控制弹出支付的函数

    $("#open_pay").click(function() {
      $(".pay_page")[0].style.display = "block";
    });
    $("#pay_back").click(function() {
      $(".pay_page")[0].style.display = "none";
    });
    //更换支付方式的函数
    $("#change_pay_way").click(function() {
      $(".change_pay_way_page")[0].style.display = "block";
      $(".pay_page")[0].style.display = "none";
    });
    $("#change_pay_way_back").click(function() {
      $(".change_pay_way_page")[0].style.display = "none";
      $(".pay_page")[0].style.display = "block";
    });
    $("#confirm_change_pay").click(function() {
      $(".change_pay_way_page")[0].style.display = "none";
      $(".pay_page")[0].style.display = "block";
    });

    $(".change_pay_way_page ul .pay_way").click(function(){
    	pay_id = $(this).attr('payid');
    	changePayWay();
    	$("#change_pay_way_back").trigger("click");
    });
    
    function changePayWay() {
    	$(".change_pay_way_page ul .pay_way").each(function(){
        	$(this).find('.number').removeClass('on');
        });
    	$('#pay-'+pay_id).find('.number').addClass('on');
    	$('#current_pay').html(payments[pay_id]['name']);
    	$('#current_pay_remark').html(payments[pay_id]['remark']);
    	$('#pay_id_input').val(pay_id);
        pay_category = payments[pay_id]['pay_category'];
	}
    
    