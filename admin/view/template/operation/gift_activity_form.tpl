<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>礼品活动管理</span>
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
                                    <label class="col-sm-2 control-label">活动标题</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="activity_title" value="<?php echo $data['activity_title']; ?>" class="form-control">
                                        <?php if (isset($error['activity_title'])) { ?><div class="text-danger"><?php echo $error['activity_title']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">活动图片</label>
                                    <div class="col-sm-5">
                                        <button type="button" class="img-thumbnail button-upload" style="outline: none;" data-tag="logo" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                            <img src="<?php echo $data['activity_image_url']; ?>" alt="消息图片" style="max-width: 100px; max-height: 100px;" class="imageurl">
                                            <input type="hidden" name="activity_image" value="<?php echo $data['activity_image']; ?>" placeholder="消息图片" class="filepath">
                                        </button>
                                        <?php if (isset($error['activity_image'])) { ?><div class="text-danger"><?php echo $error['activity_image']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">状态</label>
                                    <div class="col-sm-5">
                                        <input type="checkbox" name="activity_state" value="1" placeholder="状态" class="bootstrap-switch in-list" data-on-text="启用" data-off-text="停用" data-label-width="5" <?php echo $data['activity_state']==1 ? 'checked' : ''; ?> />
                                        <?php if (isset($error['activity_state'])) { ?><div class="text-danger"><?php echo $error['activity_state']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">活动时间</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="activity_time" value="<?php echo $data['activity_time']; ?>" class="form-control date-range">
                                        <?php if (isset($error['activity_time'])) { ?><div class="text-danger"><?php echo $error['activity_time']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">活动描述</label>
                                    <div class="col-sm-5">
                                        <textarea name="activity_description" class="form-control" rows="5"><?php echo $data['activity_description']; ?></textarea>
                                        <?php if (isset($error['activity_description'])) { ?><div class="text-danger"><?php echo $error['activity_description']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">活动礼品</label>
                                    <div class="col-sm-5">
                                        <div class="row" style="max-height: 300px;">
                                            <?php foreach($gifts as $gift) { ?>
                                            <label class="col-sm-3">
                                                <input type="checkbox" name="activityGifts[]" value="<?php echo $gift['gift_id']; ?>" <?php echo in_array($gift['gift_id'], $activityGifts) ? ' checked': '' ?> />
                                                <?php echo $gift['gift_name']; ?>
                                            </label>
                                            <?php } ?>
                                        </div>
                                        <?php if (isset($error['activity_description'])) { ?><div class="text-danger"><?php echo $error['activity_description']; ?></div><?php } ?>
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