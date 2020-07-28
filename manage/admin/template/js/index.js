/**
 * Created by anps0907 on 2/5/2015.
 */


$("#btn").live("click",function(){
    layer.alert("这是一个弹窗",1,"提示", function (index) {
        alert("dfff");
        closeDilag(index);
    });
});

/***
 * 弹出询问框
 */
$("#delete").live("click", function () {
    layer.confirm("确认删除", function (index) {
        alert("确认删除");
    },"提示",function(index){
        closeDilag(index);
    });
});
/**
 * tip
 * */
$("#contip").live("mouseover", function () {
    layer.tips('比如实现用户小名片什么的呀', this, {
        style: ['background-color:#0FA6D8; color:#fff', '#0FA6D8'],
        maxWidth:150
    });
});
$("#contip").live("mouseout", function () {
    layer.closeTips();
});

/**
 * 上传控件
 **/

$("#ftp").live("click", function () {
    layer.load(2,0,true);
});


$("#ftp").live("click", function () {

    layer.msg('删除成功', 2, function(){
        location.reload(); //自动关闭后可做一些刷新页面等操作
    });
    /**
    $.layer({
        type : 1,
        title : false,
        fix : false,
        shade : [0.5 , '#000' , false],
        offset:['50px' , ''],
        closeBtn: [1, true],
        area : ['515px','615px'],
        page : {dom : '#divsp'}
    });**/
})






/**
 * 关闭弹窗
 * */
var closeDilag = function(index){
    layer.close(index);
}




