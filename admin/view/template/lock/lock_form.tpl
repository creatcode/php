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
                        <?php if (isset($success)) { ?>
                        <div class="alert bg-light-blue"><i class="fa fa-check-circle"></i>&nbsp;<?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t24'];?></label>
                                <div class="col-sm-5">
                                    <?php if (empty($lock_id)) { ?>
                                    <input type="text" name="lock_sn" value="<?php echo $data['lock_sn']; ?>" class="form-control" />
                                    <?php if (isset($error['lock_sn'])) { ?><div class="text-danger"><?php echo $error['lock_sn']; ?></div><?php } ?>
                                    <?php } else { ?>
                                    <input type="text" disabled name="edit_lock_sn" value="<?php echo $data['lock_sn']; ?>" class="form-control" />
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t25'];?></label>
                                <div class="col-sm-5">
                                    <input type="text" name="lock_name" value="<?php echo $data['lock_name']; ?>" class="form-control">
                                    <?php if (isset($error['lock_name'])) { ?><div class="text-danger"><?php echo $error['lock_name']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t26'];?></label>
                                <div class="col-sm-5">
                                    <select name="lock_type" class="form-control">
                                        <option <?php echo $data['lock_type'] == 1 ? 'selected':'' ?> value="1">GPRS</option>
                                        <option <?php echo $data['lock_type'] == 2 ? 'selected':'' ?> value="2">杭州蓝牙锁</option>
                                        <option <?php echo $data['lock_type'] == 3 ? 'selected':'' ?> value="3">机械</option>
                                        <option <?php echo $data['lock_type'] == 4 ? 'selected':'' ?> value="4">GPRS+蓝牙</option>
                                        <option <?php echo $data['lock_type'] == 4 ? 'selected':'' ?> value="5">深圳蓝牙锁(亦强锁)</option>
                                        <option <?php echo $data['lock_type'] == 4 ? 'selected':'' ?> value="6">亦强GPRS锁</option>
                                    </select>
                                </div>
                                <?php if (isset($error['lock_type'])) { ?><div class="text-danger"><?php echo $error['lock_type']; ?></div><?php } ?>
                            </div>
                 
                            <!--<div class="form-group">
                                <label class="col-sm-2 control-label">锁平台</label>
                                <div class="col-sm-5">
                                    <select name="lock_platform" class="form-control">
                                        <option <?php echo $data['lock_platform'] == 0 ? 'selected':'' ?> value="0">物联锁旧平台</option>
                                        <option <?php echo $data['lock_platform'] == 1 ? 'selected':'' ?> value="1">物联锁新平台</option>
                                        <option <?php echo $data['lock_platform'] == 2 ? 'selected':'' ?> value="2">亦强锁平台</option>
                                    </select>
                                </div>
                            </div>-->
                            <input type="hidden" name="lock_platform" value="0">
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t27'];?></label>
                                <div class="col-sm-5">
                                    <select name="lock_factory" class="form-control">
                                        <option <?php echo $data['lock_factory'] == 1 ? 'selected':'' ?> value="1">深圳锁厂</option>
                                        <option <?php echo $data['lock_factory'] == 2 ? 'selected':'' ?> value="2">杭州锁厂</option>
                                    </select>
                                    <?php if (isset($error['lock_factory'])) { ?><div class="text-danger"><?php echo $error['lock_factory']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t28'];?></label>
                                <div class="col-sm-5">
                                    <input type="text" name="batch_num" value="<?php echo $data['batch_num']; ?>" class="form-control">
                                    <?php if (isset($error['batch_num'])) { ?><div class="text-danger"><?php echo $error['batch_num']; ?></div><?php } ?>
                                </div>
                            </div>

                         
                                
                               
                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div style="text-align:center">
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5"  style="margin-right: 40px;padding:0 30px;height:40px;font-size:14px;"><?php echo @$lang['t29'];?></button>
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding:0 30px;height:40px;font-size:14px;line-height:40px"><?php echo @$lang['t30'];?></a>
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
