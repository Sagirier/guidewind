<!DOCTYPE html>
<html lang="zh">
<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"> 
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>文件批量上传</title>

<link rel="stylesheet" type="text/css" href="system/js/ssiupload/css/bootstrap.min.css" />
<link rel="stylesheet" type="text/css" href="system/js/ssiupload/css/style.css">
<link rel="stylesheet" href="system/js/ssiupload/css/ssi-uploader.css"/>

</head>
<body>
<div class="container">
	<div class="container">
		<div class="row">
			<div class="col-md-12">
				<input type="file" multiple id="ssi-upload"/>
			</div>
			<div class="hidden" id="onAll"></div>
		</div>
	</div>
</div>

<script src="system/js/ssiupload/js/jquery-2.1.1.min.js" type="text/javascript"></script>
<script src="system/js/ssiupload/js/ssi-uploader.js"></script>
<script type="text/javascript">
	$('#ssi-upload').ssi_uploader({
		url:'<?php echo $GLOBALS["WP"]["url"]["url_path_base"];?>?action=uploads&o=editorfilemanager&filesdir=<?php echo $filesdir; ?>',
		imgContainers:"onAll",
		dropZone:false,
		allowed:['jpg','jpeg','gif','bmp','png'],
		maxNumberOfFiles:"5"
	});
</script>
</body>
</html>