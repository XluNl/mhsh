var img_base = '<?=\Yii::getAlias("@publicImageUrl")  ?>';

function ifImgNotExists(obj){
    obj.src = "<?= backend\models\BackendCommon::getBlankImage();?>"
    obj.onerror=null;
}

var addEvent=function(obj,event,fn){
	if(obj.addEventListener){
		obj.addEventListener(event,fn,false);
		}else if(obj.attachEvent){
			obj.attachEvent('on'+event,fn);
			}
	}
var getId = function (id){return  document.getElementById(id);}


function accAdd(arg1, arg2) {  
    var r1, r2, m;  
    try {  
        r1 = arg1.toString().split(".")[1].length;  
    }  
    catch (e) {  
        r1 = 0;  
    }  
    try {  
        r2 = arg2.toString().split(".")[1].length;  
    }  
    catch (e) {  
        r2 = 0;  
    }  
    m = Math.pow(10, Math.max(r1, r2));  
    return (arg1 * m + arg2 * m) / m;  
}  

Number.prototype.add = function (arg) {  
    return accAdd(arg, this);  
}; 

function Subtr(arg1, arg2) {  
    var r1, r2, m, n;  
    try {  
        r1 = arg1.toString().split(".")[1].length;  
    }  
    catch (e) {  
        r1 = 0;  
    }  
    try {  
        r2 = arg2.toString().split(".")[1].length;  
    }  
    catch (e) {  
        r2 = 0;  
    }  
    m = Math.pow(10, Math.max(r1, r2));  
     //last modify by deeka  
     //动态控制精度长度  
    n = (r1 >= r2) ? r1 : r2;  
    return ((arg1 * m - arg2 * m) / m).toFixed(n);  
}  

Number.prototype.sub = function (arg) {  
    return Subtr(this, arg);  
};  

function showPrice(x) { 
	var f = parseFloat(x); 
	if (isNaN(f)) { 
	    return; 
	} 
	f = Math.round(x)/1000; 
	return f; 
} 

function isNum(x) { 
	var f = parseInt(x); 
	if (isNaN(f)) { 
	    return undefined; 
	} 
	return f; 
}

// common js

/**
 * 短信验证码倒计时
 */
function phoneCodeSendTime(o,wait) {
	if (wait == 0) {
		$(o).removeAttr("disabled");
		$(o).html("重新获取验证码");
	} else {
		$(o).attr("disabled", true);
		$(o).html("重新发送(" + wait + ")");
		wait--;
		setTimeout(function() {
			phoneCodeSendTime(o,wait);
		}, 1000)
	}
}
/**
 * 验证手机号
 * @param mobile
 * @returns {Boolean}
 */
function checkMobile(mobile) {
    var re = /^1[3,4,5,7,8]\d{9}$/;
    if (re.test(mobile)) {
        return true;
    } else {
        return false;
    }
}
/**
 * 同步AJAX
 * @param data
 * @param url
 * @param err
 * @returns {String}
 */
function static_ajax(data,url,err){
    var result = '';
    $.ajax({
        url:url,
        data:data,
        async: false,
        type:"GET",
        dataType:"json",
        success:function(res){
            if(typeof(res) != 'undefined' && typeof(res['status']) != 'undefined'){
                if(res['status']){
                    result = res['data']; 
                }else{
                    alert(res["error"]);
                }
            }else{
                alert(err);
            }
        },
        error:function(){
            alert(err);
        }
    });
    return result;
} 


/**
 * 同步POST AJAX
 * @param data
 * @param url
 * @param err
 * @returns {String}
 */
function static_post_ajax(data,url,err){
    var result = '';
    $.ajax({
        url:url,
        data:data,
        async: false,
        type:"POST",
        dataType:"json",
        success:function(res){
            if(typeof(res) != 'undefined' && typeof(res['status']) != 'undefined'){
                if(res['status']){
                    result = res['data']; 
                }else{
                    alert(res["error"]);
                }
            }else{
                alert(err);
            }
        },
        error:function(){
            alert(err);
        }
    });
    return result;
}
/**
 * 异步AJAX
 * @param data
 * @param url
 * @param err
 * @returns {String}
 */
function static_ajax_async(data,url,err){
    var result = '';
    $.ajax({
        url:url,
        data:data,
        async: true,
        type:"GET",
        dataType:"json",
        success:function(res){
            if(typeof(res) != 'undefined' && typeof(res['status']) != 'undefined'){
                if(res['status']){
                    result = res['data']; 
                }else{
                    alert(res["error"]);
                }
            }else{
                alert(err);
            }
        },
        error:function(){
            alert(err);
        }
    });
    return result;
} 

