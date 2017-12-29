<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>区域管理</span>
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
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">礼品名称</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="gift_name" value="<?php echo $data['gift_name']; ?>" class="form-control">
                                        <?php if (isset($error['gift_name'])) { ?><div class="text-danger"><?php echo $error['gift_name']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">礼品总数量</label>
                                    <div class="col-sm-5">
                                        <input type="number" name="storage" value="<?php echo $data['storage']; ?>" class="form-control">
                                        <?php if (isset($error['storage'])) { ?><div class="text-danger"><?php echo $error['storage']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">限制兑换数量</label>
                                    <div class="col-sm-5">
                                        <div class="pull-left margin-r-5"><input type="checkbox" name="is_limit_num" value="1" class="bootstrap-switch in-list" data-on-text="是" data-off-text="否" data-label-width="5" <?php $data['is_limit_num'] == 1 ? ' checked' : ''; ?> /></div>
                                        <div class="pull-left"><input type="number" name="limit_num" value="<?php echo $data['limit_num']; ?>" placeholder="兑换数量" maxlength="4" class="form-control" style="width: 250px" <?php echo $data['is_limit_num'] == 1 ? '' : ' readonly'; ?> /></div>
                                        <?php if (isset($error['limit_num'])) { ?><div class="text-danger"><?php echo $error['limit_num']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">是否上架</label>
                                    <div class="col-sm-5">
                                        <input type="checkbox" name="is_show" value="1" placeholder="状态" class="bootstrap-switch in-list" data-on-text="是" data-off-text="否" data-label-width="5" <?php echo $data['is_show']==1 ? 'checked' : ''; ?> />
                                        <?php if (isset($error['is_show'])) { ?><div class="text-danger"><?php echo $error['is_show']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">排序</label>
                                    <div class="col-sm-5">
                                        <input type="number" name="sort_order" value="<?php echo $data['sort_order']; ?>" class="form-control">
                                        <?php if (isset($error['sort_order'])) { ?><div class="text-danger"><?php echo $error['sort_order']; ?></div><?php } ?>
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
<link rel="stylesheet" href="<?php echo HTTP_IMAGE . 'AdminLTE-2.3.7/';?>plugins/bootstrap-switch/bootstrap-switch.min.css" />
<script type="text/javascript" src="<?php echo HTTP_IMAGE . 'AdminLTE-2.3.7/'; ?>plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_IMAGE . 'AdminLTE-2.3.7/'; ?>plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // 开关控件
        $('input.bootstrap-switch').bootstrapSwitch();
        // 开关控件改变事件
        $('[name="is_limit_num"]').on('switchChange.bootstrapSwitch', function(event, state) {
            var status = state ? false : true;
            $('[name="limit_num"]').attr('readonly', status);
        });
        $('[name="is_limit_time"]').on('switchChange.bootstrapSwitch', function(event, state) {
            var status = state ? false : true;
            $('[name="limit_time"]').attr('readonly', status);
        });
        // 时间控件
        $('.date-range').daterangepicker({
            locale:{
                format: 'YYYY-MM-DD',
                isAutoVal:false,
            }
        });
    });
</script>
<?php echo $footer;?>