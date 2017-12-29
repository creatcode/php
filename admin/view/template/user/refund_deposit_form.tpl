<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>充值管理</span>
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
                            <input type="hidden" name="type" id="type">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">用户：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['mobile'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">充值订单号：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_sn'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">充值时间：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_payment_time'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">支付方式：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_payment_name'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">支付途径：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_payment_type'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">充值订单状态：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_payment_state'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">押金金额：</label>
                                    <div class="col-sm-5">
                                        <h5>￥<?php echo $recharge_info['pdr_amount'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">申请退款金额：</label>
                                    <div class="col-sm-5">
                                        <h5>￥<?php echo $cash_apply_info['apply_cash_amount'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">收款方式：</label>
                                    <div class="col-sm-5">
                                        <h5><b>
                                            <?php echo !empty($cash_apply_info['apply_payment_type'])?$apply_payment_types[$cash_apply_info['apply_payment_type']]:'unknow';?>
                                        </b></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">收款账号：</label>
                                    <div class="col-sm-5">
                                        <h5><b><?php echo $cash_apply_info['apply_account'];?></b></h5>
                                    </div>
                                </div>
                                <div style="display: <?php echo $cash_apply_info['apply_payment_type']==3?'inline':'none'; ?>">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">收款户名：</label>
                                        <div class="col-sm-5">
                                            <h5><b><?php echo $cash_apply_info['apply_account_name'];?></b></h5>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">收款银行：</label>
                                        <div class="col-sm-5">
                                            <h5><b><?php echo $cash_apply_info['apply_bank_name'];?></b></h5>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">收款支行：</label>
                                        <div class="col-sm-5">
				            <h5><b><?php echo $cash_apply_info['apply_sub_bank_name'];?></b></h5>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">退款理由：</label>
                                    <div class="col-sm-5">
                                        <h5 class="text-red"><?php echo $cash_apply_info['apply_cash_reason'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">允许退款：</label>
                                    <div class="col-sm-5">
                                        <label class="margin-r-5"><input type="radio" name="apply_state" value="1" <?php echo $data['apply_state'] == 1 ? 'checked' : ''; ?>>通过</label>
                                        <label><input type="radio" name="apply_state" value="2" <?php echo $data['apply_state'] == 2 ? 'checked' : ''; ?>>不通过</label>
                                        <?php if (isset($error['apply_state'])) { ?><div class="text-danger"><?php echo $error['apply_state']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">审核结果：</label>
                                    <div class="col-sm-5">
                                        <textarea name="apply_audit_result" class="form-control" rows="5"><?php echo $data['apply_audit_result']; ?></textarea>
                                        <?php if (isset($error['apply_audit_result'])) { ?><div class="text-danger"><?php echo $error['apply_audit_result']; ?></div><?php } ?>
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
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php echo $footer;?>
