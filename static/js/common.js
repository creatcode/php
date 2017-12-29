/**
 * Created by h on 2016/10/9.
 */

$(function () {
    $("body").on('click', '.button-upload', function() {
        var t = $(this);
        // 标签名称，上传时区分不同文件对象
        var tag = t.data("tag");
        // 附带标识参数
        var tage    = t.data("tage");
        var fileType = t.data("ftype");
        // 上传地址
        var action = t.data("action");
        // 是否用文件原来的名称
        var useOriName = t.data("useoriname") || '';
        // 清除旧表单
        $('#form-upload-' + tag).remove();
        // 添加新表单
        $('body').prepend('<form enctype="multipart/form-data" id="form-upload-'+ tag +'" style="display: none;"><input type="hidden" name="tage" value="'+ tage +'" /><input type="hidden" value="'+fileType+'" name="fileType" /><input type="hidden" value="'+useOriName+'" name="useOriName" /><input type="file" name="upfile" value="" /></form>');
        // 触发上传按钮
        $('#form-upload-'+ tag +' input[name=\'upfile\']').trigger('click');
        // 清除无效的计时器
        if (typeof timer != 'undefined') {
            clearInterval(timer);
        }
        // 监听上传按钮是否有选中上传文件
        timer = setInterval(function() {
            if ($('#form-upload-'+ tag +' input[name=\'upfile\']').val() != '') {
                // 捕捉已选中上传文件，清除监听
                clearInterval(timer);
                // 将表单数据通过AJAX请求服务器
                $.ajax({
                    url: action,
                    type: 'post',
                    dataType: 'json',
                    data: new FormData($('#form-upload-' + tag)[0]),
                    cache: false,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        // 更改上传按钮图标
                        t.find('i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
                        t.prop('disabled', true);
                    },
                    complete: function() {
                        // 还原上传按钮图标
                        t.find('i').replaceWith('<i class="fa fa-upload"></i>');
                        t.prop('disabled', false);
                    },
                    success: function(json) {
                        console.log(json);
                        // 文件上传成功
                        if (json['errorCode'] == 0) {
                            var data = json['data'];
                            $.each(data, function(k, v) {
                                var obj = t.find("." + k);
                                obj.each(function(i, e) {
                                    var t = $(e);
                                    var tagName = t.prop("tagName");
                                    switch (tagName) {
                                        case 'INPUT':
                                            t.val(v);
                                            break;
                                        case 'IMG':
                                            t.attr("src", v);
                                            break;
                                        default:
                                            t.html(v);
                                            break;
                                    }
                                });
                            });
                            var img = t.parent().find('img');
                            if(img && img != undefined ){
                                img.attr('src',data.imageurl);
                            }
                            var middle = t.parent('.row').find('.middle_input_class');
                            if(middle.length > 0 && middle != undefined ){
                                console.log(middle);
                                middle.val(data.middleImage.url);
                                middle.next().attr("src",data.middleImage.all_url);
                            }
                            var small = t.parent('.row').find('.small_input_class');
                            if(small.length > 0 && small != undefined ){
                                small.val(data.smallImage.url);
                                small.next().attr("src",data.middleImage.all_url);
                            }
                        } else {
                            // 文件上传失败，弹出提示框
                            alert(json['msg']);
                        }
                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        // 网络异常
                        alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                    }
                });
            }
        }, 500);
    });
});