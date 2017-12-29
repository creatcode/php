var getwxconfurl = app.baseUrl() + 'route=system/common/wechat_jssdk';
var appId, timestamp, nonceStr, signature;

//调起 微信JSSDK
$.ajax({
	url: getwxconfurl,
	type: "post",
	data: {},
	dataType: "json",
	success: function(result) {
		console.log(result);
		if (result.errorCode == 0) {
			
			appId = result.data.appId;
			timestamp = result.data.timestamp;
			nonceStr = result.data.nonceStr;
			signature = result.data.signature;
			wx.config({
				debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
				appId: appId, // 必填，公众号的唯一标识
				timestamp: timestamp, // 必填，生成签名的时间戳
				nonceStr: nonceStr, // 必填，生成签名的随机串
				signature: signature, // 必填，签名，见附录1
				jsApiList: [
					'onMenuShareTimeline',//分享到朋友圈
					'onMenuShareAppMessage',//分享给朋友
					'onMenuShareQQ',//分享到QQ
					'onMenuShareWeibo',//分享到腾讯微博
					'onMenuShareQZone',//分享到QQ空间
					'startRecord',//开始录音接口
					'stopRecord',//停止录音接口
					'onVoiceRecordEnd',//监听录音自动停止接口
					'playVoice',//播放语音接口
					'pauseVoice',//暂停播放接口
					'stopVoice',//停止播放接口
					'onVoicePlayEnd',//监听语音播放完毕接口
					'uploadVoice',//上传语音接口
					'downloadVoice',//下载语音接口
					'chooseImage',//拍照或从手机相册中选图
					'previewImage',//预览图片接口
					'uploadImage',//上传图片接口
					'downloadImage',//下载图片接口
					'translateVoice',//识别音频并返回识别结果接口
					'getNetworkType',//获取网络状态接口
					'openLocation',//使用微信内置地图查看位置接口
					'getLocation',//获取地理位置接口
					'hideOptionMenu',//隐藏右上角菜单接口
					'showOptionMenu',//显示右上角菜单接口
					'hideMenuItems',//批量隐藏功能按钮接口
					'showMenuItems',//批量显示功能按钮接口
					'hideAllNonBaseMenuItem',//隐藏所有非基础按钮接口
					'showAllNonBaseMenuItem',//显示所有功能按钮接口
					'closeWindow',//关闭当前网页窗口接口
					'scanQRCode',//调起微信扫一扫接口
					'chooseWXPay',//微信支付,发起一个微信支付请求
					'openProductSpecificView',//跳转微信商品页接口
					'addCard',//批量添加卡券接口
					'chooseCard',//拉取适用卡券列表并获取用户选择信息
					'openCard'//查看微信卡包中的卡券接口
				] // 必填，需要使用的JS接口列表，所有JS接口列表见附录2
			});

		}else{
			mui.alert(result.msg,'微信JS-SDK调起失败');
		}
	},
	error: function(xhr, type, errorThrown) {
		//异常处理；
		console.log(type);
	}
});