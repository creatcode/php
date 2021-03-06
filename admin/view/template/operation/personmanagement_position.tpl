<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>运维人员定位</span>
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
                     <li ><a href="<?php echo $index_url?>" data-toggle="tab">运维人员列表</a></li>
                   <li class="active"><a href="<?php echo $position_url;?>" data-toggle="tab">运维人员定位</a></li>
				   <li ><a href="<?php echo $record_url;?>" data-toggle="tab">运维记录明细</a></li>
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
                                
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label">运维人员位置</label>
                                    <div class="col-sm-8">
                                        <div  class="col-sm-12 img-thumbnail" style="height: 500px;">
                                            <div id="container"></div>
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

<link rel="stylesheet" href="http://cache.amap.com/lbs/static/main1119.css"/>
<script src="<?php echo HTTP_IMAGE;?>js/coordinate.js"></script>
<script type="text/javascript">

        var lnglat = window.wgs84togcj02(parseFloat(113.7251353), parseFloat(23.0061919));
 

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
<script type="text/javascript"  src="http://webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d&plugin=AMap.Geocoder&callback=initMap"></script>

<?php echo $footer;?>
