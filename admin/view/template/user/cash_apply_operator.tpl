<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>提现管理</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo $title; ?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form id="myForm" class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <input type="hidden" name="pdc_id" value="<?php echo $pdc_id; ?>" >
                            <input type="hidden" name="type" id="type">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">提现编号：</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdc_sn'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">用户：</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdc_user_name'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">提现类型：</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdc_type'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">提现金额：</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdc_amount'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">申请时间：</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdc_add_time'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">支付方式：</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdc_payment_name'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">支付途径：</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdc_payment_type'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">退款时间：</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdc_payment_time'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">提现状态：</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdc_payment_state_text'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">充值编号：</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_sn'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <button data-url="<?php echo $return_action; ?>" type="button" class="btn pull-right link" style="margin-right: 21px;">返回</button>
                                        <!-- fix vincent:2017-08-09 更改显示同一退款的条件$data['pdc_payment_state'] != 1 => $data['pdc_payment_state'] == 0 -->
                                        <?php if ($data['pdc_payment_state'] == 0) { ?>
                                        <input type="button" data-type="agree" class="btn btn-success opr pull-right" style="margin-right: 5px;" value="同意提现">
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    $(function () {
        $('.opr').click(function () {
            var payment_code = '<?php echo $data['pdc_payment_code']; ?>';
            var $type = $(this).data('type');
            var msg = $type == 'disagree' ? '您确定要取消提现么？' : "您确定要同意提现吗？";
            if (confirm(msg)) {
                $('#type').val($type);
                $('#myForm').submit();
            }
        });
    });
</script>
<?php echo $footer;?>
