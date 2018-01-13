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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo $title; ?></a></li><!-- 
                    <li><a href="<?php echo $bike_action; ?>" data-toggle="tab"><?php echo $title_bike; ?></a></li>
                    <li><a href="<?php echo $lock_action; ?>" data-toggle="tab"><?php echo $title_lock; ?></a></li> -->
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
                               <!--  <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-deposit">押金(元)</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="config_operator_deposit" value="<?php echo $data['config_operator_deposit']; ?>" placeholder="押金" id="input-deposit" class="form-control" />
                                        <?php if (isset($error['config_operator_deposit'])) { ?><div class="text-danger"><?php echo $error['config_operator_deposit']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div> -->
                                <!-- 交易安全校验码（key） -->
                                <!-- <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-wechat">微信公众号</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="config_wechat" value="<?php echo $data['config_wechat']; ?>" placeholder="微信公众号" id="input-wechat" class="form-control" />
                                        <?php if (isset($error['config_wechat'])) { ?><div class="text-danger"><?php echo $error['config_wechat']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div> -->
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-phone"><?php echo @$lang['t16'];?></label>
                                    <div class="col-sm-5">
                                        <input type="text" name="config_phone" value="<?php echo $data['config_phone']; ?>" placeholder="<?php echo @$lang['t16'];?>" id="input-phone" class="form-control" />
                                        <?php if (isset($error['config_phone'])) { ?><div class="text-danger"><?php echo $error['config_phone']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-phone"><?php echo @$lang['t17'];?></label>
                                    <div class="col-sm-5">
                                        <input type="text" name="config_hotline" value="<?php echo $data['config_hotline']; ?>" placeholder="<?php echo @$lang['t17'];?>" id="input-phone" class="form-control" />
                                        <?php if (isset($error['config_hotline'])) { ?><div class="text-danger"><?php echo $error['config_hotline']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-email"><?php echo @$lang['t18'];?></label>
                                    <div class="col-sm-5">
                                        <input type="text" name="config_email" value="<?php echo $data['config_email']; ?>" placeholder="<?php echo @$lang['t18'];?>" id="input-email" class="form-control" />
                                        <?php if (isset($error['config_email'])) { ?><div class="text-danger"><?php echo $error['config_email']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-web"><?php echo @$lang['t19'];?></label>
                                    <div class="col-sm-5">
                                        <input type="text" name="config_web" value="<?php echo $data['config_web']; ?>" placeholder="<?php echo @$lang['t19'];?>" id="input-web" class="form-control" />
                                        <?php if (isset($error['config_web'])) { ?><div class="text-danger"><?php echo $error['config_web']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                <!-- <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-web">成为免费单车的天数</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="config_free_bike_day" value="<?php echo $data['config_free_bike_day']; ?>" placeholder="天数" id="input-web" class="form-control" />
                                        <?php if (isset($error['config_free_bike_day'])) { ?><div class="text-danger"><?php echo $error['config_free_bike_day']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div> -->
                                
                            </div>
                            <div class="form-group">
                                <div class="col-sm-7 text-center" >
                                    <button type="submit" class="btn btn-large btn-success " style="padding: 0 30px;height: 40px;font-size: 14px;line-height: 40px;"><?php echo @$lang['t8'];?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php echo $footer;?>