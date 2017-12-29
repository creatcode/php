<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>礼品订单管理</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">礼品订单列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="region">
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
                                    <td><?php echo $data['activity_title']?></td>
                                    <td><?php echo $data['gift_name']?></td>
                                    <td><?php echo $data['mobile']?></td>
                                    <td><?php echo $data['gift_num']?></td>
                                    <td><?php echo $data['activity_state']?></td>
                                    <td>
                                        <?php if ($data['state'] == 0) { ?><button type="button" class="btn btn-info button-shipping" data-url="<?php echo $data['shipping_action']; ?>"><i class="fa fa-fw fa-truck"></i>发货</button><?php } ?>
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

<div class="modal fade" id="handling-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <span aria-hidden="true">×</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">礼品发货</h4>
            </div>
            <div class="modal-body">
                <input type="hidden" name="action" class="form-control">
                <div class="box-body">
                    <div class="alert alert-danger" style="display: none;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span></span>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <div class="form-group col-sm-12">
                        <label for="" class="col-sm-2 control-label" style="width:12%">物流公司</label>
                        <div class="col-sm-10">
                            <input type="text" name="shipping_company" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label for="shipping_code" class="col-sm-2 control-label" style="width:12%">物流单号</label>
                        <div class="col-sm-10">
                            <input type="text" name="shipping_code" class="form-control">
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <label for="remark" class="col-sm-2 control-label" style="width:12%">备注</label>
                        <div class="col-sm-10">
                            <textarea name="remark" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="form-group col-sm-12">
                        <div class="pull-right">
                            <button type="button" class="btn btn-success button-submit">提交</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- /.content -->
<script type="text/javascript">
    // 时间插件
    $('.date-range').daterangepicker({
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
        }
    });
    // 筛选
    $("#filter_type").change(function() {
        $("#filter_text").attr("name", $(this).val());
    });

    // 发货
    $(".button-shipping").click(function() {
        var url = $(this).data("url");
        $('[name="action"]').val(url);
        $('#handling-modal').modal();
    });

    // 提交发货信息
    $('.button-submit').click(function() {
        var shipping_company = $('[name="shipping_company"]').val();
        var shipping_code = $('[name="shipping_code"]').val();
        var remark = $('[name="remark"]').val();
        var action = $('[name="action"]').val();

        $.ajax(action, {
            dataType: 'json',
            data: {shipping_company: shipping_company, shipping_code: shipping_code, remark: remark},
            method: 'POST',
            success: function (result) {
                console.log(result);
                if (result.errorCode == 0) {
                    $('#handling-modal').modal('hide');
                    window.location.reload();
                } else {
                    $(".modal .alert-danger").show();
                    $(".modal .alert-danger span").text(result.msg);
                }
            }
        });
    });
</script>
<?php echo $footer;?>