/**
 * 购物车操作
*/
function parseIntWithoutNan(arg1){
	arg1 = parseInt(arg1);
	if (isNaN(arg1)) {
		arg1 = 0;
	}
	return arg1;
}
function numIntAdd(arg1,arg2){
	arg1 = parseIntWithoutNan(arg1);
	arg2 = parseIntWithoutNan(arg2);
	return parseInt(arg1)+parseInt(arg2);
}
function numIntSub(arg1,arg2){
	arg1 = parseIntWithoutNan(arg1);
	arg2 = parseIntWithoutNan(arg2);
	return parseInt(arg1)-parseInt(arg2);
}

function cartOpterate(goods_id,command,num){
    var data = {goods_id:goods_id,command:command,goods_num:num,rand:Math.random()};
    var url = "/cart/operate";
    var err = "购物车操作失败";
    var res = static_ajax(data,url,err);
	if(res == "success"){
		return true;
	}else{
		return false;
	}
}


function cartLocalOpterate(goods_id,command,num){
	num = parseIntWithoutNan(num);
    if(command=="minus"){
    	if (cart["goods_list"][goods_id]!=undefined) {
    		if (cart["goods_list"][goods_id]["num"]-num<=0) {
        		cart["goods_total"] = numIntSub(cart["goods_total"],cart["goods_list"][goods_id]["num"]);
        		cart["price_total"]=numIntSub(cart["price_total"], parseIntWithoutNan(cart["goods_list"][goods_id]["price"])*parseIntWithoutNan(cart["goods_list"][goods_id]["num"]));
        		delete(cart["goods_list"][goods_id]);
    		}
        	else {
        		cart["goods_total"] = numIntSub(cart["goods_total"], num);
        		cart["goods_list"][goods_id]["num"] =  parseIntWithoutNan(cart["goods_list"][goods_id]["num"])- num;
        		cart["price_total"] =numIntSub( cart["price_total"],parseIntWithoutNan(cart["goods_list"][goods_id]["price"])*num);
    		}
    		return true;
		}
    	return false;
    }
    else if (command=="plus") {
    	if (cart["goods_list"][goods_id]==undefined) {
    		var newGood={};
    		newGood["name"] = list_goods[goods_id]['name'];
    		newGood["unit"] = list_goods[goods_id]['unit'];
    		newGood["price"] = list_goods[goods_id]['price'];
    		newGood["net_price"] = list_goods[goods_id]['net_price'];
    		newGood["id"] = list_goods[goods_id]['id'];
    		newGood["num"] = num;
    		cart["goods_list"][goods_id] = newGood;
		}
    	else {
    		cart["goods_list"][goods_id]["num"] = numIntAdd(cart["goods_list"][goods_id]["num"],num);
		}
    	cart["goods_total"] = numIntAdd(cart["goods_total"],num) ;
		cart["price_total"] = numIntAdd(cart["price_total"], parseIntWithoutNan(cart["goods_list"][goods_id]["price"])*num);
		return true;
	}
    else if (command=="modify") {
    	if (num>=0) {
    		if (cart["goods_list"][goods_id]==undefined) {
        		var newGood={};
        		newGood["name"] = list_goods[goods_id]['name'];
        		newGood["unit"] = list_goods[goods_id]['unit'];
        		newGood["price"] = list_goods[goods_id]['price'];
        		newGood["net_price"] = list_goods[goods_id]['net_price'];
        		newGood["id"] = list_goods[goods_id]['id'];
        		newGood["num"] = 0;
        		cart["goods_list"][goods_id] = newGood;
    		}
    		var per_num = parseIntWithoutNan(cart["goods_list"][goods_id]["num"]);
        	var cha_num = num - per_num;
        	cart["goods_list"][goods_id]["num"]=num;
        	cart["goods_total"] = numIntAdd(cart["goods_total"],cha_num);
    		cart["price_total"] = numIntAdd(cart["price_total"],parseIntWithoutNan(cart["goods_list"][goods_id]["price"])*cha_num);
        	if (num==0) {
        		delete(cart["goods_list"][goods_id]);
			}
		}
    	else {
			alert('数量不能小于0');
			return false;
		}
    	return true;
	}
    return false;
}

 


