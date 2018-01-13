<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo @$lang['t44'];?></span>
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
                        <?php if (isset($success)) { ?>
                        <div class="alert bg-light-blue"><i class="fa fa-check-circle"></i>&nbsp;<?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t47'];?></label>
                                    <div class="col-sm-5">
                                        <input type="text" name="threshold_height" value="<?php echo @$data['threshold_height']; ?>" class="form-control" />
                                        <?php if (isset($error['threshold_height'])) { ?><div class="text-danger"><?php echo $error['threshold_height']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t48'];?></label>
                                    <div class="col-sm-5">
                                        <input type="text" name="threshold_low" value="<?php echo @$data['threshold_low']; ?>" class="form-control" />
                                        <?php if (isset($error['threshold_low'])) { ?><div class="text-danger"><?php echo $error['threshold_low']; ?></div><?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-8">
                                    <div style="text-align:center">
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5" style="margin-right: 40px;padding:0 30px;height:40px;font-size:14px;"><?php echo @$lang['t31'];?></button>
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding:0 30px;height:40px;font-size:14px;line-height:40px"><?php echo @$lang['t32'];?></a>
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
