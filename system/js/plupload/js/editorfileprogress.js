function FileProgress(file, targetID) {
	this.fileProgressID = file.id;

	this.opacity = 100;
	this.height = 0;

	this.fileProgressWrapper = document.getElementById("fsUploadProgress");
	this.fileProgressElement = document.getElementById(this.fileProgressID);
	this.fileProgressPercent = document.getElementById(this.fileProgressID+"_Percent");
	this.fileProgressStatu = document.getElementById(this.fileProgressID+'_Statu');
	if (!this.fileProgressElement) {
		this.fileProgressElement = document.createElement("div");
		this.fileProgressElement.className = "progressWrapper";
		this.fileProgressElement.id = this.fileProgressID;

		
		var typeimg = '<i class="fa fa-file-o"></i>';
		if(file.type==".jpg" || file.type==".jpeg" || file.type==".png" || file.type==".gif" || file.type==".bmp"){
			typeimg = '<i class="fa fa-picture-o"></i>';
		}
		if(file.type==".rar" || file.type==".zip" || file.type==".7z"){
			typeimg = '<i class="fa fa-gift"></i>';
		}
		if(file.type==".mp4" || file.type==".flv" || file.type==".swf"){
			typeimg = '<i class="fa fa-film"></i>';
		}
		if(file.type==".wmv" || file.type==".wma" || file.type==".mp3"){
			typeimg = '<i class="fa fa-music"></i>';
		}
		if(file.type==".txt"){
			typeimg = '<i class="fa fa-file-text"></i>';
		}
		if(file.type==".doc" || file.type==".docx"){
			typeimg = '<i class="fa fa-file-word-o"></i>';
		}
		if(file.type==".xls" || file.type==".xlsx"){
			typeimg = '<i class="fa fa-file-excel-o"></i>';
		}
		var progressImg = document.createElement("div");
		progressImg.className = "progressImg";
		progressImg.innerHTML = typeimg;
		
		var progressText = document.createElement("h3");
		progressText.className = "progressName";
		progressText.appendChild(document.createTextNode(file.name));
		
		var progressSize = document.createElement("span");
		progressSize.className = "progressSize";
		var filesize = file.size;
		if(filesize/1024<1){
			progressSize.appendChild(document.createTextNode(file.size+'B'));
		}else if(filesize/1024>1 && filesize/1024 <1024){
			progressSize.appendChild(document.createTextNode(Math.round(file.size/1024)+'KB'));
		}else{
			progressSize.appendChild(document.createTextNode(Math.round(file.size/1024/1024)+'MB'));
		}
		
		var progressBar = document.createElement("span");
		progressBar.className = "progressStrip";
		progressBar.innerHTML = '<div class="progress progress-striped active"><div id="'+this.fileProgressID+'_Percent" aria-valuemax="100" aria-valuemin="0" role="progressbar" class="progress-bar progress-bar-danger"></div></div>';
		
		var progressStatus = document.createElement("span");
		progressStatus.id = this.fileProgressID + "_Statu";
		progressStatus.className = "progressBarStatus";
		progressStatus.innerHTML = "等待上传";
		
		var progressCancel = document.createElement("span");
		progressCancel.innerHTML = '<button type="button" class="btn btn-danger" id="'+this.fileProgressID+'_Cancel"><i class="fa fa-times"></i> 取消</button>';
		
		this.fileProgressElement.appendChild(progressImg);
		this.fileProgressElement.appendChild(progressText);
		this.fileProgressElement.appendChild(progressSize);
		this.fileProgressElement.appendChild(progressBar);
		this.fileProgressElement.appendChild(progressStatus);
		this.fileProgressElement.appendChild(progressCancel);

		document.getElementById(targetID).appendChild(this.fileProgressElement);
	} else {
		this.fileProgressElement = this.fileProgressWrapper.firstChild;
	}

	this.height = this.fileProgressWrapper.offsetHeight;

}
FileProgress.prototype.setProgress = function (percentage) {
	this.fileProgressPercent.style.width = percentage + "%";
	this.fileProgressPercent.innerHTML = percentage+"%";
};
FileProgress.prototype.setComplete = function () {
	this.fileProgressElement.className = "progressContainer blue";
	document.getElementById(this.fileProgressID+'_Cancel').className = "btn btn-default";
	document.getElementById(this.fileProgressID+'_Cancel').disabled = "disabled";

//	var oSelf = this;
//	setTimeout(function () {
//		oSelf.disappear();
//	}, 10000);
};
FileProgress.prototype.setError = function () {
	this.fileProgressElement.className = "progressContainer red";
	this.fileProgressElement.childNodes[3].className = "progressBarError";
	this.fileProgressElement.childNodes[3].style.width = "";

//	var oSelf = this;
//	setTimeout(function () {
//		oSelf.disappear();
//	}, 5000);
};
FileProgress.prototype.setCancelled = function () {
	document.getElementById(this.fileProgressID).className = "progressContainer red";
	document.getElementById(this.fileProgressID+'_Cancel').className = "btn btn-default";
	document.getElementById(this.fileProgressID+'_Cancel').disabled = "disabled";

//	var oSelf = this;
//	setTimeout(function () {
//		oSelf.disappear();
//	}, 2000);
};

FileProgress.prototype.setStatus = function (percent) {
	this.fileProgressStatu.innerHTML = percent;	
};

// Show/Hide the cancel button
FileProgress.prototype.toggleCancel = function (show, swfUploadInstance) {
	if (swfUploadInstance) {
		var fileID = this.fileProgressID;
		this.fileProgressCancel = document.getElementById(this.fileProgressID+'_Cancel');
		
		this.fileProgressCancel.onclick = function () {
			swfUploadInstance.cancelUpload(fileID);
			this.className = 'btn btn-default';
			this.disabled = "disabled";
		};
	}
};

// Fades out and clips away the FileProgress box.
FileProgress.prototype.disappear = function () {

	var reduceOpacityBy = 15;
	var reduceHeightBy = 4;
	var rate = 30;	// 15 fps

	if (this.opacity > 0) {
		this.opacity -= reduceOpacityBy;
		if (this.opacity < 0) {
			this.opacity = 0;
		}

		if (this.fileProgressWrapper.filters) {
			try {
				this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
			} catch (e) {
				// If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
				this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
			}
		} else {
			this.fileProgressWrapper.style.opacity = this.opacity / 100;
		}
	}

	if (this.height > 0) {
		this.height -= reduceHeightBy;
		if (this.height < 0) {
			this.height = 0;
		}

		this.fileProgressWrapper.style.height = this.height + "px";
	}

	if (this.height > 0 || this.opacity > 0) {
		var oSelf = this;
		setTimeout(function () {
			oSelf.disappear();
		}, rate);
	} else {
		this.fileProgressWrapper.style.display = "none";
	}
};