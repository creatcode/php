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
        <div class="nav-tabs-custom">
            <!-- tab 标签 -->
            <ul class="nav nav-tabs">
                <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo $title; ?></a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active">
                    <?php if (isset($error['warning'])) { ?>
                    <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                    <?php } ?>
                    <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                        <div class="row">

                            
                            
                            <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t8'];?></label>
                                    <div class="col-sm-8">
                                        <div class="input-group col-sm-6">
                                        <select name="region_id" class="form-control" onchange="show_city(this)">
                                            
                                            <option value="" >--<?php echo @$lang['t9'];?>--</option>
                                            <?php foreach ($region_activity_options as $key => $option) { ?>
                                            <option value="<?php echo $key; ?>" <?php if ($key == $select_id) echo 'selected'; ?> ><?php echo $option; ?></option>
                                            <?php }?>
                                        </select>
                                        <?php if (isset($error['region_id'])) { ?><div class="text-danger"><?php echo $error['region_id']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                </div>
                                        
                            
                            <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t10'];?></label>
                                    <div class="col-sm-8">
                                        <div class="input-group col-sm-6">
                                        <select name="city_id" class="form-control" id="city_id">
                                            
                                            <option value="" ><?php echo @$lang['t11'];?></option>
                                            
                                        </select>
                                        <?php if (isset($error['city_id'])) { ?><div class="text-danger"><?php echo $error['city_id']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                </div>
                            
        
                            <div class="form-group">
                                <label for="price" class="col-sm-2 control-label"><?php echo @$lang['t12'];?></label>
                                <div class="col-sm-8">
                                    <div class="input-group col-sm-6">
                                    <input id="price" name="price" value="<?php echo $data['price'];?>" type="number" class="form-control">
                                    <?php if (isset($error['price'])) { ?><div class="text-danger"><?php echo $error['price']; ?></div><?php } ?>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t13'];?></label>
                                <div class="col-sm-8">
                                    <div class="input-group col-sm-6">
                                    <input type="text" name="effect_time" value="<?php echo $data['effect_time'];?>" class="form-control date-range">
                                    <?php if (isset($error['effect_time'])) { ?><div class="text-danger"><?php echo $error['effect_time']; ?></div><?php } ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-7">
                                <div style="text-align:center">
                                    <button type="submit" class="btn btn-sm btn-success margin-r-5" style="margin-right: 40px;padding:0 30px;height:40px;font-size:14px;"><?php echo @$lang['t14'];?></button>
                                    <a href="<?php echo $return_action;?>" class="btn btn-sm btn-default" style="padding:0 30px;height:40px;font-size:14px;line-height:40px"><?php echo @$lang['t15'];?></a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    $('.date-range').daterangepicker({
        locale: {
            format: 'YYYY-MM-DD',
            isAutoVal: false
        }
    });
</script>
<script>
    var region_data=new Array();
    <?php
        foreach($filter_regions as $key=>$val){
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
        var a='<option value="">--<?php echo @$lang['t11'];?>--</option>';
        if(region_id){
            if(region_data[region_id]){
                region_data[region_id].forEach(function (item,index,input) {
                    a+="<option value="+index+">"+item+"</option>";
                });  
            }
        }
        $("#city_id").html(a); 
    }
    function init_city(){
        var region_id="<?php echo @$data['adv_region_id'];?>";
        var city_id="<?php echo @$data['adv_city_id'];?>";
        var a='<option value="">--<?php echo @$lang['t11'];?>--</option>';
        if(region_id){
            if(region_data[region_id]){
                   region_data[region_id].forEach(function (item,index,input) {
                        a+="<option value="+index;
                        if(index==city_id){
                            a+=" selected ";
                        }
                        a+=">"+item+"</option>";
                    });
            }
            
        }
        $("#city_id").html(a); 
    }
    $(function(){
         init_city();
    });
</script>
<?php echo $footer; ?>