var img_base =  '<?=\Yii::getAlias("@publicImageUrl")  ?>';
function ifImgNotExists(obj){
    obj.src = "<?= common\models\Common::getDefaultImageUrl();?>"
    obj.onerror=null;
}

function static_ajax(data,url,err){
    var result = "";
    $.ajax({
        url:url,
        data:data,
        async: false,
        type:"GET",
        dataType:"json",
        success:function(res){
            if(typeof(res) !== "undefined" && typeof(res["status"]) !== "undefined"){
                if(res["status"]){
                    result = res["data"];
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

function static_ajax_post(data,url,err){
    var result = "";
    $.ajax({
        url:url,
        data:data,
        async: false,
        type:"POST",
        dataType:"json",
        success:function(res){
            if(typeof(res) !== "undefined" && typeof(res["status"]) !== "undefined"){
                if(res["status"]){
                    result = res["data"];
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


function clearNoNum(obj)
{
    //先把非数字的都替换掉，除了数字和.
    obj.value = obj.value.replace(/[^\d.]/g,"");
    //必须保证第一个为数字而不是.
    obj.value = obj.value.replace(/^\./g,"");
    //保证只有出现一个.而没有多个.
    obj.value = obj.value.replace(/\.{2,}/g,".");
    //保证.只出现一次，而不能出现两次以上
    obj.value = obj.value.replace(".","$#$").replace(/\./g,"").replace("$#$",".");
}

function getAysncCallback(url,func,errorMsg) {
    errorMsg = errorMsg===undefined?"网络错误,请稍后重试":errorMsg;
    func = function(data){
        bootbox.alert("操作成功");
    }
    $.get(url,function(data){
        if (data===undefined){
            bootbox.alert(errorMsg);
        }
        else if (data.status===false){
            bootbox.alert(data.error);
        }
        else {
            func(data);
        }
    });
}