<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>单车管理</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">单车详情</a></li>
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
                                    <label for="" class="col-sm-4 control-label">站点编码</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['station_sn']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">站点状态</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $station_states[$data['station_state']]; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">电池状态</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $station_power_states[$data['power_state']]; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">站点区域</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $region['region_name']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">站点城市</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $city['city_name']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">车桩数</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['total']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">空车位</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['total']-$data['used']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">故障车位</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['total']-$data['used']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">有车位</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['total']-$data['used']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">停车数</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['used']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">满车率</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['used']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label">添加时间</label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo date('Y-m-d H:i:s',$data['add_time']); ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label">当前位置</label>
                                    <div class="col-sm-8">
                                        <div  class="col-sm-12 img-thumbnail" style="height: 500px;">
                                            <div id="container"></div>
                                        </div>
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
<script>
jQuery.getScript('<?php echo HTTP_IMAGE;?>js/coordinate.js');
</script>
<link rel="stylesheet" href="http://cache.amap.com/lbs/static/main1119.css"/>
<script src="<?php echo HTTP_IMAGE;?>js/coordinate.js"></script>
<script type="text/javascript">
    <?php if (isset($data['lng']) && isset($data['lat'])) { ?>
        var lnglat = window.wgs84togcj02(parseFloat(<?php echo $data['lng']; ?>), parseFloat(<?php echo $data['lat']; ?>));
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
<script type="text/javascript"  src="http://webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d&plugin=AMap.Geocoder&callback=initMap"></script>

<?php echo $footer;?>
