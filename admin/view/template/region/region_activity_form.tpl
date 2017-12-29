<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>区域活动</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
    <?php echo $statistics_in_page_header;?>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="nav-tabs-custom">
            <!-- tab 标签 -->
            <ul class="nav nav-tabs">
                <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo $title; ?></a></li>
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
                                <label for="region_id" class="col-sm-2 control-label">区域名称</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <select id="region_id" name="region_id" class="form-control">
                                            <option value="0">请选择区域</option>
                                            <?php foreach ($region_activity_options as $key => $option) { ?>
                                            <option value="<?php echo $key; ?>" <?php if ($key == $select_id) echo 'selected'; ?> ><?php echo $option; ?></option>
                                            <?php }?>
                                        </select>
                                    </div>
                                    <?php if (isset($error['region_id'])) { ?><div class="text-danger"><?php echo $error['region_id']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="region_id" class="col-sm-2 control-label">城市名称</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <select id="city_id" name="region_id" class="form-control">
                                            <option value="0">请选择城市</option>
                                        </select>
                                    </div>
                                    <?php if (isset($error['city_id'])) { ?><div class="text-danger"><?php echo $error['city_id']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="price" class="col-sm-2 control-label">活动价格</label>
                                <div class="col-sm-8">
                                    <input id="price" name="price" value="<?php echo $data['price'];?>" type="number" class="form-control">
                                    <?php if (isset($error['price'])) { ?><div class="text-danger"><?php echo $error['price']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">有效时间</label>
                                <div class="col-sm-5">
                                    <input type="text" name="effect_time" value="<?php echo $data['effect_time'];?>" class="form-control date-range">
                                    <?php if (isset($error['effect_time'])) { ?><div class="text-danger"><?php echo $error['effect_time']; ?></div><?php } ?>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-10">
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-sm btn-success margin-r-5">提交</button>
                                    <a href="<?php echo $return_action;?>" class="btn btn-sm btn-default">返回</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    $('.date-range').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD',
            isAutoVal: false
        }
    });
</script>
<?php echo $footer; ?>