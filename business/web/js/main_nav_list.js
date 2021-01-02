/*弹出备注的函数*/
/*$(".add_editor").click(function() {
  $(".add_remark_wrp")[0].style.display = "block";
});
$(".close_remark").click(function() {
  $(".add_remark_wrp")[0].style.display = "none";
});*/


/*添加到购物车的函数*/
/*$('.add-to-cart').on('click', function() {
  var cart = $('.shopping-cart');
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
        'top': cart.offset().top + 10,
        'left': cart.offset().left + 10,
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
});*/

//滑动固定导航到顶部的函数
var domTopBox = getId('fixed_Top_Wrp');
var scrollEvent = function() {
  var scrollnHeight = document.documentElement.scrollTop || document.body.scrollTop;
  var mainTop = 300;
  if (scrollnHeight >= mainTop) {
    domTopBox.style.cssText = 'position:fixed;left:0;top:0;z-index:5; ';
  } else {
    domTopBox.style.cssText = 'position:relative;left:0px;top:0px';
  }
}

addEvent(window, 'scroll', function() {
  scrollEvent();
});
addEvent(window, 'resize', function() {
  scrollEvent();
});
