<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo @$lang['t2'];?></span>
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
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t31'];?>：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['mobile'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t58'];?>：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $has_waiting_checkout_order;?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t4'];?>：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_sn'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t4'];?>：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_payment_time'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t65'];?>：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $payment_name[$recharge_info['pdr_payment_code']];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t35'];?>：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $payment_type[$recharge_info['pdr_payment_type']];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t66'];?>：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_payment_state'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t9'];?>：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_amount'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t67'];?>：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['has_cash_amount']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t71'];?>：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['available_deposit']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t68'];?>：</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="cash_amount" value="<?php echo $data['cash_amount']; ?>" class="form-control">
                                        <small>（<?php echo @$lang['t69'];?><?php echo $recharge_info['allow_cash_amount']; ?><?php echo @$lang['t44'];?>）</small>
                                        <?php if (isset($error['cash_amount'])) { ?><div class="text-danger"><?php echo $error['cash_amount']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t70'];?>：</label>
                                    <div class="col-sm-5">
                                        <textarea name="cash_reason" class="form-control" rows="5"><?php echo $data['cash_reason']; ?></textarea>
                                        <?php if (isset($error['cash_reason'])) { ?><div class="text-danger"><?php echo $error['cash_reason']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-7 text-center">
                                            <button type="submit" class="btn btn-sm btn-success margin-r-5" style="padding: 0 30px;height: 40px;font-size: 14px;line-height: 40px;"><?php echo @$lang['t64'];?></button>
                                            <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding: 0 30px;height: 40px;font-size: 14px;line-height: 40px;"><?php echo @$lang['t48'];?></a>
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
