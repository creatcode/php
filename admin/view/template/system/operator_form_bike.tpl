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
                    
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo $title_bike; ?></a></li>
                    <li><a href="<?php echo $lock_action; ?>" data-toggle="tab"><?php echo $title_lock; ?></a></li>
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
                                <!-- <div class="form-group">
                                    <label class="col-sm-2 control-label" for="input-rent">车辆长时未租</label>

                                    <div class="col-sm-5 input-group">
                                        <input type="number" name="config_bike_rent" value="<?php echo $data['config_bike_rent']; ?>" placeholder="车辆长时未租时间" id="input-rent" class="form-control" />
                                        <span class="input-group-addon">小时</span>
                                        <small class="help-block">注意：只能填写数字</small>
                                        <?php if (isset($error['config_bike_rent'])) { ?>
                                        <div class="text-danger"><?php echo $error['config_bike_rent']; ?></div><?php } ?>
                                        
                                    </div>
                                </div> -->
                               <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t5'];?></label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" id="input-rent" name="config_bike_rent" value="<?php echo $data['config_bike_rent']; ?>" placeholder="<?php echo @$lang['t5'];?>"  class="form-control">
                                            <span class="input-group-addon"><?php echo @$lang['t7'];?></span>
                                        </div>
                                         <?php if (isset($error['config_bike_rent'])) { ?>
                                        <div class="text-danger"><?php echo $error['config_bike_rent']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t6'];?></label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="config_bike_return" value="<?php echo $data['config_bike_return']; ?>" placeholder="<?php echo @$lang['t6'];?>" id="input-return"  class="form-control">
                                            <span class="input-group-addon"><?php echo @$lang['t7'];?></span>
                                        </div>
                                        <?php if (isset($error['config_bike_return'])) { ?><div class="text-danger"><?php echo $error['config_bike_return']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <!-- <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-return">车辆长时未还</label>
                                    <div class="col-sm-5 input-group">
                                        <input type="number" name="config_bike_return" value="<?php echo $data['config_bike_return']; ?>" placeholder="车辆长时未还时间" id="input-return" class="form-control" />
                                        <?php if (isset($error['config_bike_return'])) { ?><div class="text-danger"><?php echo $error['config_bike_return']; ?></div><?php } ?>
                                        <small class="help-block">注意：只能填写数字</small>
                                    </div>
                                </div> -->
                            </div>
                             <div class="form-group">
                            <div class="col-sm-7">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-sm btn-success margin-r-5" style="padding: 0 30px;height: 40px;font-size: 14px;line-height: 40px;"><?php echo @$lang['t8'];?></button>
                                    <!-- <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default">返回</a> -->
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