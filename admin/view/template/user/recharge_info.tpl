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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo @$lang['t46'];?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post">
                            <div class="row">
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t4'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_sn']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t31'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['mobile']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t9'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_amount']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t10'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_type']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t15'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_payment_state_name']; ?></h5>
                                    </div>
                                </div>
                                <?php if ($data['pdr_payment_state'] == 1) { ?>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t65'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_payment_name']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t35'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_payment_type']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t38'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_trade_sn']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t32'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_payment_time']; ?></h5>
                                    </div>
                                </div>
                                <?php } ?>
                                <!-- <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">管理员名称</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_admin_name']; ?></h5>
                                    </div>
                                </div> -->
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t37'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['pdr_add_time']; ?></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="text-center">
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
