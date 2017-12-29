<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>消费记录</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">消费记录详情</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post">
                            <div class="row">

                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">订单sn</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['order_sn']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">用户名</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['user_name']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">锁sn</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['lock_sn']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">单车sn</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['bicycle_sn']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">区域</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['region_name']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">开始时间</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['start_time']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">出发经度</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['start_lng']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">出发纬度</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['start_lat']; ?></h5>
                                    </div>
                                </div>
                                <?php if ($data['order_status'] == 2) { ?>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">结束时间</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['end_time']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">结算时间</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['settlement_time']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">订单金额（元）</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['order_amount']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">结束经度</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['end_lng']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">结束纬度</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['end_lat']; ?></h5>
                                    </div>
                                </div>
                                <?php } ?>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">订单状态</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['order_state']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">下单时间</label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['add_time']; ?></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="pull-right">
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
<link rel="stylesheet" href="//cache.amap.com/lbs/static/main1119.css"/>
<script type="text/javascript"  src="//webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d&plugin=AMap.Geocoder&callback=initMap"></script>
<script src="<?php echo HTTP_CATALOG;?>js/coordinate.js"></script>
<script type="text/javascript">
    <?php if (isset($data['lng']) && isset($data['lat'])) { ?>
        var lnglat = wgs84togcj02(parseFloat(<?php echo $data['lng']; ?>), parseFloat(<?php echo $data['lat']; ?>));
    <?php } ?>

    var initMap = function(){
        if(typeof AMap != 'undefined') {
            var marker, map = new AMap.Map("container", {
                resizeEnable: true,
                zoom: 13
            });
            marker = new AMap.Marker({
                map: map
            });
            if (typeof lnglat != 'undefined') {
                marker.setPosition(lnglat);
                map.setCenter(lnglat);
            }
        }
    };
</script>
<?php echo $footer;?>
