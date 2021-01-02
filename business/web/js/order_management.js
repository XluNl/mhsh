 //控制退单理由的函数

 $(".chargeback_order").click(function() {
   $(".chargeback_order_reason")[0].style.display = "block";
 });
 $("#mask").click(function() {
   $(".chargeback_order_reason")[0].style.display = "none";
 });
