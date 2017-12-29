<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>锁详情</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
    <?php echo $statistics_in_page_header;?>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border"></div>
                <div class="box-body">
                    <form class="form-horizontal" method="post" action="http://admin.estaxi.app.estronger.cn/orders/Index/carpool_order_save">

                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">锁编号</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['lock_sn']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">锁名称</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['lock_name']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">电池电压(伏特)</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['gx']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">充电电压(伏特)</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['gy']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">当前电量（百分比）</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['battery']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">信号强度(dB)</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['gz']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">开锁次数</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['open_nums']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">更新时间</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['system_time']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">状态</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['lock_status']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-12 padding">
                            <div class="col-sm-12" style="height: 400px;box-sizing: content-box;">
                                <div id="container"></div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<link rel="stylesheet" href="http://cache.amap.com/lbs/static/main1119.css"/>
<script type="text/javascript"  src="http://webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d&plugin=AMap.Geocoder&callback=initMap"></script>
<script type="text/javascript" src="http://cache.amap.com/lbs/static/addToolbar.js"></script>
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
