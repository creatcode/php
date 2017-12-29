<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>站点管理</span>
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
                                    <label class="col-sm-2 control-label">站点编号</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="station_sn" value="<?php echo @$data['station_sn']; ?>" class="form-control" />
                                        <?php if (isset($error['station_sn'])) { ?><div class="text-danger"><?php echo $error['station_sn']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">站点状态</label>
                                    <div class="col-sm-5">
                                        <select name="station_states" class="form-control">
                                            <?php foreach($station_states as $k => $v) { ?>
                                            <option value="<?php echo $k; ?>" <?php if ((string)$k == @$data['station_state']) { ?>selected<?php } ?>><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['station_states'])) { ?><div class="text-danger"><?php echo $error['station_states']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">电池状态</label>
                                    <div class="col-sm-5">
                                        <select name="power_state" class="form-control">
                                            <?php foreach($station_power_states as $k => $v) { ?>
                                            <option value="<?php echo $k; ?>" <?php if ((string)$k == @$data['power_state']) { ?>selected<?php } ?>><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['power_state'])) { ?><div class="text-danger"><?php echo $error['power_state']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">所在区域</label>
                                    <div class="col-sm-5">
                                        <select name="cooperator_id" class="form-control">
                                            <option>请选择...</option>
                                            <?php foreach($regions as $key => $region) { ?>
                                            <option value="<?php echo $region['region_id']; ?>" <?php if ((string)$region['region_id'] == @$data['region_id']) { ?>selected<?php } ?>><?php echo $region['region_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['region_id'])) { ?><div class="text-danger"><?php echo $error['region_id']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">所在城市</label>
                                    <div class="col-sm-5">
                                        <select name="cooperator_id" class="form-control">
                                            <option>请选择...</option>
                                            <?php foreach($cooperators as $v) { ?>
                                            <option value="<?php echo $v['cooperator_id']; ?>" <?php if ((string)$v['cooperator_id'] == $data['cooperator_id']) { ?>selected<?php } ?>><?php echo $v['cooperator_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['cooperator_id'])) { ?><div class="text-danger"><?php echo $error['cooperator_id']; ?></div><?php } ?>
                                    </div>
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php echo $footer;?>
