<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>优惠券管理</span>
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
                                    <label class="col-sm-2 control-label">优惠券类型</label>
                                    <div class="col-sm-5">
                                        <label class="radio-inline">
                                            <input type="radio" name="coupon_type" value="3" />代金券(数值单位：元)
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="coupon_type" value="1" />减免时间（数值单位：分钟）
                                        </label>
                                        <label class="radio-inline">
                                            <input type="radio" name="coupon_type" value="2" />单次体验券
                                        </label>
                                        <?php if (isset($error['coupon_type'])) { ?><div class="text-danger"><?php echo $error['coupon_type']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">优惠券数值</label>
                                    <div class="col-sm-5">
                                        <input type="number" name="number" value="<?php echo $data['number']; ?>" class="form-control">
                                        <?php if (isset($error['number'])) { ?><div class="text-danger"><?php echo $error['number']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">有效时间</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="valid_time" value="<?php echo $data['valid_time']; ?>" class="form-control date-range" style="width: 200px;">
                                        <?php if (isset($error['valid_time'])) { ?><div class="text-danger"><?php echo $error['valid_time']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">用户列表(每行一个手机号码):</label>
                                    <div class="col-sm-5">
                                        <textarea name="mobiles" class="form-control date" rows="5"><?php echo $data['mobiles']; ?></textarea>
                                        <?php if (isset($error['mobiles'])) { ?><div class="text-danger"><?php echo $error['mobiles']; ?></div><?php } ?>
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


<script type="text/javascript">
    $(function () {
        $('[name="coupon_type"]').on("change", function () {
            if ($(this).val() == 2) {
                $('[name="number"]').parents(".form-group").fadeOut();
            } else {
                $('[name="number"]').parents(".form-group").fadeIn();
            }
        });
    });
    $('.date-range').daterangepicker({
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
        }
    });

    $(function () {
        $('[name="mobiles"]').on('change', function () {

        });
    });
</script>

<?php echo $footer;?>