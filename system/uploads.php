<?php if(!defined('APP_PATH')||!defined('WIND_PATH')){exit('Access Denied');} ?>
<!DOCTYPE>
<html lang=zh>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title></title>
<link href="manage/admin/template/css/bootstrap.min.css?v=3.4.0" rel="stylesheet">
<link href="manage/admin/template/font-awesome/css/font-awesome.css?v=4.3.0" rel="stylesheet">
<link href="manage/admin/template/css/style.css?v=2.2.0" rel="stylesheet">

<style>
	.swfupload{display: inline-block;float: left;margin-right: 5px; }
	.progressImg i{font-size: 40px; vertical-align: middle;}
	.progressStrip .progress,.progressBarComplete .progress,.progressBarError .progress{margin: 10px 0;}
</style>

<script src="system/js/swfupload.js" type="text/javascript"></script>
<script src="system/js/plupload/js/swfupload.queue.js" type="text/javascript"></script>
<script src="system/js/plupload/js/fileprogress.js" type="text/javascript"></script>
<script src="manage/admin/template/js/plugins/layer/layer.js" type="text/javascript"></script>
<script src="system/js/plupload/js/handlers.js" type="text/javascript"></script>
<script type="text/javascript">
	var swfu;
	window.onload = function() {
		var settings = {
			flash_url : "system/js/plupload/swfupload.swf",
			file_post_name : "Filedata",
			upload_url: "<?php echo $GLOBALS['WWW']; ?>admin.php?action=uploads&o=filemanager&filesdir=<?php echo $filesdir; ?>",	// Relative to the SWF file
			post_params: {"PHPSESSID" : "<?php echo session_id(); ?>"},
			file_size_limit : <?php echo $sizeLimit; ?>,
			file_types : "<?php echo $fileExt; ?>",
			file_types_description : "<?php echo $filesType; ?>",
			file_upload_limit : <?php echo $fileover; ?>,
			custom_settings : {
				progressTarget : "fsUploadProgress",
				startButtonId : "btnStart",
				cancelButtonId : "btnCancel"
			},
			debug: false,

			// Button settings
			button_image_url: "manage/admin/template/img/upload.png",	// Relative to the Flash file
			button_width: "99",
			button_height: "34",
			button_placeholder_id: "spanButtonPlaceHolder",
			//button_text: '',
			//button_text_style: ".theFont { font-size: 16; }",
			//button_text_left_padding: 12,
			//button_text_top_padding: 3,

			// The event handler functions are defined in handlers.js
			file_queued_handler : fileQueued,
			file_queue_error_handler : fileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : uploadSuccess,
			upload_complete_handler : uploadComplete,
			queue_complete_handler : queueComplete	// Queue plugin event
		};
		swfu = new SWFUpload(settings);
    };
    var d;//保存上传成功图片的路径信息
    function ShowData(file, serverData) {
        d = serverData.split(":");
        if (d[0] == "ok") {
            $("#divContent").css("backgroundImage","url("+d[1]+")").css("width",d[2]+"px").css("height",d[3]+"px");
        }
    };
    $(function () {
        $("#divCut").draggable({ containment: 'parent' }).resizable({ containment: '#divContent' });
        $("#btnCut").click(function () {
            var y = $("#divCut").offset().top - $("#divContent").offset().top;
            var x = $("#divCut").offset().top - $("#divContent").offset().top;
            var width = $("#divCut").width();
            var height = $("#divCut").height();
            $.post("<?php echo $GLOBALS['WWW']; ?>admin.php?action=uploads&o=filemanager&filesdir=<?php echo $filesdir; ?>", { "action": "cut", "x": parseInt(x), "y": parseInt(y), "width": parseInt(width), "height": parseInt(height), "imgSrc": d[1] }, function (data) {
                $("#imgSrc").attr("src",data);
            });
        });
	});
</script>
</style>
</head>

<body style="background: none;">
<div class="wrapper wrapper-content animated fadeInRight">
    <div class="row">
    	<?php if (!$_SESSION['auser']){ message("登陆超时");} ?>
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>文件上传</h5>
                </div>
                <div class="ibox-content">
                	<table class="table table-bordered article-table">
                		<thead>
                			<tr>
                				<td>文件类型</td>
                				<td>文件名</td>
                				<td>文件大小</td>
                				<td>上传进度</td>
                				<td>上传状态</td>
                				<td>操作</td>
                			</tr>
                		</thead>
                		<tbody id="fsUploadProgress"></tbody>
                	</table>
					<div>
						<span id="spanButtonPlaceHolder"></span>
						<button id="btnStart" type="button" onclick="swfu.startUpload();" disabled="disabled" class="btn btn-info"><i class="fa fa-upload"></i> 开始上传</button>
						<button id="btnCancel" type="button" onclick="swfu.cancelQueue();" disabled="disabled" class="btn btn-danger"><i class="fa fa-times"></i> 取消上传</button>
					</div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include "manage/admin/template/js.html"; ?>
<script src="manage/admin/template/js/plugins/layer/layer.min.js" type="text/javascript"></script>
<script>
	function returnServerData(serverData){
		<?php if($fileover==1){ ?>
		self.parent.document.getElementById("<?php echo $inputid; ?>").value=serverData;
		<?php }else{ ?>
		var pannelid="img_pannel_"+Math.floor(Math.random()*10000); //产生(1000-9999)范围的随机id便于删除该pannel
		var imgcontailer=self.parent.document.getElementById("<?php echo $inputid; ?>");
		var imgpannel=document.createElement("div");
			imgpannel.className="img-pannel product-picture";
			imgpannel.id=pannelid;
			delpannel='"'+pannelid+'"';
		var imgr=document.createElement("img");
			imgr.className="img-image";
			imgr.src=serverData;
		var inps=document.createElement("div");
			inps.style.display="none";
			inps.innerHTML="<input type='hidden' value='"+serverData+"' name='product_picture[]' />";
		var inpsname=document.createElement("div");
			inpsname.innerHTML="<input class='form-control no-clip' name='product_picturename[]' />";
		var orders=document.createElement("div");
			orders.innerHTML="<span class='img-order'>顺序：</span><input class='form-control no-clip W-20' name='product_pictureorder[]' />";
		var del=document.createElement("div");
			del.className="delimg";
			del.innerHTML="<a class='btn btn-danger' onclick='imgpannelDel("+delpannel+");'><i class='fa fa-times'></i> 删除</a>"; //删除按钮

			imgcontailer.appendChild(imgpannel);
			imgpannel.appendChild(imgr);
			imgpannel.appendChild(inps);
			imgpannel.appendChild(inpsname);
			imgpannel.appendChild(orders);
			imgpannel.appendChild(del);
		<?php } ?>
	}
</script>
</body>
</html>
