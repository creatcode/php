<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>区域广告管理</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
    <?php echo $statistics_in_page_header;?>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <!-- tab 标签 -->
                <ul class="nav nav-tabs">
                    <li class="active"><a href="javascript:;" data-toggle="tab">区域广告列表</a></li>
                </ul>

                <form class="search_form" action="<?php echo $return_action; ?>" method="get">
                    <!-- 搜索 -->
                    <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">

                        <select name="adv_region_id" class="input-sm">
                            <option value="">平台</option>
                            <?php if (!empty($region_list)) { ?>
                            <?php foreach($region_list as $v) { ?>
                            <option value="<?php echo $v['region_id']; ?>" <?php echo $v['region_id'] == $adv_region_id ? 'selected' : ''; ?>><?php echo $v['region_name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
                        <div class="pull-right">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                        </div>
                    </div>
                </form>



                <div class="tab-content">
                    <div class="form-group">
                        <a href="<?php echo $add_action; ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;新增</a>
                    </div>
                    <div class="tab-pane active" id="bicycle">
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>&nbsp;<?php echo $error['warning']; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <?php if (isset($success)) { ?>
                        <div class="alert bg-light-blue"><i class="fa fa-check-circle"></i>&nbsp;<?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form id="table_form" class="table_form" method="post">
                            <table class="table table-bordered table-hover dataTable" role="grid">
                                <thead>
                                <tr>
                                    <?php foreach ($data_columns as $column) { ?>
                                    <th><?php echo $column['text']; ?></th>
                                    <?php } ?>
                                    <th style="min-width:130px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <td><?php echo $data['adv_sort']?></td>
                                    <td><?php echo $data['adv_region_id']?></td>
                                    <td><?php echo '城市名'/*$data['adv_region_id']*/?></td>
                                    <td><?php echo $data['adv_start_time']?></td>
                                    <td><?php echo $data['adv_end_time']?></td>

                                    <td> <img class="bigimage" src="<?php echo $static.$data['adv_image']?>" height="35" > </td>

                                    <td><?php echo $data['adv_add_memo']?></td>
                                    <td><?php echo $data['adv_add_time']?></td>

                                    <td><?php echo $data['adv_approved']?></td>

                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $data['edit_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-pencil"></i>编辑</button>
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo $data['delete_action']; ?>">删除</a></li>
                                            </ul>
                                        </div>
                                        <div class="btn-group">
                                            <button data-aid="<?php echo $data['adv_id']?>"  type="button" class="btn btn-info sendClass">发送</button>
                                        </div>
                                        <div class="btn-group">
                                            <button data-aid="<?php echo $data['adv_id']?>"  type="button" class="btn btn-info ckeckClass">审批</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </form>
                        <div class="row"><div class="col-sm-6 text-left"><?php echo $pagination; ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section>
    <div class="modal fade" id="handling-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-blue-gradient" style="padding:10px">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel">区域广告审批</h4>
                </div>
                <div class="modal-body">
                    <form method="post" action="<?php echo $batch_handling_action; ?>">
                        <div class="box-body">
                            <div class="form-group col-sm-12">
                                <label for="" class="col-sm-2 control-label" style="width:12%">备注</label>
                                <div class="col-sm-10">
                                    <span class="adv_approve_memo adv_add_memo"> </span>
                                </div>
                            </div>
                            <div class="form-group col-sm-12">
                                <label for="" class="col-sm-2 control-label" style="width:12%">图片</label>
                                <div class="col-sm-10">
                                    <span class="adv_approve_memo "> <img class="adv_image bigimage" style="max-height: 200px; max-width: 600px;"  src="<?php echo $static; ?>\images/default.jpg" > </span>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 repair_type">
                                <label for="" class="col-sm-3 control-label" style="width:12%">状态</label>
                                <div class="col-sm-9">
                                    <label class="radio-inline pull-left">
                                        <input type="radio" name="adv_approved" class="adv_approved" value="1" />通过
                                    </label>
                                    <label class="radio-inline pull-left">
                                        <input type="radio" name="adv_approved" value="2" />不通过
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 handle_content">
                                <label for="" class="col-sm-2 control-label" style="width:12%">审批备注</label>
                                <div class="col-sm-10">
                                    <span><textarea class="col-sm-10" rows="3" name="adv_approve_memo" placeholder="请输入审批备注"></textarea></span>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="pull-right">
                                        <input class="adv_id" type="hidden" name="adv_id" value="" />
                                        <a href="javascript:;" class="btn btn-sm btn-primary batchHandlingSubmit">提交处理</a>
                                        <a href="javascript:;" class="btn btn-sm btn-default" data-dismiss="modal">取消</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</section>

<!--图片放大效果-->
<section>
    <div class="modal fade" id="handling-modal2">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-blue-gradient" style="padding:10px">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel">区域图片审批</h4>
                </div>
                <div class="modal-body">
                    <img class="bigimageshow" src="">
                </div>
            </div>
        </div>
    </div>

</section>

<!-- /.content -->

<script type="text/javascript">
    $('.date-range').daterangepicker({
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
        }
    });

    $("#filter_type").change(function() {
        $("#filter_text").attr("name", $(this).val());
    });
    $('.sendClass').click(function(){
        var adv_id = $(this).attr('data-aid');
        $.ajax({
            url:'<?php echo $send_msg; ?>',
            data:{adv_id:adv_id},
            dataType: 'json',
            method: 'POST',
            success:function(data){
                alert(data.msg);
                console.log(data);
            },
            error:function(err1,err2,err3){
                console.log(err1);
                console.log(err2);
                console.log(err3);
            }

        });
        console.log(adv_id);
    });

    $(document).on('click', '.ckeckClass', function () {
            var data_aid = $(this).attr('data-aid');
            $.ajax('<?php echo $get_advertisement_info; ?>', {
                dataType: 'json',
                 data: {adv_id:data_aid},
                method: 'POST',
                success: function (result) {
                    $('.adv_add_memo').text(result.data.adv_add_memo);
                    $('.adv_image').attr('src','<?php echo $static; ?>'+result.data.adv_image);
                    if(result.data.adv_approved == 1){
                        $('.adv_approved').attr("checked","checked");
                    }else{
                        $('.adv_approved').attr("checked","checked");
                    }
                    $('.adv_id').val(result.data.adv_id);

                },
                error:function(error1,error2,error3){
                    console.log(error1);
                    console.log(error2);
                    console.log(error3);
                }
            });

        $('#handling-modal').modal();

    });

    $('.batchHandlingSubmit').click(function () {
        $.ajax('<?php echo $check_url; ?>', {
            dataType: 'json',
            data: $(this).parents('form:first').serialize(),
            method: 'POST',
            success: function (result) {
                if(result.errorCode){
                    alert(result.msg);
                }else{
                    alert(result.msg);
                    location.reload();
                }
            }
        });
    });


    $('.bigimage').click(function () {
        var image_src = $(this).attr('src');
        $('.bigimageshow').attr('src',image_src);
        $('#handling-modal2').modal();
    });

</script>
<?php echo $footer;?>