$(function(){
    //翻页
    $('#choose_driver a').click(function(){
        var pageid=$(this).attr('data-ci-pagination-page');
        $.get('/orders/Index/get_driver_ajax_links/'+pageid,'',function(res,status){
            if(status=='success'){
                $('#ajax_links').empty();
                $('#ajax_links').html(res);
            }
        });
        $.get('/orders/Index/get_driver_ajax/'+pageid,'',function(res,status){
            if(status=='success'){
                $('#ajax_body').empty();
                $('#ajax_body').html(res);
            }
        });
        return false;
    });

    //选择司机
    $("input:radio[name='driver_checked']").click(function(){
        if($('#order_type').val()==1){
            //初始化默认值
            $('#subsidy').val('');
            $("#order_price").text('');
            $("#total_price").text('');
            $('.price-input').each(function(){
                $(this).val('');
            });

            var driver_id=$(this).val();
            $.post('/orders/Index/get_driver_info',{'order_id':$('#order_id_checked').val(), 'driver_id':$(this).val()},function(res,status){
                var res=jQuery.parseJSON(res);
                if(status=='success' & res.status){
                    $('#driver_id').val(driver_id);
                    $('#current_location').text(res.data.driver.current_location);
                    $('#cartype').text(res.data.driver.cartype);
                    $('#content-box-confirm').fadeIn(function(){ $(this).show(); });
                }else{
                    toastr.error(res.msg);
                    return false;
                }
            });
        }

        if($('#order_type').val()>1){
            var driver_id=$(this).val();
            $.post('/orders/Index/get_driver_info',{'order_id':$('#order_id_checked').val(), 'driver_id':$(this).val()},function(res,status){
                var res=jQuery.parseJSON(res);
                if(status=='success' & res.status){
                    $('#driver_id1').val(driver_id);
                    $('#current_location1').text(res.data.driver.current_location);
                    $('#cartype1').text(res.data.driver.cartype);
                    $('#content-box-confirm1').fadeIn(function(){ $(this).show(); });
                }else{
                    toastr.error(res.msg);
                    return false;
                }
            });
        }

    });

    //计算订单价格
    var s = 0.0;
    $('.price-input').change(function(){
        $('.price-input').each(function () {
            if(parseFloat($(this).val()))  s = FloatAdd(parseFloat($(this).val()),s);
        });
        $("#order_price").text(s);
        s=0.0;
        if($('#subsidy').val() =='' || $('#subsidy').val()==0) $('#subsidy').val(0);
        $("#total_price").text(FloatAdd(parseFloat($('#subsidy').val()),parseFloat($('#order_price').text())));
    });

    //计算总价格
    $('#subsidy').change(function(){
        var m = 0.0;
        if($(this).val() =='' || $(this).val()==0) $("#total_price").text($('#order_price').text());
        m = FloatAdd(parseFloat($(this).val()),parseFloat($('#order_price').text()));
        if(parseFloat($('#subsidy').val()))  $("#total_price").text(m);
        s=0.0;
    });

    //价格计算方法
    function FloatAdd(arg1,arg2){
        var r1,r2,m;
        try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
        try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
        m=Math.pow(10,Math.max(r1,r2))
        return (arg1*m+arg2*m)/m
    }
})