<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>运营设置</span>
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
                    <!-- <li><a href="<?php echo $oper_action; ?>" data-toggle="tab"><?php echo $title; ?></a></li> -->
                    <li><a href="<?php echo $oper_action; ?>" data-toggle="tab"><?php echo $title_bike; ?></a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo $title_lock; ?></a></li>
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
                                    <label class="col-sm-2 control-label" for="input-deposit">锁桩长期空闲时长</label>
                                    <div class="col-sm-5 input-group">
                                        <input type="text" name="config_lock_free_time" value="<?php echo $data['config_lock_free_time']; ?>" placeholder="锁桩长期空闲时长" id="input-deposit" class="form-control" />
                                        <span class="input-group-addon">小时</span>
                                        <?php if (isset($error['config_lock_free_time'])) { ?><div class="text-danger"><?php echo $error['config_lock_free_time']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div> -->
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">锁桩长期空闲时长</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="config_lock_free_time" value="<?php echo $data['config_lock_free_time']; ?>" placeholder="锁桩长期空闲时长" id="input-return"  class="form-control">
                                            <span class="input-group-addon">小时</span>
                                        </div>
                                        <?php if (isset($error['config_lock_free_time'])) { ?><div class="text-danger"><?php echo $error['config_lock_free_time']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">车辆长时未租</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="config_lock_rent" value="<?php echo $data['config_lock_rent']; ?>" placeholder="车辆长时未租" id="input-return"  class="form-control">
                                            <span class="input-group-addon">小时</span>
                                        </div>
                                        <?php if (isset($error['config_lock_rent'])) { ?><div class="text-danger"><?php echo $error['config_lock_rent']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                
                                 <div class="form-group">
                                    <label class="col-sm-2 control-label">车辆长时未还</label>
                                    
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="config_lock_return" value="<?php echo $data['config_lock_return']; ?>" placeholder="车辆长时未还" id="input-return"  class="form-control">
                                            <span class="input-group-addon">小时</span>
                                        </div>
                                        <?php if (isset($error['config_lock_return'])) { ?><div class="text-danger"><?php echo $error['config_lock_return']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                               
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-cycle">站点实时上传周期</label>
                                    <div class="col-sm-5">
                                    <div class="col-sm-12 input-group">
                                        <input type="text" name="config_upload_cycle" value="<?php echo $data['config_upload_cycle']; ?>" placeholder="站点实时上传周期" id="input-cycle" class="form-control" />
                                    </div>
                                        <?php if (isset($error['config_upload_cycle'])) { ?><div class="text-danger"><?php echo $error['config_upload_cycle']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-ratio">低电量比率</label>
                                    <div class="col-sm-5">
                                    <div class="col-sm-12 input-group">
                                        <input type="text" name="config_electricity_ratio" value="<?php echo $data['config_electricity_ratio']; ?>" placeholder="低电量比率" id="input-ratio" class="form-control" />
                                    </div>
                                        <?php if (isset($error['config_electricity_ratio'])) { ?><div class="text-danger"><?php echo $error['config_electricity_ratio']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-time">站点云端同步时间</label>
                                    <div class="col-sm-5">
                                    <div class="col-sm-12 input-group">
                                        <input type="text" name="config_site_time" value="<?php echo $data['config_site_time']; ?>" placeholder="站点云端同步时间" id="input-time" class="form-control" />
                                    </div>
                                        <?php if (isset($error['config_site_time'])) { ?><div class="text-danger"><?php echo $error['config_site_time']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-low">低电量阀值</label>
                                    <div class="col-sm-5">
                                    <div class="col-sm-12 input-group">
                                        <input type="text" name="config_low_threshold" value="<?php echo $data['config_low_threshold']; ?>" placeholder="低电量阀值" id="input-low" class="form-control" />
                                    </div>
                                        <?php if (isset($error['config_low_threshold'])) { ?><div class="text-danger"><?php echo $error['config_low_threshold']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-full">满电量阀值</label>
                                    <div class="col-sm-5">
                                    <div class="col-sm-12 input-group">
                                        <input type="text" name="config_full_threshold" value="<?php echo $data['config_full_threshold']; ?>" placeholder="满电量阀值" id="input-full" class="form-control" />
                                    </div>
                                        <?php if (isset($error['config_full_threshold'])) { ?><div class="text-danger"><?php echo $error['config_full_threshold']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div><div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-Charge">同时最大可充锁桩个数</label>
                                    <div class="col-sm-5">
                                    <div class="col-sm-12 input-group">
                                        <input type="text" name="config_max_piles" value="<?php echo $data['config_max_piles']; ?>" placeholder="同时最大可充锁桩个数" id="input-charge" class="form-control" />
                                    </div>
                                        <?php if (isset($error['config_max_piles'])) { ?><div class="text-danger"><?php echo $error['config_max_piles']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <button type="submit" class="btn btn-large btn-success pull-right">提交</button>
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