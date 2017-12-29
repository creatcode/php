<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>区域广告管理</span>
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
                            <input type="hidden" name="adv_id" value="<?php echo $data['adv_id']; ?>"  />
                            <div class="row">
                                <div class="form-group">
                                <label class="col-sm-2 control-label">大图片上传</label>
                                <div class="col-sm-5">
                                    <button type="button" class="btn btn-primary btn-sm button-upload" data-useoriname="1" data-ftype="upload" data-tag="image" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                        <i class="fa fa-upload"></i>
                                        <div class="inline">上传</div>
                                        <input type="hidden" name="adv_image" value="<?php echo $data['adv_image']; ?>" class="filepath">
                                        <span class="filepath"><?php echo $data['adv_image']; ?></span>
                                    </button>
                                    <img src="<?php echo $data['all_adv_image']; ?>" class="filepath" height="35" >
                                    <?php if (isset($error['adv_image'])) { ?>
                                    <div class="text-danger"> <?php echo $error['adv_image']; ?> </div>
                                    <?php } ?>
                                </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">中大图片上传</label>
                                    <div class="col-sm-5">
                                        <button type="button" class="btn btn-primary btn-sm button-upload" data-ftype="upload" data-tag="image" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                            <i class="fa fa-upload"></i>
                                            <div class="inline">上传</div>
                                            <input type="hidden" name="adv_image_2x" value="<?php echo $data['adv_image_2x']; ?>" class="filepath">
                                            <span class="filepath"><?php echo $data['adv_image_2x']; ?></span>
                                        </button>
                                        <img src="<?php echo $data['all_adv_image_2x']; ?>" class="filepath" height="35" >
                                        <?php if (isset($error['adv_image_2x'])) { ?>
                                        <div class="text-danger"> <?php echo $error['adv_image_2x']; ?> </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                <label class="col-sm-2 control-label">小图片上传</label>
                                <div class="col-sm-5">
                                    <button type="button" class="btn btn-primary btn-sm button-upload" data-ftype="upload" data-tag="image" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                        <i class="fa fa-upload"></i>
                                        <div class="inline">上传</div>
                                        <input type="hidden" name="adv_image_1x" value="<?php echo $data['adv_image_1x']; ?>" class="filepath">
                                        <span class="filepath"><?php echo $data['adv_image_1x']; ?></span>
                                    </button>
                                    <img src="<?php echo $data['all_adv_image_1x']; ?>" class="filepath" height="35" >
                                    <?php if (isset($error['adv_image_1x'])) { ?>
                                    <div class="text-danger"> <?php echo $error['adv_image_1x']; ?> </div>
                                    <?php } ?>
                                </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">广告链接</label>
                                    <div class="col-sm-5">

                                        <input type="text" name="adv_link" value="<?php echo $data['adv_link']; ?>" class="form-control" />
                                        <?php if (isset($error['adv_link'])) { ?>
                                        <div class="text-danger"> <?php echo $error['adv_link']; ?> </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">ios广告链接</label>
                                    <div class="col-sm-5">

                                        <input type="text" name="ios_link" value="<?php echo $data['ios_link']; ?>" class="form-control" />
                                        <?php if (isset($error['ios_link'])) { ?>
                                        <div class="text-danger"> <?php echo $error['ios_link']; ?> </div>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">有效时间-失效时间</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="adv_effect_time" value="<?php echo $data['adv_effect_time']; ?>" class="form-control date-range">
                                        <?php if (isset($error['adv_effect_time'])) { ?><div class="text-danger"><?php echo $error['adv_effect_time']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                <label class="col-sm-2 control-label">开始时间-结束时间</label>
                                <div class="col-sm-5">
                                    <input type="text" name="adv_start_time" value="<?php echo $data['adv_start_time']; ?>" class="form-control date-range">
                                    <?php if (isset($error['adv_start_time'])) { ?><div class="text-danger"><?php echo $error['adv_start_time']; ?></div><?php } ?>
                                </div>
                                </div>
                                <div class="form-group">
                                <label class="col-sm-2 control-label">备注</label>
                                <div class="col-sm-5">
                                    <input type="text" name="adv_add_memo" value="<?php echo $data['adv_add_memo']; ?>" class="form-control" />
                                    <?php if (isset($error['adv_add_memo'])) { ?>
                                        <div class="text-danger"><?php echo $error['adv_add_memo']; ?></div>
                                    <?php } ?>
                                </div>
                                </div>
                                <div class="form-group">
                                <label class="col-sm-2 control-label">广告排序</label>
                                <div class="col-sm-5">
                                    <input type="text" name="adv_sort" value="<?php echo $data['adv_sort']; ?>" class="form-control" />
                                    <?php if (isset($error['adv_sort'])) { ?>
                                        <div class="text-danger"><?php echo $error['adv_sort']; ?></div>
                                    <?php } ?>
                                </div>
                                </div>
                                <div class="form-group">
                                <label class="col-sm-2 control-label">投放位置</label>
                                <div class="col-sm-5">
                                    <select name="adv_type" class="form-control">
                                        <?php if (!empty($adv_types)) { ?>
                                        <?php foreach($adv_types as $k => $v) { ?>
                                        <option value="<?php echo $k; ?>" <?php echo $k == $data['adv_type'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                    <?php if (isset($error['adv_type'])) { ?><div class="text-danger"><?php echo $error['adv_type']; ?></div><?php } ?>
                                </div>
                                </div>
                                <div class="form-group">
                                <label class="col-sm-2 control-label">区域</label>
                                <div class="col-sm-5">
                                    <select name="adv_region_id" class="form-control">
                                        <option value="0">全国</option>
                                        <?php if (!empty($region_list)) { ?>
                                        <?php foreach($region_list as $v) { ?>
                                        <option value="<?php echo $v['region_id']; ?>" <?php echo $v['region_id'] == $data['adv_region_id'] ? 'selected' : ''; ?>><?php echo $v['region_name']; ?></option>
                                        <?php } ?>
                                        <?php } ?>
                                    </select>
                                    <?php if (isset($error['adv_region_id'])) { ?><div class="text-danger"><?php echo $error['adv_region_id']; ?></div><?php } ?>
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
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.css" />
<script type="text/javascript" src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
<script type="text/javascript">
        $(document).ready(function() {
            $('input.bootstrap-switch').bootstrapSwitch();
        });


$('.date-range').daterangepicker({
    locale:{
        format: 'YYYY-MM-DD',
        isAutoVal:false,
    }
});

</script>


<?php echo $footer;?>