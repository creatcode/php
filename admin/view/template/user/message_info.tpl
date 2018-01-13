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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo @$lang['t8'];?></a></li>
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
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t9'];?></label>
                                    <div class="col-sm-5" style="margin-top: 7px;">
                                        <span><?php echo $data['msg_title']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t10'];?></label>
                                    <div class="col-sm-5" style="margin-top: 7px;">
                                        <div class="img-thumbnail"><img src="<?php echo $data['msg_image_url']; ?>" style="max-width: 200px; max-height: 200px;" /></div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t11'];?></label>
                                    <div class="col-sm-5" style="margin-top: 7px;">
                                        <span><?php echo $data['user_name']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t12'];?></label>
                                    <div class="col-sm-5" style="margin-top: 7px;">
                                        <span><?php echo $data['msg_abstract']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t13'];?></label>
                                    <div class="col-sm-5" style="margin-top: 7px;">
                                        <div style="word-break: break-all;"><a href="<?php echo $data['msg_link']; ?>" target="_blank"><?php echo $data['msg_link']; ?></a></div>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t14'];?></label>
                                    <div class="col-sm-5" style="margin-top: 7px;">
                                        <span><?php echo $data['msg_content']; ?></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div class="pull-right">
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding: 0 30px;height: 40px;font-size: 14px;line-height: 40px;"><?php echo @$lang['t15'];?></a>
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
