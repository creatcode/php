$(function(){

    //全选和取消全选订单
    $('#checkall').click(function(){
        var parentTable = $(this).parents('table');                                           
        var ch = parentTable.find('tbody input[type=checkbox]');                                         
        if($(this).is(':checked')) {
            ch.each(function(){ 
                $(this).prop('checked',true);
            });
            parentTable.find('#checkall').each(function(){ $(this).prop('checked',true); });
        } else {
            ch.each(function(){ 
                $(this).prop('checked',false); 
            }); 
            parentTable.find('#checkall').each(function(){ $(this).prop('checked',false); });
        }
    });

    ///// 取消所选订单 /////
    $('#deletebutton').click(function(){
        if($("input:checkbox[name='order_id']:checked").length > 0){
            if(!confirm('是否确认取消订单?')){
                return;
            }
        }

        var sel = false;
        var ch = $('#current_order').find('tbody input[type=checkbox]');


        ch.each(function(){
            if($(this).is(':checked')) {
                sel = true;
                $.post('/orders/Index/del_orders', {'order_id':$(this).val(), 'order_type':$(this).attr('order_type')}, function(res){
                    var res=jQuery.parseJSON(res);
                    if(!res.status){
                        toastr.error(res.msg);
                    }
                });
                $(this).parents('tr').fadeOut(function(){
                    $(this).remove();
                });
            }
        });
        if(!sel) toastr.error('请选择要取消的订单');
    });

    //派车
    $('#sendcarbutton').click(function(){
        var oid=[];
        var _max_passenger=0;
        var _order_type=[];
        var order_type=0;
        $('#user_destination').empty();
        $('#user_destination1').empty();
        $("input:checkbox[name='order_id']:checked").each(function(i,o){
            oid[i]=$(o).val();
            _max_passenger+=+$(o).attr('passenger');
            _order_type[i]=$(o).attr('order_type');
            order_type=$(o).attr('order_type');
            if(order_type==1){
                $('#user_destination').append("<tr><td>起点/终点：</td><td>"+$(o).attr('departure')+"</td><td>"+$(o).attr('destination')+"</td><td>"+ $(o).attr('passenger') +"人</td><td style='padding:0;'><input type='hidden' value='"+ $(o).val() +"' name='order_id[]'><input type='text' placeholder='请输入价格' class='price-input' name='price[]'></td></tr>");
            }
            if(order_type>1){
                $('#user_destination1').append("<tr><td>起点/终点：</td><td>"+$(o).attr('departure')+"</td><td colspan='3'>"+$(o).attr('destination')+"<input type='hidden' name='order_id' value='"+ $(o).val() +"'></td></tr>");
            }
        });

        if(oid.length<1){
            return false;
        }

        if(_order_type.length>1){
            if(!Verify_repeating_elements(_order_type)){
                toastr.error('请选择相同业务类型,进行派车!');
                return false;
            }
        }


        //拼车业务处理
        if(order_type ==1){
            $('#order-title').text('拼车订单');
//            if(_max_passenger < 2){
//                toastr.error('亲,拼车最少2个人才可以!');
//                return;
//            }
//            if(_max_passenger > 4){
//                toastr.error('亲,拼车最多4个人!');
//                return;
//            }
//            if(oid.length < 2){
//                toastr.error('亲,拼单业务至少选2个订单!');
//                return;
//            }
            $('#max_passenger_input').val(_max_passenger);
            $('#max_passenger').text(_max_passenger);
            $('#order_type').val(1);
        }

        //包车和接送机业务处理
        if(order_type == 2){
            $('#order-title').text('包车订单');
            $('#max_passenger_input').val(1); //总乘车人数-表单：给初始值1 包车接送机没有人数设定
            $('#max_passenger').text('1');
            $('#order_type').val(2);
            $('#order_type1').val(2);
        }

        if(order_type == 3){
            $('#order-title').text('接送机订单');
            $('#max_passenger_input').val(1); //总乘车人数-表单：给初始值1 包车接送机没有人数设定
            $('#max_passenger').text('1');
            $('#order_type').val(3);
            $('#order_type1').val(3);
        }


        //获取司机信息
        $.post('/orders/Index/get_driver', {'order_id':oid}, function(res,status){
            if(status=='success'){
                $('#box-content').html(res);
                $('#content-box').fadeIn(function(){
                    $(this).show();
                });
            }
        });

    });

    //验证重复元素 如:var ary = new Array("111","222","33","112","222");  有重复返回true；否则返回false
    function Verify_repeating_elements (arr) {
        return /(\x0f[^\x0f]+)\x0f[\s\S]*\1/.test("\x0f"+arr.join("\x0f\x0f") +"\x0f");
    }

    //反选
    $('#btnInvert').click(function(){
        $("input:checkbox[name='order_id']").each(function () {  
            this.checked = !this.checked;  
        });
    });

    //关闭弹窗
    $('#close_driver').click(function(){
        if($('#content-box-confirm').css('display') == 'block'){
            return false;
        }else{
            $('#content-box').fadeOut(function(){
                $(this).hide();
            })
        }
    });

    $('#close_price').click(function(){
        $('#content-box-confirm').fadeOut(function(){
            $(this).hide();
        })
    });
    $('#close_price1').click(function(){
        $('#content-box-confirm1').fadeOut(function(){
            $(this).hide();
        })
    });


    //确认价格
    $('#confirm_price').click(function(){
        $(this).attr("disabled",true).text('正在提交，请稍等...');
        $.post('/orders/Index/save_order_price', $("#price-form").serialize(), function(res,status){
            var res=$.parseJSON(res);
            if(res.status){
                toastr.success(res.msg,'',res.url);
            }else{
                toastr.error(res.msg);
                $('#confirm_price').attr("disabled",false).text('确认分配');
            }
        });
        return false;
    });

    //确认价格
    $('#confirm_price1').click(function(){
        $(this).attr("disabled",true).text('正在提交，请稍等...');
        $.post('/orders/Index/save_order_price1', $("#price-form1").serialize(), function(res,status){
            var res=$.parseJSON(res);
            if(res.status){
                toastr.success(res.msg,'',res.url);
            }else{
                toastr.error(res.msg);
                $('#confirm_price1').attr("disabled",false).text('确认分配');
            }
        });
        return false;
    });


});