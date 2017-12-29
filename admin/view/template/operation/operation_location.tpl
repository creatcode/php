<?php echo $header; ?>
<link rel="stylesheet" href="<?php echo HTTP_IMAGE; ?>css/plugins/bootstrap-treeview.min.css">
<link rel="stylesheet" href="<?php echo HTTP_IMAGE; ?>css/admin/index.css">
<link rel="stylesheet" href="<?php echo HTTP_IMAGE; ?>css/admin/magnific-popup.css">
<link rel="stylesheet" href="//cache.amap.com/lbs/static/main1119.css"/>

<style>
    #map{
        bottom: 0;
    }
    .bike-info-tabs > li{
        width:33.3333%;
    }
</style>
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>运维人员管理</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
</section>

<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <!-- tab 标签 -->
                <ul class="nav nav-tabs">
                    <li class="active"><a href="javascript:;" data-toggle="tab">运维人员定位</a></li>
                    <li><a href="<?php echo $repair_url?>" data-toggle="tab">运维记录统计列表</a></li>
                    <li><a href="<?php echo $repair_detail_url?>" data-toggle="tab">运维记录明细列表</a></li>
                    <li><a href="<?php echo $area_url?>" data-toggle="tab">运维区域</a></li>
                </ul>
                <div class="tab-content" style="height: 800px;padding: 0px">
                    <div id="admin-main">
                        <div id="dashboard-content" style="top:0px;">
                        <div id="map-n-statistics" class="no-padding">
                            <div id="map"></div>
                            <button type="button" class="btn btn-default btn-xs btn-no-border btn-refresh-marker" style="font-size: 14px; vertical-align: baseline;margin-left: 5px;margin-top: 5px;">
                                <i class="fa fa-refresh"></i> <span class="hidden-phone hidden-xs hidden-sm">刷新</span>
                            </button>
                        </div>
                        <div id="bikes-bg"></div>
                        <div id="bikes" class="no-padding">
                            <form>
                                <div class="dropdown show-bike-type-select">
                                    <button class="btn btn-default btn-xs btn-no-border dropdown-toggle from-control" type="button" data-toggle="dropdown">
                                        全部
                                        <span class="caret pull-right"></span>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li data-cooperator-id='0' data-cooperator-name='东莞总平台' ><a href="#">东莞总平台</a></li>
                                        <?php foreach($cooperatorList as $cooperator){ ?>
                                        <li data-cooperator-id='<?php echo $cooperator["cooperator_id"]?>' data-cooperator-name='<?php echo $cooperator["cooperator_name"]?>' ><a href="#"><?php echo $cooperator['cooperator_name']?></a></li>
                                        <?php }?>
                                    </ul>
                                </div>
                            </form>
                            <ul class="bike-list" style="height: 100%;overflow: auto; box-sizing:border-box;border-bottom:100px solid transparent;">
                            </ul>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<script type="text/javascript">
//    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/coordinate.js');
//    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/plugins/bootstrap-treeview.min.js');
//    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/plugins/jquery.sparkline.min.js');
//    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/admin/sparkline.js');
//    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/admin/magnific-popup.js');
//    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/admin/operation.js');
//jQuery.getScript('http://webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d&plugin=AMap.Geocoder,AMap.Heatmap,&callback=initMap');
//jQuery.getScript('http://webapi.amap.com/ui/1.0/main.js');
    window.imageUrlBase = '<?php echo HTTP_IMAGE;?>';
</script>
<script src="<?php echo HTTP_IMAGE;?>js/coordinate.js"></script>
<script src="<?php echo HTTP_IMAGE;?>js/admin/sparkline.js"></script>
<script src="<?php echo HTTP_IMAGE;?>js/admin/magnific-popup.js"></script>
<script src="<?php echo HTTP_IMAGE;?>js/admin/operation.js"></script>
<script type="text/javascript"  src="http://webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d&plugin=AMap.Geocoder,AMap.Heatmap,&callback=initMap"></script>
<script type="text/javascript" src="//webapi.amap.com/ui/1.0/main.js"></script>
<?php echo $footer;?>
