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
        if(confirm('请确认是否改派为选中司机？')){
            var driver_id=$(this).val();
            var order_type=$("input:checkbox[name='order_id']:checked").attr('order_type');
            $.post('/orders/Index/change_driver',{'order_id':$('#order_id_checked').val(), 'driver_id':$(this).val(), 'order_type':order_type},function(res,status){
                var res=jQuery.parseJSON(res);
                if(status=='success' & res.status){
                    toastr.success(res.msg, '', res.url);
                    return false;
                }else{
                    toastr.error(res.msg);
                    return false;
                }
            });
        }else{
            return false;
        }
    });

});