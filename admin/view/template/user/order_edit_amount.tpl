<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>消费记录</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">修改订单金额</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">订单sn</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['order_sn']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">用户名</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['user_name']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">区域</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['region_name']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">开始时间</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['start_time']; ?></h5>
                                    </div>
                                </div>
                                <?php if ($data['order_status'] == 2) { ?>
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">结束时间</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['end_time']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">订单金额（元）</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['order_amount']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">订单实际支付金额（元）</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pay_amount']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">已退金额（元）</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['refund_amount']; ?></h5>
                                    </div>
                                </div>
                                <?php } ?>
                                <input type="hidden" name="order_sn" value="<?php echo $data['order_sn']; ?>" >
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">订单状态</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['order_state']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">下单时间</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['add_time']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">退回金额</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="amount" class="form-control">
                                        <?php if (isset($error['amount'])) { ?><div class="text-danger"><?php echo $error['amount']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="" class="col-sm-2 control-label">修改理由</label>
                                    <div class="col-sm-5">
                                        <textarea name="reason" class="form-control" rows="5"></textarea>
                                        <?php if (isset($error['reason'])) { ?><div class="text-danger"><?php echo $error['reason']; ?></div><?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div class="pull-right">
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5">提交</button>
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default">返回</a>
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
<script src="<?php echo HTTP_CATALOG;?>js/coordinate.js"></script>
<?php echo $footer;?>
