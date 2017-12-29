function loadingFadeIn(){
	$('.loading-modal').fadeIn("fast");
}
function loadingFadeOut() {
	$('.loading-modal').fadeOut("fast");
}
function getNowFormatDate() {
	var date = new Date();
	var seperator1 = "-";
	var seperator2 = ":";
	var month = date.getMonth() + 1;
	var strDate = date.getDate();
	if (month >= 1 && month <= 9) {
		month = "0" + month;
	}
	if (strDate >= 0 && strDate <= 9) {
		strDate = "0" + strDate;
	}
	var currentdate = date.getFullYear() + seperator1 + month + seperator1 + strDate
		+ " " + date.getHours() + seperator2 + date.getMinutes()
		+ seperator2 + date.getSeconds();
	return currentdate;
}
function loadMessageCenter(url, sendData) {
	var messageDropdown = $('#message-dropdown');
	$.ajax({
		url: url,
		type: 'post',
		dataType: 'json',
		data: sendData,
		cache: false,
        	global: false,
		contentType: false,
		processData: false,
		beforeSend: function() {
  		    // 旋转刷新按钮图标
	            messageDropdown.find('.message-list>div').empty();
        	    messageDropdown.find('.update-info > button i').addClass('fa-spin');
	            messageDropdown.find('.loader-wrapper').addClass('show');
		},
		complete: function() {
		    // 停止旋转刷新按钮图标
        	    messageDropdown.find('.update-info > button i').removeClass('fa-spin');
	            messageDropdown.find('.loader-wrapper').removeClass('show');
		},
		success: function(json) {
			if (typeof json.errorCode == 'undefined' || json.errorCode != 0) {
				return false;
			}
			var data = json.data;
			var keys = new Array;
			var html = '<table class="table table-bordered table-hover dataTable no-margin" role="grid">';
			// head
			html += '<thead><tr>';
			$.each(data.title, function (k, v) {
				keys.push(k);
				html += '<th>' + v + '</th>';
			});
			html += '<th style="min-width:130px;">操作</th></tr></thead>';

			// body
			html += '<tbody>';
			$.each(data.list, function(k, v) {
				html += '<tr>';
				for (f in keys) {
					html += '<td>' + v[keys[f]] + '</td>';
				}
				if (typeof v.uri != 'undefined') {
					html += '<td><a class="open-marker" data-bicycle-id="' + v.bicycle_id + '" data-action="' + v.uri + '" href="#">查看</a></td>';
				}
				html += '</tr>';
			});
			html += '</tbody>';
			html += '</table>';

			// pagination
			if (data.total > 1) {
				html += '<div class="box-tools pull-right margin"><ul class="pagination pagination-sm inline" data-action="' + url + '" data-page="' + sendData['page'] + '">';
				if (sendData['page'] > 1) {
					html += '<li><a href="javascript:;" data-page="1">«</a></li>';
					if (sendData['page'] > 2) {
						html += '<li><a href="javascript:;" data-page="' + (parseInt(sendData['page']) - 2) + '">' + (parseInt(sendData['page']) - 2) + '</a></li>';
					}
					html += '<li><a href="javascript:;" data-page="' + (parseInt(sendData['page']) - 1) + '">' + (parseInt(sendData['page']) - 1) + '</a></li>';
				}
				html += '<li class="active"><a href="javascript:;" data-page="' + sendData['page'] + '">' + sendData['page'] + '</a></li>';
				if (sendData['page'] < data.total) {
					html += '<li><a href="javascript:;" data-page="' + (parseInt(sendData['page']) + 1) + '">' + (parseInt(sendData['page']) + 1) + '</a></li>';
					if (sendData['page'] + 1 < data.total) {
						html += '<li><a href="javascript:;" data-page="' + (parseInt(sendData['page']) + 2) + '">' + (parseInt(sendData['page']) + 2) + '</a></li>';
					}
					html += '<li><a href="javascript:;" data-page="' + data.total + '">»</a></li>';
				}
				html += '</ul></div>';
			}

			// message number
			var messageButton = $('#button-message');
			var messageDropdown = $('#message-dropdown');
			messageButton.find("span").html(data.statistics.amount);
			messageDropdown.find(".btn-group-justified .btn.violation span").html("(" + data.statistics.violations + ")");
			messageDropdown.find(".btn-group-justified .btn.fault span").html("(" + data.statistics.faults + ")");
			messageDropdown.find(".btn-group-justified .btn.other span").html("(" + data.statistics.feedbacks + ")");

			$(".message-list>div").html(html);
			$(".update-info span").html(getNowFormatDate());
		}
	});
	
}
(function($){
	var seed=new Array();
	
	window.View={
		init:function(){

		},
		//记录定时器
		addSeed:function(val){
			seed.push(val);
		},
		//清除定时器
		clearSeed:function(){
			for(var i=0;i<seed.length;i++){
				window.clearInterval(seed[i]);
			}
		}
	};

	$( document ).ajaxStart(function() {
		loadingFadeIn();
	});

	$( document ).ajaxSuccess(function() {
		loadingFadeOut();
        $('#content-wrapper').height($('#content-wrapper .content').height()+41);
	});

	$('html body').on('click','a[no-ajax!="true"]',function(event){
		if($(this).attr('target')=='_blank' || $(this).attr('href').substr(0,10)=='javascript'){
			return;
		}
		event.preventDefault();
		var state={
			url:$(this).attr('href')
		};
		if($(this).attr('href')=='#'){
			return;
		}
		View.clearSeed();
		history.pushState(state,null,$(this).attr('href'));
		$.get($(this).attr('href'),function(res){
			if(res!=null && res!=undefined){
				$('#content-wrapper').html(res);
			}else{
				$('#content-wrapper').html('');
			}
		});
	});
	
	$('html body').on('click','button[type="submit"],input[type="submit"]',function(event){
		var form=$(this).parents('form');
		var query=form.serialize();
		var method=form.attr('method');
		var state={
			url:form.attr('action')
		};
		history.pushState(state,null,form.attr('action'));
		if(method=='get'){
			$.get(form.attr('action'),query,function(res){
				$('#content-wrapper').html(res);
			});
		}else{
			var oData = new FormData(form[0]);
			var oReq = new XMLHttpRequest();
			var url=form.attr('action');
			oReq.open("POST", url, true);
			oReq.setRequestHeader("X-Requested-With", "XMLHttpRequest");
			oReq.send(oData);
			oReq.onload = function(oEvent) {
				$('#content-wrapper').html(oReq.responseText);
			};
//			$.post(form.attr('action'),query,function(res){
//				$('#content-wrapper').html(res);
//			});
		}
		
		return false;
	});
	
	
	$('html body').on('click','button.link',function(event){
		var state={
			url:$(this).attr('data-url')
		};
		history.pushState(state,null,$(this).attr('data-url'));
		$.get($(this).attr('data-url'),function(res){
			$('#content-wrapper').html(res);
		});
	});

	
	window.addEventListener("popstate", function(e) {
	    var url = e.state.url;
	    console.log(e.state);
		var state={
			url:url
		};
		history.pushState(state,null,url);
		$.get(url,function(res){
			$('#content-wrapper').html(res);
		});
	    
	});
	
	toastr.options = {
	  "closeButton": true,
	  "debug": false,
	  "positionClass": "toast-top-center",
	  "onclick": null,
	  "showDuration": "300",
	  "hideDuration": "1000",
	  "timeOut": "5000",
	  "extendedTimeOut": "1000",
	  "showEasing": "swing",
	  "hideEasing": "linear",
	  "showMethod": "fadeIn",
	  "hideMethod": "fadeOut"
	};

    var messageDropdown = $('#message-dropdown');

    messageDropdown.on('click', '.btn-group, .box-tools, >span', function(e){
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation();
    }).on('click', '.btn-group .btn', function(e) { //消息中心切换标签
        $(this).addClass('active').siblings().removeClass('active');
		var page = 1;
		var url = $(this).data("action");
		loadMessageCenter(url, {page:page});
    }).on('click', '.update-info > button', function() { //消息中心手动刷新
		messageDropdown.find(".btn-group-justified .btn.active").trigger("click");
    }).on('click', '.pagination a', function() { //消息中心分页
		var page = $(this).data("page");
		var url = $(this).parents("ul").data("action");
		loadMessageCenter(url, {page:page});
    }).on('click', '.open-marker', function () {
        var action = $(this).data('action');
        var bicycleId = $(this).data('bicycleId');
        getUrl(function(result) {
            if(result['route'] != 'admin/index'){
                var bicycle_id = bicycleId;
                localStorage.open_marker_bicycle_id = bicycle_id;
                window.location.href = action;
            }
        });
    });
    messageDropdown.parent().on('show.bs.dropdown', function(){
        clearInterval(messageUpdateInt);
    }).on('hidden.bs.dropdown', function(){
        messageUpdateInt = setInterval(updateMessage, 30000);
    });
    
	function updateMessage(){
            messageDropdown.find('.update-info > button').trigger("click");
	}
        
        // 定时更新和立刻更新
	var messageUpdateInt = setInterval(updateMessage, 30000);
	updateMessage();

})(jQuery);


//获取当前url
function getUrl(callback){
	var $_GET = (function(){
		var url = window.document.location.href.toString();
		var u = url.split("?");
		if(typeof(u[1]) == "string"){
			u = u[1].split("&");
			var get = {};
			for(var i in u){
				var j = u[i].split("=");
				get[j[0]] = j[1];
			}
			return get;
		} else {
			return {};
		}
	})();
	callback($_GET) ;
}

/**
 * 设置cookie
 * @param key
 * @param value
 */
function setCookie(key, value) {
    var Days = 30;
    var exp = new Date();
    exp.setTime(exp.getTime() + Days*24*60*60*1000);
    document.cookie = key + "="+ escape (value) + ";expires=" + exp.toGMTString();
}

/**
 * 读取cookie
 * @param name
 * @returns {null}
 */
function getCookie(key) {
    var arr, reg = new RegExp("(^| )" + key + "=([^;]*)(;|$)");
    if (arr = document.cookie.match(reg)) {
        return unescape(arr[2]);
    } else {
		return null;
	}
}