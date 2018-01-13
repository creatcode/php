<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo @$lang['t2'];?></span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo @$lang['t15'];?></a></li>
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
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t16'];?></label>
                                    <div class="col-sm-10">
                                        <?php if ($data['avatar']) { ?><span class="img-thumbnail"><img src="<?php echo $data['avatar']; ?>" style="max-width:100px;max-height:100px;" /></span><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t17'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['mobile']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t18'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['nickname']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t19'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['deposit']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t20'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['available_deposit']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t21'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['credit_point']; ?></h5>
                                    </div>
                                </div>

             

                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t22'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $available_states[$data['available_state']]; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t23'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['login_time']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t24'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['ip']; ?></h5>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t25'];?></label>
                                    <div class="col-sm-8">
                                        <h5><?php echo $data['add_time']; ?></h5>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="text-center">
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding: 0 30px;height: 40px;font-size: 14px;line-height: 40px;"><?php echo @$lang['t26'];?></a>
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
