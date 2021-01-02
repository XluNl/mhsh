// pure JS
// var elem = document.getElementById('mySwipe');
var mySwipe = Swipe(document.getElementById('mySwipe'), {
  // startSlide: 4,
  auto: 3000,
  // continuous: true,
  // disableScroll: true,
  // stopPropagation: true,
  callback: function(index, element) {
      slideTab(index);
    }
    // transitionEnd: function(index, element) {}
});
// function addEvent(obj,type,fn){
//     if(obj.attachEvent){
//         obj.attachEvent('on'+type,function(){
//             fn.call(this);
//         });
//     }else{
//         obj.addEventListener(type,fn,false);
//     }
// }
//鐐瑰嚮鏁板瓧瀵艰埅璺宠浆
var bullets = document.getElementById('btn_control').getElementsByTagName('span');
for (var i = 0; i < bullets.length; i++) {
  // (function(i, bullets){
  //   addEvent(bullets[i],'click',function(){
  //     mySwipe.slide(i, 500);
  //   })
  // })(i, bullets);
  var elem = bullets[i];
  elem.setAttribute('data-tab', i);
  elem.onclick = function() {
    mySwipe.slide(parseInt(this.getAttribute('data-tab'), 10), 500);
  }
}
//楂樹寒褰撳墠鏁板瓧瀵艰埅
function slideTab(index) {
  var i = bullets.length;
  while (i--) {
    bullets[i].className = bullets[i].className.replace('on', ' ');
  }
  index = ((index%bullets.length)+bullets.length)%bullets.length;
  bullets[index].className = 'on';
};

// with jQuery
// window.mySwipe = $('#mySwipe').Swipe().data('Swipe');

// url bar hiding
(function() {

  var win = window,
    doc = win.document;

  // If there's a hash, or addEventListener is undefined, stop here
  if (!location.hash || !win.addEventListener) {

    //scroll to 1
    window.scrollTo(0, 1);
    var scrollTop = 1,

      //reset to 0 on bodyready, if needed
      bodycheck = setInterval(function() {
        if (doc.body) {
          clearInterval(bodycheck);
          scrollTop = "scrollTop" in doc.body ? doc.body.scrollTop : 1;
          win.scrollTo(0, scrollTop === 1 ? 0 : 1);
        }
      }, 15);

    if (win.addEventListener) {
      win.addEventListener("load", function() {
        setTimeout(function() {
          //reset to hide addr bar at onload
          win.scrollTo(0, scrollTop === 1 ? 0 : 1);
        }, 0);
      }, false);
    }
  }

})();


 
/*主导航左右滑动的函数*/


