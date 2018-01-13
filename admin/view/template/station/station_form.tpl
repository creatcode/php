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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo $title; ?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <?php if (isset($success)) { ?>
                        <div class="alert bg-light-blue"><i class="fa fa-check-circle"></i>&nbsp;<?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t8'];?></label>
                                    <div class="col-sm-5">
                                        <input type="text" name="station_sn" value="<?php echo @$data['station_sn']; ?>" class="form-control" />
                                        <?php if (isset($error['station_sn'])) { ?><div class="text-danger"><?php echo $error['station_sn']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t24'];?></label>
                                    <div class="col-sm-5">
                                        <input type="text" name="station_name" value="<?php echo @$data['station_name']; ?>" class="form-control" />
                                        <?php if (isset($error['station_name'])) { ?><div class="text-danger"><?php echo $error['station_name']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t6'];?></label>
                                    <div class="col-sm-5">
                                        <select name="station_states" class="form-control">
                                            <?php foreach($station_states as $k => $v) { ?>
                                            <option value="<?php echo $k; ?>" <?php if ((string)$k == @$data['station_state']) { ?>selected<?php } ?>><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['station_states'])) { ?><div class="text-danger"><?php echo $error['station_states']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t25'];?></label>
                                    <div class="col-sm-5">
                                        <select name="power_state" class="form-control">
                                            <?php foreach($station_power_states as $k => $v) { ?>
                                            <option value="<?php echo $k; ?>" <?php if ((string)$k == @$data['power_state']) { ?>selected<?php } ?>><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['power_state'])) { ?><div class="text-danger"><?php echo $error['power_state']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t26'];?></label>
                                    <div class="col-sm-5">
                                        <select name="region_id" class="form-control" onchange="show_city(this)">
                                            <option value=""><?php echo @$lang['t27'];?></option>
                                            <?php foreach($regions as $key => $region) { ?>
                                            <option value="<?php echo $region['region_id']; ?>" <?php if ((string)$region['region_id'] == @$data['region_id']) { ?>selected<?php } ?>><?php echo $region['region_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['region_id'])) { ?><div class="text-danger"><?php echo $error['region_id']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t28'];?></label>
                                    <div class="col-sm-5">
                                        <select name="city_id" id="city_id" class="form-control">
                                            <option value=""><?php echo @$lang['t27'];?></option>
                                            
                                        </select>
                                        <?php if (isset($error['city_id'])) { ?><div class="text-danger"><?php echo $error['city_id']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t29'];?></label>
                                    <div class="col-sm-5">
                                        <input type="number" name="threshold_height" value="<?php echo @$data['threshold_height']; ?>" class="form-control" />
                                        <?php if (isset($error['threshold_height'])) { ?><div class="text-danger"><?php echo $error['threshold_height']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t30'];?></label>
                                    <div class="col-sm-5">
                                        <input type="number" name="threshold_low" value="<?php echo @$data['threshold_low']; ?>" class="form-control" />
                                        <?php if (isset($error['threshold_low'])) { ?><div class="text-danger"><?php echo $error['threshold_low']; ?></div><?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div  style="text-align:center">
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5" style="margin-right: 40px;padding:0 30px;height:40px;font-size:14px;"><?php echo @$lang['t31'];?></button>
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
<script>
    var region_data=new Array();
    <?php
        foreach($regions as $key=>$val){
    ?>
            region_data[<?php echo $val['region_id']?>]=new Array();
            <?php
                foreach($val['city'] as $key2=>$val2){
            ?>
                region_data[<?php echo $val['region_id']?>][<?php echo $val2['city_id']?>]="<?php echo $val2['city_name']?>";
            <?php
                }
            ?>
    <?php
        } 
    ?>
    function show_city(t){
        var region_id=$(t).val();
        var a='<option value=""><?php echo @$lang['t5'];?></option>';
        if(region_id){
            region_data[region_id].forEach(function (item,index,input) {
		a+="<option value="+index+">"+item+"</option>";
            });
            $("#city_id").html(a); 
        }
    }
    <?php
        if(@$data['station_id']){
    ?>
     function init_city(){
        var a='<option value=""><?php echo @$lang['t5'];?></option>';
        var city_id="<?php echo @$data['city_id']?>";
        var region_id="<?php echo @$data['region_id']?>";
            region_data[region_id].forEach(function (item,index,input) {
		a+="<option value="+index;
                if(index==city_id){
                    a+=" selected ";
                }
                a+=">"+item+"</option>";
            });
            console.log(a);
            $("#city_id").html(a); 
     }
    $(function(){
        init_city();
     });
     <?php
        }
     ?>
</script>
<?php echo $footer;?>
