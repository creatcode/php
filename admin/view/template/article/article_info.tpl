<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>单车详情</span>
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
                            <label for="" class="col-sm-4 control-label">单车编码</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['bicycle_sn']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">单车类型</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['type']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">锁编号</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['lock_sn']; ?></span>
                            </div>
                        </div>
                        <div class="form-group col-sm-6">
                            <label for="" class="col-sm-4 control-label">状态</label>
                            <div class="col-sm-8">
                                <span><?php echo $data['is_using']; ?></span>
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
<script type="text/javascript"  src="http://webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d&plugin=AMap.Geocoder"></script>
<script src="<?php echo HTTP_CATALOG;?>js/coordinate.js"></script>
<script type="text/javascript">
    var lnglat = wgs84togcj02(parseFloat(<?php echo $data['lng']; ?>), parseFloat(<?php echo $data['lat']; ?>));
    var marker, map = new AMap.Map("container", {
        resizeEnable: true,
        center: lnglat,
        zoom: 13
    });
    marker = new AMap.Marker({
        position: lnglat,
        map: map
    });
</script>
<?php echo $footer;?>
