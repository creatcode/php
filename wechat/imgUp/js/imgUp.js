//单车项目 未用到
(function(mui, window, document) {
	mui.init();
	
	
	var get = function(id) {
		return document.getElementById(id);
	};
	var qsa = function(sel) {
		return [].slice.call(document.querySelectorAll(sel));
	};
	var ui = {
		imageList: get('image-list')
	};
	ui.clearForm = function() {
		ui.imageList.innerHTML = '';
	};
	ui.getFileInputArray = function() {
		return [].slice.call(ui.imageList.querySelectorAll('input[type="file"]'));
	};
	
	var imageIndexIdNum = 0;
	ui.newPlaceholder = function() {
		var fileInputArray = ui.getFileInputArray();
		if (fileInputArray &&
			fileInputArray.length > 0 &&
			fileInputArray[fileInputArray.length - 1].parentNode.classList.contains('space')) {
			return;
		}
		imageIndexIdNum++;
		
		var placeholder = document.createElement('div');
		placeholder.setAttribute('class', 'image-item space');
		var closeButton = document.createElement('div');
		closeButton.setAttribute('class', 'image-close');
		closeButton.innerHTML = 'X';
		
		closeButton.addEventListener('click', function(event) {
			event.stopPropagation();
			event.cancelBubble = true;
			setTimeout(function() {
				ui.imageList.removeChild(placeholder);
			}, 0);
			return false;
		}, false);
		var fileInput = document.createElement('input');
		fileInput.setAttribute('type', 'file');
		fileInput.setAttribute('accept', 'image/*');
		fileInput.setAttribute('id', 'image-' + imageIndexIdNum);
		fileInput.addEventListener('change', function(event) {
			var file = fileInput.files[0];
			if (file) {
				
				//iOS重力感应图片旋转90°处理
				EXIF.getData(file, function(){
					console.log('获取图片所有信息：'+JSON.stringify(EXIF.getAllTags(this)))
					EXIF.getAllTags(this);
					
					//获取图片重力感应 Orientation参数：  1:0°  /  6:顺时针90°  /  8:逆时针180°  /  3:180°
					var orientation = EXIF.getTag(this, 'Orientation');
					console.log('获取图片重力感应：'+EXIF.getTag(this, 'Orientation'));
					
					
					var reader = new FileReader();
					
					reader.onload = function() {
						//处理 android 4.1 兼容问题
						//var base64 = reader.result.split(',')[1];
						//var dataUrl = 'data:image/png;base64,' + base64;
						//console.log('Base64图片：'+dataUrl);
						//placeholder.style.backgroundImage = 'url(' + dataUrl + ')';
						
						
						//输出图片
						getImgData(this.result, orientation,500, function(data) {
							//这里可以使用校正后的图片data了
							console.log('Base64图片：'+data);
							placeholder.style.backgroundImage = 'url(' + data + ')';
							placeholder.setAttribute("data-img",data);
						});
						
					}
					
					
					reader.readAsDataURL(file);
					placeholder.classList.remove('space');
					ui.newPlaceholder();
					
					
					return false;
				});
			}
		}, false);
		
		placeholder.appendChild(closeButton);
		placeholder.appendChild(fileInput);
		ui.imageList.appendChild(placeholder);
		
		
		//不能上传超过3张
		if($('#image-list').children().length==2){
			$('.image-item.space').hide();
		}else{
			$('.image-item.space').show();
		}
		$('.image-close').on('tap',function(){
			$('.image-item.space').show();
		});
		
		
	};
	ui.newPlaceholder();
	
})(mui, window, document);
