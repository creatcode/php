<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>单车分布图<small>单车分布图</small></h1>
    <ol class="breadcrumb">
        <li><i class="fa fa-dashboard"></i>首页</li>
        <li>单车分布图</li>
    </ol>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div id="container" style="height: 800px;"></div>
        </div>
    </div>
</section>
<link rel="stylesheet" href="//cache.amap.com/lbs/static/main1119.css"/>
<script type="text/javascript"  src="//webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d></script>
<script type="text/javascript" src="//cache.amap.com/lbs/static/addToolbar.js"></script>
<script src="<?php echo HTTP_CATALOG;?>js/coordinate.js"></script>

<script type="text/javascript">
    var list = <?php echo $list; ?>;
    var markers = new Array;
    var map = new AMap.Map("container", {
        resizeEnable: true,
        zoom: 13
    });

    if (typeof list != "undefined" && list != null) {
        var marker;
        for (i in list) {
            marker = new AMap.Marker({
                position: wgs84togcj02(parseFloat(list[i]['lng']), parseFloat(list[i]['lat'])),
                map: map
            });
            markers.push(marker);
        }
    }
    map.setFitView();
</script>
<?php echo $footer;?>
