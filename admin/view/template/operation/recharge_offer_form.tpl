<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>编辑充值优惠</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo $title;?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">充值金额</label>
                                    <div class="col-sm-5">
                                        <input type="number" name="recharge_amount" readonly value="<?php echo $data['recharge_amount']; ?>" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">赠送金额</label>
                                    <div class="col-sm-5">
                                        <input type="number" name="present_amount" value="<?php echo $data['present_amount']; ?>" class="form-control">
                                        <?php if (isset($error['present_amount'])) { ?><div class="text-danger"><?php echo $error['present_amount']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">有效时间</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="effect_time" value="<?php echo $data['effect_time'];?>" class="form-control date-range">
                                        <?php if (isset($error['effect_time'])) { ?><div class="text-danger"><?php echo $error['effect_time']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-grop">
                                    <label class="col-sm-2 control-label">是否启用</label>
                                    <div class="col-sm-5">
                                        <input type="checkbox" name="state" value="1" placeholder="状态" class="bootstrap-switch in-list" data-on-text="启用" data-off-text="停用" data-label-width="5" <?php echo $data['state']==1 ? 'checked' : ''; ?> />
                                        <?php if (isset($error['state'])) { ?><div class="text-danger"><?php echo $error['state']; ?></div><?php } ?>
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
<script type="text/javascript">
    $('.date-range').daterangepicker({
        locale : {
            format: 'YYYY-MM-DD',
            isAutoVal: false
        }
    });
</script>
<?php echo $footer; ?>