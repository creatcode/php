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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo @$lang['t33'];?></a></li>
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
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t34'];?></label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['bicycle_sn']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t35'];?></label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['full_bicycle_sn']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t36'];?></label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['type']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t26'];?></label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['lock_sn']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t37'];?></label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['place']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t38'];?></label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['is_using']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-6">
                                    <label for="" class="col-sm-4 control-label"><?php echo @$lang['t39'];?></label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['add_time']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t40'];?></label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['fault']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t41'];?></label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['illegal_parking']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t42'];?></label>
                                    <div class="col-sm-8" style="margin-top: 7px;">
                                        <span><?php echo $data['low_battery']; ?></span>
                                    </div>
                                </div>
                                <div class="form-group col-sm-12">
                                    <label for="" class="col-sm-2 control-label"><?php echo @$lang['t43'];?></label>
                                    <div class="col-sm-8">
                                        <div  class="col-sm-12 img-thumbnail" style="height: 500px;">
                                            <div id="container"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div style="text-align:center">
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding:0 30px;height:40px;font-size:14px;line-height:40px"><?php echo @$lang['t32'];?></a>
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
    <script type="text/javascript"  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDykSoVc_Z96D_rLGPhQOf9XHYluROzceI"></script>
<script type="text/javascript">
    
    <?php if (isset($data['lng']) && isset($data['lat'])) { ?>
      

     var myCenter=new google.maps.LatLng('<?php echo $data['lat']; ?>','<?php echo $data['lng']; ?>');

    function initialize()
{
var mapProp = {
  center:myCenter,
  zoom:13,
  mapTypeId:google.maps.MapTypeId.ROADMAP
  };

var map=new google.maps.Map(document.getElementById("container"),mapProp);

var marker=new google.maps.Marker({
  position:myCenter,
  });

marker.setMap(map);
}

google.maps.event.addDomListener(window, 'load', initialize);
    <?php } ?>


</script>


<?php echo $footer;?>
