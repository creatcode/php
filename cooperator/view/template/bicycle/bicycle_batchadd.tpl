<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>单车管理</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
    <?php echo $statistics_in_page_header;?>
</section>
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
                                    <label class="col-sm-2 control-label">单车编号</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-6">
                                            <input type="number" name="bicycle_sn_start" class="form-control text-center">
                                            <span class="input-group-addon" style="border-left: 0;border-right: 0;">~</span>
                                            <input type="number" name="bicycle_sn_end" class="form-control text-center">
                                            <span class="input-group-addon no-border bicycle-num"></span>
                                        </div>
                                        <?php if (isset($error['bicycle_sn'])) { ?><div class="text-danger"><?php echo $error['bicycle_sn']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">单车类型</label>
                                    <div class="col-sm-5">
                                        <select name="type" class="form-control">
                                            <?php foreach($types as $k => $v) { ?>
                                            <option value="<?php echo $k; ?>" <?php if ((string)$k == $data['type']) { ?>selected<?php } ?>><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['type'])) { ?><div class="text-danger"><?php echo $error['type']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">所在区域</label>
                                    <div class="col-sm-5">
                                        <select name="region_id" class="form-control">
                                            <?php foreach($regions as $v) { ?>
                                            <option value="<?php echo $v['region_id']; ?>" <?php if ((string)$v['region_id'] == $data['region_id']) { ?>selected<?php } ?>><?php echo $v['region_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['region_id'])) { ?><div class="text-danger"><?php echo $error['region_id']; ?></div><?php } ?>
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
<?php if (isset($bicycles) && !empty($bicycles) && is_array($bicycles)) { ?>
<div class="col-xs-12">
    <div class="box no-border">
        <div class="box-body">
            <div class="form-group">
                <button class="btn btn-primary btn-sm" form="table_form" formaction="<?php echo $export_qrcode_action; ?>"><i class="fa fa-qrcode"></i>&nbsp;导出二维码</button>
            </div>
            <form id="table_form" class="table_form" method="post">
            <table class="table table-bordered table-hover dataTable" role="grid">
                <thead>
                <tr>
                    <th>单车编号</th>
                    <th>单车类型</th>
                    <th>区域</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach($bicycles as $bicycle) { ?>
                <tr>
                    <td><input type="hidden" name="selected[]" value="<?php echo $bicycle['bicycle_id']; ?>" /><?php echo $bicycle['bicycle_sn']; ?></td>
                    <td><?php echo $bicycle['type_name']; ?></td>
                    <td><?php echo $bicycle['region_name']; ?></td>
                </tr>
                <?php } ?>
                </tbody>
            </table>
            </form>
        </div>
    </div>
</div>
<?php } ?>
<script type="text/javascript">
    $('[name="bicycle_sn_start"],[name="bicycle_sn_end"]').change(function () {
        $(".bicycle-num").text('');
        var bicycle_sn_start = parseInt($('[name="bicycle_sn_start"]').val());
        var bicycle_sn_end = parseInt($('[name="bicycle_sn_end"]').val());
        var num = bicycle_sn_end - bicycle_sn_start;
        if (bicycle_sn_start > 0 && bicycle_sn_end > 0) {
            $(".bicycle-num").text(num + 1 + '台');
        }
    });
    
    
    

    
</script>
<?php echo $footer;?>