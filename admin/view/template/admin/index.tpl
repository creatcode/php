<?php echo $header; ?>
<link rel="stylesheet" href="<?php echo HTTP_IMAGE; ?>css/plugins/bootstrap-treeview.min.css">
<link rel="stylesheet" href="<?php echo HTTP_IMAGE; ?>css/admin/index.css">
<link rel="stylesheet" href="<?php echo HTTP_IMAGE; ?>css/admin/magnific-popup.css">
<link rel="stylesheet" href="//cache.amap.com/lbs/static/main1119.css"/>
<?php if(!$index_report){ ?>
<style>
    #map{
        bottom: 0;
    }
</style>
<?php } ?>
<div id="admin-main">
    <div id="dashboard" class="hidden-xs">
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3">
            <h1 class="dashboard-title"><i class="fa fa-dashboard fa-fw"></i> <?php echo @$lang['t2'];?><span> Dashboard</span></h1>
        </div>
        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9">
            <ul id="sparks" class="">
                <li class="sparks-info">
                    <h5> <?php echo @$lang['t3'];?> <span class="txt-color-blue"><?php echo $user_sum['total'] ?></span></h5>
                    <div class="sparkline txt-color-blue hidden-md hidden-sm hidden">
                        <?php echo implode(',', array_column($user_sum['list'], 'count'));?>
                    </div>
                </li>
                <li class="sparks-info">
                    <h5> <?php echo @$lang['t4'];?> <span class="txt-color-purple"><?php echo $bicycle_sum['total'] ?></span></h5>
                    <div class="sparkline txt-color-purple hidden-md hidden-sm hidden">
                        <?php echo implode(',', array_column($bicycle_sum['list'], 'count'));?>
                    </div>
                </li>
                <li class="sparks-info">
                    <h5> <?php echo @$lang['t5'];?> <span class="txt-color-purple"><?php echo $bicycle_sum['total'] ?></span></h5>
                    <div class="sparkline txt-color-purple hidden-md hidden-sm hidden">
                        <?php echo implode(',', array_column($bicycle_sum['list'], 'count'));?>
                    </div>
                </li>
                <li class="sparks-info">
                    <h5> <?php echo @$lang['t6'];?> <span class="txt-color-greenDark"><?php echo $used_sum['total'] ?></span></h5>
                    <div class="sparkline txt-color-greenDark hidden-md hidden-sm hidden">
                        <?php echo implode(',', array_column($used_sum['list'], 'count'));?>
                    </div>
                </li>
                <li class="sparks-info">
                    <h5> <?php echo @$lang['t7'];?> <span class="txt-color-orangeDark"><?php echo $fault_sum['total'] ?></span></h5>
                    <div class="sparkline txt-color-orangeDark hidden-md hidden-sm hidden">
                        <?php echo implode(',', array_column($fault_sum['list'], 'count'));?>
                    </div>
                </li>
            </ul>
        </div>
    </div>
    <div id="dashboard-content">
        <div id="map-n-statistics" class="no-padding">
            <div id="map"></div>
            <div class="heatmap-box clearfix" style="margin-right: 0; position:absolute;left: 80px;top: 50px; display: none;">
                <div class="pull-left"><input type="text" placeholder="<?php echo @$lang['t8'];?>" class="input-sm margin-r-5 heatmap_datetimepicker" style="width: 180px;" /></div>
                <div class="dropdown show-heatmap-select pull-left">
                    <button class="btn btn-primary btn-sm dropdown-toggle" type="button" id="dropdownOfShowHeatMaps" data-toggle="dropdown" style="font-size: 14px; vertical-align: baseline;">
                        <?php echo @$lang['t9'];?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu left" aria-labelledby="dropdownOfShowHeatMaps" style="right: 0px;left: auto;">
                        <li data-heatmap_type=""><a href="#"><?php echo @$lang['t9'];?></a></li>
                        <li role="separator" class="divider"></li>
                        <li data-heatmap_type="start_position"><a href="#"><?php echo @$lang['t10'];?></a></li>
                        <li data-heatmap_type="end_position"><a href="#"><?php echo @$lang['t11'];?></a></li>
                    </ul>
                </div>
            </div>
            <div id="map-toolbar">
                <!--<div class="dropdown place-select">
                    <button class="btn btn-default btn-xs dropdown-toggle" type="button" data-toggle="dropdown" style="font-size: 14px; vertical-align: baseline;">
                        西通电子
                        <span class="caret"></span>
                    </button>
                    <div class="dropdown-menu" id="treeview"></div>
                </div>-->
                <!--<select class="cooperator-selecter input-sm">
                    <option value="">所有合伙人</option>
                </select>-->
                <select class="region-selecter input-sm">
                    <option value=""><?php echo @$lang['t12'];?></option>
                    <?php foreach($region as $v){ ?>
                    <option value="<?php echo $v['region_name']?>"><?php echo $v['region_name']?></option>
                    <?php }?>
                </select>
                <span class="separator-after-region-selecter">|</span>
                <span class="br-after-region-selecter"><br/></span>
                <button type="button" class="btn btn-default btn-xs btn-no-border btn-refresh-marker" style="font-size: 14px; vertical-align: baseline;">
                    <i class="fa fa-refresh"></i> <span class="hidden-phone hidden-xs hidden-sm"><?php echo @$lang['t13'];?></span>
                </button>
                <button type="button" class="btn btn-default btn-xs btn-no-border btn-map-label" style="font-size: 14px; vertical-align: baseline;">
                    <i class="fa fa-map-pin"></i> <span class="hidden-phone hidden-xs hidden-sm"><?php echo @$lang['t14'];?></span>
                </button>
                <div class="dropdown" style="margin-right: 0;">
                    <button class="btn btn-default btn-xs btn-no-border dropdown-toggle" type="button" data-toggle="dropdown" style="font-size: 14px; vertical-align: baseline;">
                        <i class="fa fa-cog"></i> <?php echo @$lang['t15'];?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu tools-select" aria-labelledby="dropdownMenu1">
                        <li class="tool-ruler"><a href="#"><i class="fa fa-expand fa-fw"></i> <?php echo @$lang['t16'];?></a></li>
                        <li class="tool-share"><a href="#"><i class="fa fa-share-alt fa-fw"></i> <?php echo @$lang['t17'];?></a></li>
                    </ul>
                </div>
                <button type="button" class="btn btn-default btn-xs btn-no-border btn-map-maximize" style="font-size: 14px; vertical-align: baseline;">
                    <i class="fa fa-arrows-alt"></i> <span class="hidden-phone hidden-xs hidden-sm"><?php echo @$lang['t18'];?></span>
                </button>
                <span class="hidden-xs hidden-sm hidden-md">|</span>
                <div class="checkbox hidden-xs hidden-sm hidden-md" style="margin-left: 3px;">
                    <label>
                        <input type="checkbox" id="show-heatmap"> <span class=""><?php echo @$lang['t19'];?></span>
                    </label>
                </div>
                |
                <div class="checkbox" style="margin-left: 3px;">
                    <!--
                                        <label>
                                            <input type="checkbox"> 显示电子围栏
                                        </label>
                                    </div>
                                    <div class="checkbox">
                    -->
                    <label>
                        <input type="checkbox" id="show-bike-number"> <span class="hidden-phone hidden-xs hidden-sm hidden-md"><?php echo @$lang['t20'];?></span><?php echo @$lang['t21'];?>
                    </label>
                </div>
                |
                <div class="dropdown show-bike-select" style="margin-right: 0;">
                    <button class="btn btn-default btn-xs btn-no-border dropdown-toggle" type="button" id="dropdownOfShowBikes" data-toggle="dropdown" style="font-size: 14px; vertical-align: baseline;">
                        <?php echo @$lang['t22'];?>
                        <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownOfShowBikes">
                        <li class="active" data-bike_type=""><a href="#"><?php echo @$lang['t22'];?></a></li>
                        <li role="separator" class="divider"></li>
                        <li data-bike_type="low_battery"><a href="#"><?php echo @$lang['t23'];?></a></li>
                        <li data-bike_type="illegal_parking"><a href="#"><?php echo @$lang['t24'];?></a></li>
                        <li data-bike_type="fault"><a href="#"><?php echo @$lang['t25'];?></a></li>
                        <li data-bike_type="cant_finish"><a href="#"><i class="fa fa-angle-right"></i> <?php echo @$lang['t26'];?></a></li>
                        <li role="separator" class="divider"></li>
                        <li data-bike_type="offline"><a href="#"><?php echo @$lang['t27'];?></a></li>
                        <li data-bike_type="offline24"><a href="#"><?php echo @$lang['t28'];?></a></li>
                        <li role="separator" class="divider"></li>
                        <li data-bike_type="using"><a href="#"><?php echo @$lang['t29'];?></a></li>
                        <li role="separator" class="divider"></li>
                       <!-- <li data-bike_type="noUsedDays2"><a href="#">2天以上未使用</a></li>
                        <li data-bike_type="noUsedDays3"><a href="#">3天以上未使用</a></li>
                        <li data-bike_type="noUsedDays4"><a href="#">4天以上未使用</a></li>
                        <li data-bike_type="noUsedDays5"><a href="#">5天以上未使用</a></li>
                        <li data-bike_type="noUsedDays6"><a href="#">6天以上未使用</a></li>
                        <!--<li role="separator" class="divider"></li>
                        <li data-bike_type="bikeHide"><a href="#">隐藏的车辆</a></li>-->
                        <li ><a href="#"><?php echo @$lang['30'];?></a></li>
                        <li ><a href="#"><?php echo @$lang['t31'];?></a></li>
                    </ul>
                </div>
                <form method="post" action="<?php echo $export_action; ?>" style="font-size: 14px; vertical-align: baseline;display: inline-block;">
                    <input type="hidden" name="region_name" value="" />
                    <button class="btn btn-default btn-xs btn-no-border hidden-phone hidden-xs hidden-sm hidden-md" style="font-size: 14px; vertical-align: baseline;">
                        <i class="fa fa-file-excel-o"></i> <span class=""><?php echo @$lang['t32'];?></span>
                    </button>
                </form>
                <button type="button" id="btn-open-search" class="hidden btn btn-default btn-xs btn-no-border visible-xs-inline" style="font-size: 14px; vertical-align: baseline;">
                    <i class="fa fa-search"></i>
                </button>
            </div>
			<?php if($index_report){ ?>
            <div id="statistics">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <h1 class="dashboard-title"><i class="fa fa-cny fa-fw"></i> <?php echo @$lang['t33'];?><span> Financial Center</span></h1>
                    <ul>
                        <li>
                            <div class="statistics-block-title"><?php echo @$lang['t34'];?></div>
                            <div class="statistics-block tow-lines-block" style="line-height: 15px;">
                                <?php echo @$lang['t35'];?>：￥<?php echo $recharge_sum['orderToday']?$recharge_sum['orderToday']:'0.00' ?><br/>
                                <?php echo @$lang['t36'];?>：￥<?php echo $recharge_sum['orderTotal']?$recharge_sum['orderTotal']:'0.00' ?><br/>
                                <?php echo @$lang['t37'];?>：￥<?php echo $recharge_sum['rechargeToday']?$recharge_sum['rechargeToday']:'0.00' ?><br/>
                                <?php echo @$lang['t38'];?>：￥<?php echo $recharge_sum['rechargeTotal']?$recharge_sum['rechargeTotal']:'0.00' ?>
                            </div>
                        </li>
                        <li>
                            <div class="statistics-block-title"><?php echo @$lang['t39'];?></div>
                            <div class="statistics-block three-lines-block" style="line-height: 15px;">
                                <?php echo @$lang['t40'];?>：￥<?php echo $deposit_sum['rechargeToday']?$deposit_sum['rechargeToday']:'0.00' ?><br/>
                                <?php echo @$lang['t41'];?>：￥<?php echo $deposit_sum['rechargeTotal']?$deposit_sum['rechargeTotal']:'0.00' ?><br/>
                                <?php echo @$lang['t42'];?>：￥<?php echo $deposit_sum['refundToday']?$deposit_sum['refundToday']:'0.00' ?><br/>
                                <?php echo @$lang['t43'];?>：￥<?php echo $deposit_sum['refundTotal']?$deposit_sum['refundTotal']:'0.00' ?>
                            </div>
                        </li>
                        <li>
                            <div class="statistics-block-title"><?php echo @$lang['t44'];?></div>
                            <div class="statistics-block tow-lines-block" style="line-height: 15px;">
                                <?php echo @$lang['t45'];?>：<?php echo $coupon_sum['countUsedToday']?$coupon_sum['countUsedToday']:'0' ?><?php echo @$lang['t49'];?><br/>
                                <?php echo @$lang['t46'];?>：<?php echo $coupon_sum['countUsedTotal']?$coupon_sum['countUsedTotal']:'0' ?><?php echo @$lang['t49'];?><br/>
                                <?php echo @$lang['t47'];?>：<?php echo $coupon_sum['countAddToday']?$coupon_sum['countAddToday']:'0' ?><?php echo @$lang['t49'];?><br/>
                                <?php echo @$lang['t48'];?>：<?php echo $coupon_sum['countAddTotal']?$coupon_sum['countAddTotal']:'0' ?><?php echo @$lang['t49'];?>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
			<?php } ?>
        </div>
        <div id="bikes-bg"></div>
        <div id="bikes" class="no-padding">
            <form>
            <div class="dropdown show-bike-type-select">
                <button class="btn btn-default btn-xs btn-no-border dropdown-toggle from-control" type="button" data-toggle="dropdown">
                    <?php echo @$lang['t50'];?>
                    <span class="caret pull-right"></span>
                </button>
                <ul class="dropdown-menu">
                    <li>
                        <a href="#">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"> <?php echo @$lang['t51'];?>
                            </label>
                        </div>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"> <?php echo @$lang['t52'];?>
                            </label>
                        </div>
                        </a>
                    </li>
                    <li>
                        <a href="#">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox"> <?php echo @$lang['t53'];?>
                            </label>
                        </div>
                        </a>
                    </li>
                </ul>
            </div>
            </form>
            <!-- search form -->
            <div class="search-form">
                <div class="input-group">
                    <input type="text" name="query" id="search-bicycle-input" class="form-control" placeholder="<?php echo @$lang['t54'];?>">
                    <span class="input-group-btn">
                    <button name="search" id="search-bicycle-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                    </button>
                  </span>
                </div>
            </div>
            <!-- /.search form -->
            <ul class="bike-list" style="height: 100%;overflow: auto; box-sizing:border-box;border-bottom:100px solid transparent;">
            </ul>
        </div>
    </div>
</div>
<div id="user-modal" class="modal fade" tabindex="-1" role="dialog"></div><!-- /.modal -->
<script type="text/javascript">
    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/coordinate.js');
    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/plugins/bootstrap-treeview.min.js');
    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/plugins/jquery.sparkline.min.js');
    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/admin/sparkline.js');
    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/admin/magnific-popup.js');

	//jQuery.getScript('<?php echo HTTP_IMAGE;?>js/admin/index.js?v=<?php echo time();?>');
    jQuery.getScript('<?php echo HTTP_IMAGE;?>js/admin/index_1.js?v=<?php echo time();?>');
	jQuery.getScript('https://maps.googleapis.com/maps/api/js?key=AIzaSyDykSoVc_Z96D_rLGPhQOf9XHYluROzceI&callback=initMap');
    window.imageUrlBase = '<?php echo HTTP_IMAGE;?>';

</script>
<!--<script type="text/javascript"  src="//webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d&plugin=AMap.Geocoder,AMap.Heatmap,&callback=initMap"></script>-->

<?php echo $footer;?>
