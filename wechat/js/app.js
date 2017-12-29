var user_id,wxOpenid,sign,resData,mapA,nowLng,nowLat;

//分享相关信息
var shareImgUrl='http://bike.e-stronger.com/bike/wechat/img/IconShare.jpg',
	shareTips1='领免费骑行券，一起体验小强单车！',
	shareTips2='我在小强单车完成一次骑行，快来一起免费体验吧！';
	
	
// 常量
var BIND = 1,
	UNBIND = 2;
	


/***************************************
 * 一代聪师 自执行函数
 ***************************************/
(function(app, $, mui) {
	/**
	 * 设置基础域名配置
	 **/
	app.baseUrl = function() { 
		return 'http://bike.e-stronger.com/bike/api/index.php?'
//		return 'https://api.s-bike.cn/index.php?'
	}
	/**
	 * HTML5本地存储 设置应用本地配置
	 * settings:保存数据
	 * name:保存数据名
	 **/
	app.setSettings = function(settings, name) {
		settings = settings || {};
		localStorage.setItem(name, JSON.stringify(settings));
	}

	/**
	 * 获取HTML5本地存储的数据
	 * 获取数据：localStorage.getItem(key)
	 * 获取全部数据：localStorage.valueOf()
	 * 删除数据：localStorage.removeItem(key) 
	 * 清空全部数据：localStorage.clear();
	 * 获取本地存储数据数量：localStorage.length
	 * 获取第 N 个数据的 key 键值：localStorage.key(N)
	 **/
	app.getSettings = function(name) {
		var settingsText = localStorage.getItem(name) || "{}";
		return JSON.parse(settingsText);
	}

	/**
	 * 删除应用本地配置
	 **/
	app.removeSettings = function(name) {
		localStorage.removeItem(name);
	}
	
	/**
	 * 获取cookie
	 **/
	app.getCookie = function(name) {
		var arr,reg=new RegExp("(^| )"+name+"=([^;]*)(;|$)");
		if(arr=document.cookie.match(reg)){
			return unescape(arr[2]);
		}else{
			return null;
		}
	}
	
	/**
	 * url传参  可传中文
	 **/
	app.getRequest=function(obj) {
		var urlStr = location.search,
			theRequest = '';
		var urlCutA = urlStr.split(obj)[1];
		if(urlCutA) {
			var urlCutB = urlCutA.split('=')[1];
			var urlCutC = urlCutB.split('&')[0]
			if(urlCutC) {
				theRequest = urlCutC;
			} else {
				theRequest = urlCutB;
			}
			theRequest = decodeURI(theRequest);
			return theRequest;
		} else {
			return false;
		}
	}
	
	/**
	 * 身份证 正则验证
	 **/
	app.idCardCheck=function(num){
		return /^[1-9]\d{5}[1-9]\d{3}((0\d)|(1[0-2]))(([0|1|2]\d)|3[0-1])\d{3}([0-9]|X)$/.test(num);
	}
	
	/**
	 * 手机 正则验证
	 **/
	app.telCheck=function(num){
		return /^1[1234567890]\d{9}$/.test(parseInt(num));
	}
	
	/**
	 * 正整数 正则验证
	 **/
	app.numCheck=function(num){
		return /^\d+$/.test(num);
	}
	
	
	/**
	 * mui.ajax请求函数
	 * 请求地址/请求参数/成功返回/失败返回/提示语链接
	 **/
	app.ajax=function(url,obj,successBack,errorBack,linkGo){
		mui.ajax(app.baseUrl()+url,{
			dataType: 'json',
			type: 'post', 
			data:obj,
			timeout: 10000,
			beforeSend: function(request) {
				request.setRequestHeader('Client', 'wechat');
			},
			success: function(res) {
				app.loading('hide');
				//列表页一进来加载的小圈圈
				$('#listLoad').remove();
				
				console.log('s-bike',res);
				resData=res;
				
				
				if(resData.errorCode==0){
					successBack();
				}else{
					if(resData.errorCode==99){
						mui.alert(resData.msg,'温馨提示',function(){
							//清空全部数据
							localStorage.clear();
							window.location='./login.html';
							return;
						});
					}else{
						
						mui.alert(resData.msg,'温馨提示',function(){
							if(linkGo!=undefined){
								window.location=linkGo;
								return;
							}
						});
						
						if (typeof errorBack === 'function') {
							errorBack();
						}
					}
				}
			},
			error: function(xhr, type, errorThrown){
				console.log(JSON.stringify(xhr));
				console.log(type);
				console.log(errorThrown);
				mui.toast('网络超时');
			} 
		});
	}
	
	/**
	 * 微信openid 检查
	 **/
	app.wxOpenidCheck=function(callback){
	  	wxOpenid = app.getCookie('openid');
		console.log('微信openid: '+wxOpenid);
		if(wxOpenid==''||wxOpenid==null||wxOpenid==undefined){
			window.location=app.baseUrl()+'route=system/common/wechat';
		}else{
			app.setSettings(wxOpenid,'openid');
			callback();
		}
	}
	
	/**
	 * 本地存储 用户ID 判断
	 **/
	app.check=function(callback){
		//检测微信openid
		app.wxOpenidCheck(function(){
			
			//检测用户ID
			app.userIdCheck(function(){//无ID
				//直接跳转登录页
				window.location='./login.html';
			},function(){//有ID
				//获取用户个人信息，判断 交押金/实名验证
				app.userCenterCheck(function(){
					callback();
				});
			});
		});
	}
	
	/**
	 * 首页 check 不需用用户ID，直接可以请求marker点
	 * callback1 必须执行
	 * callback2 无用户ID执行
	 * callback3 有用户ID执行
	 **/
	app.homeCheck=function(callback1,callback2,callback3){
		callback1();
		//检测用户ID
		app.userIdCheck(function(){//无ID
			callback2();
		},function(){//有ID
			//获取用户个人信息，判断 交押金/实名验证
			app.userCenterCheck(function(){
				callback3();
			});
		});
	}
	
	//有无个人ID判断
	app.userIdCheck=function(callback1,callback2){
		user_id = app.getSettings("user_id");
		if(user_id instanceof Object){
			console.log('无用户ID');
			callback1();
		}else{
			console.log('有用户ID   user_id:'+user_id);
			//sign MD5加密
			sign = hex_md5(user_id+wxOpenid);
			app.setSettings(sign,'sign');
			console.log('sign MD5加密：'+sign);
			callback2();
		}
	};
	
	
	
	/**
	 * 获取个人信息
	 **/
	app.userCenter=function(callback){
		app.ajax('route=account/account/info',{
			'user_id':user_id,
			'sign':sign
		},function(){
			callback();
		});
	}
	
	
	/**
	 * 判断 交押金/实名验证
	 **/
	app.userCenterCheck=function(callback){
		app.userCenter(function(){
			console.log('是否已交押金：'+resData.data.deposit_state+'    实名认证状态：'+resData.data.verify_state);
			if(resData.data.deposit_state=='0'&&resData.data.verify_state=='0'){//未交押金 未实名认证
				mui.alert('您未交押金','温馨提示',function(){
					window.location='./deposit.html';
				});
			}else if(resData.data.deposit_state=='0'&&resData.data.verify_state=='1'){//未交押金 已实名认证
				mui.alert('您未交押金','温馨提示',function(){
					window.location='./recharge2.html';
				});
			}else if(resData.data.verify_state=='0'&&resData.data.deposit_state=='1'){//实名认证状态：0未，1已
				mui.alert('您未实名认证','温馨提示',function(){
					window.location='./realName.html';
				});
			}else{
				callback();
			}
		});
	}
	
	
	/**
	 * 当前订单状况
	 **/
	app.nowOrder=function(callback1,callback2){
		app.ajax('route=account/order/current',{
			'user_id':user_id,
			'sign':sign
		},function(){
			if(resData.data.has_order==true){//有正在进行中的订单
				console.log(resData.data.current_order);
				
				callback1();
			}else{//没有 正在进行中的订单
				console.log('没有正在进行中的订单...');
				callback2();
			}
		});
	}
	
	/**
	 * 订单详情
	 **/
	app.orderInfo=function(sn,callback){
		app.ajax('route=account/order/getOrderInfo',{
			'user_id':user_id,
			'sign':sign,
			'order_sn':sn
		},function(){
			callback();
		});
	}
	
	/**
	 * loading 界面
	 * name:show/hide
	 * text:自定义文字 选填
	 **/
	app.loading=function(name,text){
		var textNow='';
		
		if(text!=undefined){
			textNow=text;
		}else{
			textNow='正在请求';
		}
		
		if(name=='show'){
			var loadWait='<div class="loadWait" id="loadFill">\
				<div class="neiDeng">\
					<div class="uil-default-css">\
						<div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div><div></div>\
					</div>\
				    <div class="loadWaitText">'+textNow+'</div>\
				</div>\
			</div>';
	  		$('body').append(loadWait).css({'overflow':'hidden'});
		}else if(name=='hide'){
			$('#loadFill').remove();
  			$('body').css({'overflow':'visible'});
		}
	}
	
	
	/**
	 * 地球坐标转火星坐标
	 **/
	app.wgs84togcj02 = function(lng, lat){
		//传入经/纬度
		var lng=parseFloat(lng);
		var lat=parseFloat(lat);
		
		//定义一些常量
		var x_PI = 3.14159265358979324 * 3000.0 / 180.0;
		var PI = 3.1415926535897932384626;
		var a = 6378245.0;
		var ee = 0.00669342162296594323;
		
		/***
		* WGS84转GCj02
		* @param lng
		* @param lat
		* @returns {*[]}
		***/
		if (out_of_china(lng, lat)) {
			return [lng, lat]
		}else {
			var dlat = transformlat(lng - 105.0, lat - 35.0);
			var dlng = transformlng(lng - 105.0, lat - 35.0);
			var radlat = lat / 180.0 * PI;
			var magic = Math.sin(radlat);
			magic = 1 - ee * magic * magic;
			var sqrtmagic = Math.sqrt(magic);
			dlat = (dlat * 180.0) / ((a * (1 - ee)) / (magic * sqrtmagic) * PI);
			dlng = (dlng * 180.0) / (a / sqrtmagic * Math.cos(radlat) * PI);
			var mglat = lat + dlat;
			var mglng = lng + dlng;
			return [mglng, mglat];
		}
		
		
		
		/**
		 * 判断是否在国内，不在国内则不做偏移
		 * @param lng
		 * @param lat
		 * @returns {boolean}
		 */
		function out_of_china(lng, lat) {
			return(lng < 72.004 || lng > 137.8347) || ((lat < 0.8293 || lat > 55.8271) || false);
		}
		function transformlat(lng, lat) {
			var ret = -100.0 + 2.0 * lng + 3.0 * lat + 0.2 * lat * lat + 0.1 * lng * lat + 0.2 * Math.sqrt(Math.abs(lng));
			ret += (20.0 * Math.sin(6.0 * lng * PI) + 20.0 * Math.sin(2.0 * lng * PI)) * 2.0 / 3.0;
			ret += (20.0 * Math.sin(lat * PI) + 40.0 * Math.sin(lat / 3.0 * PI)) * 2.0 / 3.0;
			ret += (160.0 * Math.sin(lat / 12.0 * PI) + 320 * Math.sin(lat * PI / 30.0)) * 2.0 / 3.0;
			return ret
		}
		
		function transformlng(lng, lat) {
			var ret = 300.0 + lng + 2.0 * lat + 0.1 * lng * lng + 0.1 * lng * lat + 0.1 * Math.sqrt(Math.abs(lng));
			ret += (20.0 * Math.sin(6.0 * lng * PI) + 20.0 * Math.sin(2.0 * lng * PI)) * 2.0 / 3.0;
			ret += (20.0 * Math.sin(lng * PI) + 40.0 * Math.sin(lng / 3.0 * PI)) * 2.0 / 3.0;
			ret += (150.0 * Math.sin(lng / 12.0 * PI) + 300.0 * Math.sin(lng / 30.0 * PI)) * 2.0 / 3.0;
			return ret
		}
	}
	
	
	/**
	 * 实时获取我现在的坐标点wgs84
	 **/
	app.wgs84Location=function(callback){
		wx.ready(function(){
			wx.getLocation({
			    type: 'wgs84',
			    success: function (res) {
			    	nowLng = res.longitude;
			        nowLat = res.latitude;
					
					callback();
			    },
		        cancel: function (res) {
		            mui.alert('请重启应用，允许获取您的定位','温馨提示');
		        }
			});
		});
	}
	
	/**
	 * 实时获取我现在的坐标点gcj02
	 **/
	app.gcj02Location=function(callback){
		wx.ready(function(){
			wx.getLocation({
			    type: 'wgs84',//gcj02
			    success: function (res) {
			        nowLng = app.wgs84togcj02(res.longitude,res.latitude)[0];
			        nowLat = app.wgs84togcj02(res.longitude,res.latitude)[1];
			        
					callback();
			    },
		        cancel: function (res) {
		            mui.alert('请重启应用，允许获取您的定位','温馨提示');
		        }
			});
		});
	}
	
	/**
	 * 无法检测个人ID,跳转登录页面
	 **/
	app.loginCheck=function(){
		window.location='./login.html';
		app.loading('hide');
	}
	
	/**
	 * 调起二维码 获取单车编号 复制进input文本框内
	 **/
	app.scan=function(a,b){
		$('#'+a).on('tap',function(){
			app.loading('show','启动相机');
			
			setTimeout(function(){
				app.loading('hide');
			},2000);
			wx.ready(function(){
				wx.scanQRCode({
					needResult:1, // 默认为0，扫描结果由微信处理，1则直接返回扫描结果，
					scanType:["qrCode", "barCode"], // 可以指定扫二维码还是一维码，默认二者都有
					success: function(res) {
						var codeStr = res.resultStr;
						var device_id=codeStr.substring(codeStr.indexOf('=')+6);
						$('#'+b).val(device_id);
					}
				});
				wx.error(function(res){
				    alert(JSON.stringify(res));
				});
			});
		});
	}
	
	/**
	 * 文本输入框 文字统计 函数
	 * a总字数/b当前字数
	 **/
	app.countText=function(a,b){
		var t2=parseInt($('#'+a).text());
		$('textarea').on('input propertychange', function(){
			var val=$(this).val();
			var num=$(this).val().length;
			$('#'+b).text(num);
			
			if(num>t2){
				$('#'+b).text(t2);
				mui.toast('您已经超出限定字数');
				var valx=val.substr(0,t2);
				$(this).val(valx);
					
		        return false;
			}
		});
	}
	
	/**
	 * 分享行程
	 **/
	app.shareLove=function(order_id){
		//获取encryptCode
		app.ajax('route=account/account/getEncryptCode',{
			'user_id':user_id,
			'sign':sign
		},function(){
			var encryptCode=resData.data.encrypt_code;
			console.log('encryptCode: '+encryptCode);
			
			wx.ready(function(){
				//分享指定链接
				var shareLove={
					title:shareTips2,
				    desc:shareTips2,
				    imgUrl:shareImgUrl,
				    link: 'http://bike.e-stronger.com/bike/wechat/myTripShare.html?order_id='+order_id+'&encryptCode='+encryptCode,
				    success: function () { 
				       console.log('分享成功');
				    },
				    cancel: function () { 
				       console.log('分享失败');
				    }
				};
				wx.onMenuShareTimeline(shareLove);
				wx.onMenuShareAppMessage(shareLove);
				wx.onMenuShareQQ(shareLove);
				wx.onMenuShareWeibo(shareLove);
				wx.onMenuShareQZone(shareLove);
				wx.error(function(res){
				    alert(JSON.stringify(res));
				});
			});
		});
	}
	
	/**
	 * mui框架 上拉加载函数
	 * obj.data		数据数组
	 * obj.start	数组不为空执行逻辑
	 * obj.end		数组为空执行逻辑
	 * obj.judge	判断条件
	 * obj.wrap		上拉容器
	 * obj.success	执行逻辑函数
	 **/
	app.pullToRefresh=function(obj){
		//判断列表是否为[]数组
		var data=obj.data;
		if(data!='' && data!=null && data!=undefined){
			//数组不为空执行逻辑
			obj.start();
			
			if(!!(obj.judge)){
				//上拉加载
				mui(obj.wrap).pullToRefresh({
					up: {
						//更新时 显示
						contentrefresh: '<i class="mui-spinner congZhuan" style="padding:0;"></i>正在加载...',
						//无内容加载时 显示
						contentnomore:'没有更多数据了',
						//更新前提示 显示
						contentdown: '上拉显示更多',
						//回调函数：上拉加载
						callback: function(){
							var zhe = this;
							obj.success(zhe);
						}
					}
				});
			}
			
		}else{
			//数组为空执行逻辑
			obj.end();
		}
	}
	
	/**
	 * 格式化时间戳
	 * obj.type		1年月日时分 / 2年月日  / 3月日 / 4时分 / 5分秒
	 * obj.time		时间戳
	 **/
	app.formatTime = function(obj){
		//格式化时间戳
		Date.prototype.Format = function(fmt) { // author: meizz
			var o = {
				"M+": this.getMonth() + 1, // 月份
				"d+": this.getDate(), // 日
				"h+": this.getHours(), // 小时
				"m+": this.getMinutes(), // 分
				"s+": this.getSeconds(), // 秒
				"q+": Math.floor((this.getMonth() + 3) / 3), // 季度
				"S": this.getMilliseconds()// 毫秒
			};
			if(/(y+)/.test(fmt))
				fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "")
					.substr(4 - RegExp.$1.length));
			for(var k in o)
				if(new RegExp("(" + k + ")").test(fmt))
					fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) :
						(("00" + o[k]).substr(("" + o[k]).length)));
			return fmt;
		}
		
		var out='';
		switch (parseInt(obj.type)){ //1年月日时分 / 2年月日  / 3月日 / 4时分 / 5分秒
	        case 1: out='yyyy-MM-dd hh:mm'; break;
	        case 2: out='yyyy-MM-dd'; break;
	        case 3: out='MM-dd'; break;
	        case 4: out='hh:mm'; break;
	        case 5: out='mm:ss'; break;
	    }
		
		return new Date(parseInt(obj.time) * 1000).Format(out);
	}

	/**
	 * 调起微信支付		zhe/1,2/生成支付订单提交 obj/成功执行 callback
	 **/
	app.pay=function(zhe,num,obj,callback){
		zhe.prop('disabled',true).text('正在请求支付');
		
		//1押金充值  / 2余额充值
		if(parseInt(num)==1){
			var url='deposit';
		}else if(parseInt(num)==2){
			var url='charging';
		}
		
		//生成充值订单
		app.ajax('route=account/account/'+url,obj,function(){
			var pdr_sn=resData.data.pdr_sn;
			var openid=app.getSettings('openid');
			
			//请求微信支付
			app.ajax('route=account/deposit/wxPayChargeDeposit',{
				'user_id':user_id,
				'sign':sign,
				'pdr_sn':pdr_sn,
				'openid':openid
			},function(){
				var appId=resData.data.appId;
				var timeStamp=resData.data.timeStamp;
				var nonceStr=resData.data.nonceStr;
				var package=resData.data.package;
				var paySign=resData.data.paySign;
				
				//调起微信支付
				wx.ready(function() {
					function onBridgeReady() {
						WeixinJSBridge.invoke(
							'getBrandWCPayRequest', {
								"appId":appId,
								"timeStamp":timeStamp,// 支付签名时间戳，注意微信jssdk中的所有使用timestamp字段均为小写。但最新版的支付后台生成签名使用的timeStamp字段名需大写其中的S字符
								"nonceStr":nonceStr,// 支付签名随机串，不长于 32 位
								"package":package,// 统一支付接口返回的prepay_id参数值，提交格式如：prepay_id=***）
								"signType":"MD5", // 签名方式，默认为'SHA1'，使用新版支付需传入'MD5'
								"paySign":paySign// 支付签名
							},
							function(res) {// 支付成功后的回调函数
								console.log(res);
								switch(res.err_msg) {
									//支付成功
				                    case "get_brand_wcpay_request:ok":
				                        mui.alert("支付成功", "温馨提示", function() {
				                        	callback();
				                        });
				                        break;
				                    //支付取消
				                    case "get_brand_wcpay_request:cancel":
				                        mui.alert("支付取消", "温馨提示", function() {
				                            window.location.reload();//刷新页面
				                            zhe.prop('disabled',false).text('充 值');
				                        });
				                        break;
				                    //支付失败
				                    case "get_brand_wcpay_request:fail":
				                        mui.alert("支付失败", "温馨提示", function() {
				                            window.location.reload();//刷新页面
				                            zhe.prop('disabled',false).text('充 值');
				                        });
				                        break;
				                }
							}
						);
					}
					
					//判断有无加载 微信js
					if(typeof WeixinJSBridge == "undefined") {
						if(document.addEventListener) {
							document.addEventListener('WeixinJSBridgeReady', onBridgeReady, false);
						} else if(document.attachEvent) {
							document.attachEvent('WeixinJSBridgeReady', onBridgeReady);
							document.attachEvent('onWeixinJSBridgeReady', onBridgeReady);
						}
					} else {
						onBridgeReady();
					}
					
				});
			},function(){
				zhe.prop('disabled',false).text('充 值');
			});
			
		},function(){
			console.log('生成充值订单失败');
		},'./index.html');
	}
	
	/**
	 * 上传单张图片函数
	 **/
	app.selectFileImage=function(fileObj) {
		var file = fileObj.files['0'];
		
		//如果有图片
		if (file) {
			app.loading('show','正在上传');
			console.log("正在上传,请稍后...");
			var rFilter = /^(image\/jpeg|image\/png)$/i; // 检查图片格式 
			if (!rFilter.test(file.type)) {
				mui.alert('请选择正确的图片格式','温馨提示');
				app.loading('hide');
				return;
			}
	    	console.log(file);
	
	    	//iOS重力感应图片旋转90°处理
			EXIF.getData(file, function(){
				console.log('获取图片所有信息：'+JSON.stringify(EXIF.getAllTags(this)))
				EXIF.getAllTags(this);
				
				//获取图片重力感应 Orientation参数：  1:0°  /  6:顺时针90°  /  8:逆时针180°  /  3:180°
				var orientation = EXIF.getTag(this, 'Orientation');
				console.log('获取图片重力感应：'+EXIF.getTag(this, 'Orientation'));
	
	
				var reader = new FileReader();
				reader.onload = function(e) {
					console.log(e);
					console.log(this);
					
					//输出图片
					app.getImgData(this.result, orientation,500, function(data) {
						//这里可以使用校正后的图片data了
						console.log('Base64图片：'+data);
						$('#myImage').attr({
							'src':data,
							'data-img':data
						});
					});
					
					app.loading('hide');
				}
	
				reader.readAsDataURL(file);
				return false;
			});
			
		}   
	}
	
	/**
	 * 扇形圆进度
	 * id DOM ID
	 * x,y 坐标
	 * radius 半径
	 * xin 圆心填充颜色
	 * process 百分比
	 * backColor 中心颜色
	 * proColor 进度颜色
	 * bg 中间的图片
	 **/
	app.drowProcess=function(id,x,y,radius,xin,process,backColor,proColor,bg){
		var canvas = document.getElementById(id);
	
		if (canvas.getContext) {
			var cts = canvas.getContext('2d');
		}else{
			return;
		}
		cts.beginPath();  
		// 坐标移动到圆心  
		cts.moveTo(x, y);  
		// 画圆,圆心是24,24,半径24,从角度0开始,画到2PI结束,最后一个参数是方向顺时针还是逆时针  
		cts.arc(x, y, radius, 0, Math.PI * 2, false);  
		cts.closePath();  
		// 填充颜色  
		cts.fillStyle = backColor;  
		cts.fill();
		cts.beginPath();  
		// 画扇形的时候这步很重要,画笔不在圆心画出来的不是扇形  
		cts.moveTo(x, y);  
		// 跟上面的圆唯一的区别在这里,不画满圆,画个扇形  
		cts.arc(x, y, radius, Math.PI * 1.5, Math.PI * 1.5 -  Math.PI * 2 * process / 100, true);  
		cts.closePath();  
		cts.fillStyle = proColor;  
		cts.fill(); 
		
		//填充背景
		cts.beginPath();  
		cts.moveTo(x, y); 
		cts.arc(x, y, radius - (radius * 0.05), 0, Math.PI * 2, true);  
		cts.closePath();
		cts.fillStyle = xin;  
		cts.fill(); 
	  
			  
		//中间的图片
		var img = new Image();
		//指定图片的URL
		img.src =bg;
		cts.drawImage(img, 12, 12, 75, 75);  
		
	}
	
	/**
	 * 地图轨迹填充显示
	 **/
	app.mapFit=function(resData){
		var mapA,marker,polyline;
		var polylineS=[];
		
		//实例化，高德地图
	    mapA = new AMap.Map('container', {
	        resizeEnable: true,//监听地图容器大小变化
	    });
	    
	    //地图加载完成后 执行
	    mapA.on('complete', function() {
			console.log('加载完成');
	        $('#bikeLoadWait').remove();
	    });
	    
	    
		var icon1 = new AMap.Icon({
	        image : './img/K1.png',
	        size : new AMap.Size(25,31)
	    });
	    var icon2 = new AMap.Icon({
	        image : './img/K2.png',
	        size : new AMap.Size(25,31)
	    });
	    
	    //循环标记点
	    var trail=resData.data.locations;
	    
		mui.each(trail,function(i,val){
			var walkingA;
			walkingA=app.wgs84togcj02(val.lng,val.lat);
			polylineS.push(walkingA);
		});
	
	    //标注点起点
	    new AMap.Marker({
	        position:polylineS[0],
	        icon :icon1,
	        offset:new AMap.Pixel(-12,-26),//X轴Y轴,
	        map:mapA
	    });
	
	    //标注点 终点
	    new AMap.Marker({
	        position:polylineS[polylineS.length-1],
	        icon : icon2,
	        offset:new AMap.Pixel(-12,-26),//X轴Y轴,
	        map:mapA
	    });
		
		// 绘制轨迹
	    polyline = new AMap.Polyline({
	        map: mapA,
	        path: polylineS,
	        strokeColor: "#eb474c",  //线颜色
	        // strokeOpacity: 1,     //线透明度
	        strokeWeight: 3,      //线宽
	        // strokeStyle: "solid"  //线样式
	    });
	    
		//设置合适的视图
		mapA.setFitView();
	    
	}
	
	/**
	 * 数组 最小值 计算
	 **/
	app.arrayMin=function(team){
		return Math.min.apply(Math,team);
	}
	
	/**
	 * 数组 最大值 计算
	 **/
	app.arrayMax=function(team){
		return Math.max.apply(Math,team);
	}
	
	/**
	 * 调起 微信 jssdk
	 **/
	app.wechat_jssdk=function(callBack){
		app.ajax('route=system/common/wechat_jssdk',{},function(){
			var appId = resData.data.appId;
			var timestamp = resData.data.timestamp;
			var nonceStr = resData.data.nonceStr;
			var signature = resData.data.signature;
			
			//注入配置
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
			
			wx.ready(function(){
				if(typeof callBack === 'function'){
					callBack();
				}
			});
		});
	}
	
	/**
	 * 调起 微信 jsapi
	 **/
	app.wechat_ble=function(callBack){
		app.ajax('route=system/common/wechat_jssdk',{},function(){
			var appId = resData.data.appId;
			var timestamp = resData.data.timestamp;
			var nonceStr = resData.data.nonceStr;
			var signature = resData.data.signature;
			
			//注入配置
			wx.config({
				beta:true,    //坑：这个很重要，必须配置这个为true,才能调用微信的硬件API
				debug: false, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
				appId: appId, // 必填，公众号的唯一标识
				timestamp: timestamp, // 必填，生成签名的时间戳
				nonceStr: nonceStr, // 必填，生成签名的随机串
				signature: signature, // 必填，签名，见附录1
				jsApiList: [
					'scanQRCode',
					"openWXDeviceLib",//初始化设备库（只支持蓝牙设备）
			        "closeWXDeviceLib",//关闭设备库（只支持蓝牙设备）
			        "getWXDeviceInfos",//获取设备信息（获取当前用户已绑定的蓝牙设备列表）
			        "sendDataToWXDevice",//发送数据给设备
			        "startScanWXDevice",//扫描设备（获取周围所有的设备列表，无论绑定还是未被绑定的设备都会扫描到）
			        "stopScanWXDevice",//停止扫描设备
			        "connectWXDevice",//连接设备
			        "disconnectWXDevice",//断开设备连接
			        "getWXDeviceTicket",//获取操作凭证
			
			        //下面是监听事件：
			        "onWXDeviceBindStateChange",//微信客户端设备绑定状态被改变时触发此事件
			        "onWXDeviceStateChange",//监听连接状态，可以监听连接中、连接上、连接断开
			        "onReceiveDataFromWXDevice",//接收到来自设备的数据时触发
			        "onScanWXDeviceResult",//扫描到某个设备时触发
			        "onWXDeviceBluetoothStateChange",//手机蓝牙打开或关闭时触发
				]
			});
			wx.ready(function(){
				if(typeof callBack === 'function'){
					callBack();
				}
			});
		});
	}
	
	/**
	 * 初始化设备
	 **/
	app.openWXDeviceLib=function(callBack){
		wx.invoke('openWXDeviceLib',{
			'brandUserName':'gh_62145a65e139'
		}, function(res){
	        if(res.bluetoothState=='off'){
	        	app.loading('hide');
	        	mui.alert('请您手动打开蓝牙','蓝牙未开启');
	        }else if(res.bluetoothState=='on'){
	        	callBack();
	        }
	        
	    });
	}
	
	/**
	 * 开始 扫描蓝牙
	 **/
	app.startScanWXDevice=function(callBack){
		wx.invoke("startScanWXDevice",{
			"btVersion":"ble"
		},function(res){
			if(res.err_msg=='startScanWXDevice:fail'){
				app.loading('hide');
	        	mui.alert('请重启应用后再试','扫描失败');
	        }else if(res.err_msg=='startScanWXDevice:ok'){
	        	callBack();
	        }
		});
	}
    
	/**
	 * 开始 连接蓝牙
	 **/
	app.connectWXDevice=function(deviceId, callBack){
		wx.invoke("connectWXDevice",{
			"deviceId":deviceId
		},function(res){
//			alert(JSON.stringify(res));
			if(res.err_msg=='connectWXDevice:ok'){
	        	callBack();
	        }
		});
	}
	
	/**
	 * 停止 扫描蓝牙
	 **/
	app.stopScanWXDevice=function(){
		wx.invoke("stopScanWXDevice",function(res){
			console.log('停止扫描蓝牙',res);
		});
	}
		
	/**
	 * 获取蓝牙操作凭证
	 * deviceId	设备id
	 * num	获取的操作凭证类型，1:绑定设备 2:解绑设备
	 */
	app.getWXDeviceTicket=function(deviceId, num, callBack){
		wx.invoke("getWXDeviceTicket", {
			"deviceId":deviceId, 
			"type":num
		}, function(res){
//			alert(JSON.stringify(res));
			
			if(res.err_msg=='getWXDeviceTicket:fail'){
	        	mui.alert('请重启应用后再试','操作失败');
	        }else if(res.err_msg=='getWXDeviceTicket:ok'){
	        	callBack(res);
	        }
		});
	}

	/**
	 * 发送数据给设备
	 * @param string deviceId	设备id
	 * @param string base64Data	数据，经过base64编码后的字符串
	 */
	app.sendDataToWXDevice=function(deviceId, base64Data, callBack){
		wx.invoke("sendDataToWXDevice", {"deviceId":deviceId, "base64Data":base64Data, "connType":"blue"}, function(res){
			console.log('getWXDeviceTicket', res);
			if(res.err_msg=='sendDataToWXDevice:fail'){
	        	mui.alert('请重启应用后再试','操作失败');
	        }else if(res.err_msg=='sendDataToWXDevice:ok'){
	        	callBack(res);
	        }
		});
	}

	
	/**
	 * iOS解决 图片旋转90度函数  带  压缩功能
	 **/
	app.getImgData=function(img, dir,setSize, next) {
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
	
	/**
	 * 文章列表 链接跳转
	 **/
	app.articleLink=function(dom,code){
		app.ajax('route=article/index',{
			'language':'zh'
		},function(){
			var link;
			resData.data.forEach(function(val,i){
				if(val.code==code){
					link=val.link;
					return false;
				}
			});
			if(!!link){
				dom.on('tap',function(){
					window.location=link;
				});
			}
		});
	}
	
	/**
	 * 强制刷新 解决ios10 微信back键 不刷新的问题
	 **/
	app.reload=function(){
		setTimeout(function(){
			window.location.reload(true);
		},600);
	}
	
	/**
	 * 下载APP
	 **/
	app.downloadApp=function(){
		window.location='http://a.app.qq.com/o/simple.jsp?pkgname=cn.estronger.bike';
	}
	
}(window.app = {},$,mui));




/***********************************************
 * UI操作
 ***********************************************/
mui('#scroll,#scroll1,#scroll2,#scroll3,#scroll4').scroll({
	//APP端 是否显示滚动条，web端必须为false 否则会出现双滚动条
	indicators: false
});

//点击button 所有输入框失焦
$('button').on('tap',function(){
	$('input,textarea').blur();
});

//分享按钮
$('.shareButton').on('tap',function(){
	$('body').append('<div class="shareDialog"></div>');
});
$(document).on('tap','.shareDialog',function(){
	$(this).remove();
});

//快速清除框操作
$(document).on('tap','.mui-icon-clear',function(){
	//获取验证码 禁用
	$('#getCode,#go').prop('disabled',true);
	
	$('#searchHistory').show();
	$('#searchNow').hide();
});

//关于我们 快捷操作
$('#visitGo').on('tap','li',function(){
	var xu=$(this).index();
	//电话/官网
	var tel= $(this).find('#m2').html();
	var web= $(this).find('#m4').html();
	if(xu==0){
		window.location='tel:'+tel;
	}else if(xu==1){
		window.location='http://'+web;
	}
});