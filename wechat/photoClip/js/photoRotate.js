//iOS解决 图片旋转90度函数  带  压缩功能
function getImgData(img, dir,setSize, next) {
	var image = new Image();
	image.onload = function() {
		var degree = 0,
			drawWidth, drawHeight, width, height;
		drawWidth = this.naturalWidth;
		drawHeight = this.naturalHeight;
		//以下改变一下图片大小
		var maxSide = Math.max(drawWidth, drawHeight);
		
		//宽度 高度最大值为 setSize回调函数调用
		if (maxSide > setSize) {
			var minSide = Math.min(drawWidth, drawHeight);
			minSide = minSide / maxSide * setSize;
			maxSide = setSize;
			if (drawWidth > drawHeight) {
				drawWidth = maxSide;
				drawHeight = minSide;
			} else {
				drawWidth = minSide;
				drawHeight = maxSide;
			}
		}
		var canvas = document.createElement('canvas');
		canvas.width = width = drawWidth;
		canvas.height = height = drawHeight;
		var context = canvas.getContext('2d');

		if(dir!=undefined){
			console.log('图片重力感应：'+dir);

			//判断图片方向，重置canvas大小，确定旋转角度，iphone默认的是home键在右方的横屏拍摄方式
			switch (dir){
				//iphone横屏拍摄，此时home键在左侧
				case 3:
					degree = 180;
					drawWidth = -width;
					drawHeight = -height;
					break;
					//iphone竖屏拍摄，此时home键在下方(正常拿手机的方向)
				case 6:
					canvas.width = height;
					canvas.height = width;
					degree = 90;
					drawWidth = width;
					drawHeight = -height;
					break;
					//iphone竖屏拍摄，此时home键在上方
				case 8:
					canvas.width = height;
					canvas.height = width;
					degree = 270;
					drawWidth = -width;
					drawHeight = height;
					break;
			}

		}else{
			console.log('图片重力感应：无');
		}
		
		//使用canvas旋转校正
		context.rotate(degree * Math.PI / 180);
		context.drawImage(this, 0, 0, drawWidth, drawHeight);
		//返回校正图片   图片/压缩质量0~1,
		next(canvas.toDataURL("image/jpeg",0.9));

	}
	image.src = img;
}