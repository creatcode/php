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
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t9'];?></label>
                                <div class="col-sm-8">
                                    <div class="input-group col-sm-7">
                                        <input type="text" name="region_name" value="<?php echo @$data['region_name']; ?>" class="form-control" />
                        
                                  
                                    <?php if (isset($error['region_name'])) { ?><div class="text-danger"><?php echo $error['region_name']; ?></div><?php } ?>
                                    </div>
                                </div>
                            </div>
                     
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t10'];?></label>
                                <div class="col-sm-8">
                                    <div class="input-group col-sm-7">
                                    <input type="text" name="region_city_ranking" value="<?php echo @$data['region_city_ranking']; ?>" class="form-control" disabled="disabled">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t11'];?></label>
                                <div class="col-sm-8">
                                    <div class="input-group col-sm-7">
                                    <input type="text" name="consumer_limit" value="<?php echo @$data['consumer_limit']; ?>" class="form-control">
                                    <?php if (isset($error['consumer_limit'])) { ?><div class="text-danger"><?php echo $error['consumer_limit']; ?></div><?php } ?>
                                    </div>
                                </div>
                            </div>
                           
                                <!-- <label class="col-sm-2 control-label">方&nbsp;&nbsp;&nbsp;&nbsp;案</label> -->
                                <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t12'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-7">
                                            <input type="text" name="deposit" value="<?php echo @$data['deposit']; ?>" class="form-control">
                                            <?php if (isset($error['deposit'])) { ?><div class="text-danger"><?php echo $error['deposit']; ?></div><?php } ?>
                                        </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t13'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-7">
                                            <input type="text" name="monthly_card_money" value="<?php echo @$data['monthly_card_money']; ?>" class="form-control">
                                            <?php if (isset($error['monthly_card_money'])) { ?><div class="text-danger"><?php echo $error['monthly_card_money']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t14'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-7">
                                            <input type="text" name="yearly_card_money" value="<?php echo @$data['yearly_card_money']; ?>" class="form-control">
                                            <?php if (isset($error['yearly_card_money'])) { ?><div class="text-danger"><?php echo $error['yearly_card_money']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                  <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t15'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-7">
                                            <input type="number" name="calculate_unit" value="<?php echo @$data['calculate_unit']; ?>" class="form-control" placeholder="<?php echo @$lang['t16'];?>" onkeyup="change_unit(this)">
                                            <?php if (isset($error['calculate_unit'])) { ?><div class="text-danger"><?php echo $error['calculate_unit']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t17'];?><span class="calculate_unit"><?php echo empty($data['calculate_unit'])?'N':$data['calculate_unit'];?></span><?php echo @$lang['t18'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-7">
                                            <input type="text" name="cards_first_half" value="<?php echo @$data['cards_first_half']; ?>" class="form-control">
                                            <?php if (isset($error['cards_first_half'])) { ?><div class="text-danger"><?php echo $error['cards_first_half']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t19'];?><span class="calculate_unit"><?php echo empty($data['calculate_unit'])?'N':$data['calculate_unit'];?></span><?php echo @$lang['t20'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-7">
                                            <input type="text" name="cards_afterwards_half" value="<?php echo @$data['cards_afterwards_half']; ?>" class="form-control">
                                            <?php if (isset($error['cards_afterwards_half'])) { ?><div class="text-danger"><?php echo $error['cards_afterwards_half']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t17'];?><span class="calculate_unit"><?php echo empty($data['calculate_unit'])?'N':$data['calculate_unit'];?></span><?php echo @$lang['t21'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-7">
                                            <input type="text" name="first_half" value="<?php echo @$data['first_half']; ?>" class="form-control">
                                            <?php if (isset($error['first_half'])) { ?><div class="text-danger"><?php echo $error['first_half']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t19'];?><span class="calculate_unit"><?php echo empty($data['calculate_unit'])?'N':$data['calculate_unit'];?></span><?php echo @$lang['t21'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-7">
                                            <input type="text" name="afterwards_half" value="<?php echo @$data['afterwards_half']; ?>" class="form-control">
                                            <?php if (isset($error['afterwards_half'])) { ?><div class="text-danger"><?php echo $error['afterwards_half']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
               
                                <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t22'];?></label>
                                <div class="col-sm-8">
                                    <div class="input-group col-sm-7">
                                        <span class="input-group-addon"><?php echo @$lang['t23'];?></span>
                                        <input type="text" name="free_start" value="<?php echo @$data['free_start']; ?>" class="form-control text-center date-range">
                                        <span class="input-group-addon" style="border-left: 0;border-right: 0;"><?php echo @$lang['t24'];?></span>
                                        <input type="text" name="free_end" value="<?php echo @$data['free_end']; ?>" class="form-control text-center date-range">
         
                                    </div>
                                    <?php if (isset($error['free_start'])) { ?><div class="text-danger"><?php echo $error['free_start']; ?></div><?php } ?>
                                    <?php if (isset($error['free_end'])) { ?><div class="text-danger"><?php echo $error['free_end']; ?></div><?php } ?>
                                </div>
                            </div>
                           
                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div  style="text-align:center">
                                        <textarea name="region_bounds" class="hidden"><?php echo $data['region_bounds']; ?></textarea>
                                        <textarea name="region_bounds_southwest_lng" class="hidden"><?php echo $data['region_bounds_southwest_lng']; ?></textarea>
                                        <textarea name="region_bounds_southwest_lat" class="hidden"><?php echo $data['region_bounds_southwest_lat']; ?></textarea>
                                        <textarea name="region_bounds_northeast_lng" class="hidden"><?php echo $data['region_bounds_northeast_lng']; ?></textarea>
                                        <textarea name="region_bounds_northeast_lat" class="hidden"><?php echo $data['region_bounds_northeast_lat']; ?></textarea>
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5"  style="margin-right: 40px;padding:0 30px;height:40px;font-size:14px;"><?php echo @$lang['t25'];?></button>
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding:0 30px;height:40px;font-size:14px;line-height:40px"><?php echo @$lang['t26'];?></a>
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

    $('.date-range').datetimepicker({
        pickDate: false,
        format: 'HH:mm'
    });
    function change_unit(t){
         var unit='N';
         if($(t).val().trim()){
             unit=$(t).val().trim();
         }
         $('.calculate_unit').html(unit);
    }
</script>
<script>
        $("input[name='region_name']").blur(function(){
            var cn=$("input[name='region_name']").val();
            $.get("https://maps.googleapis.com/maps/api/geocode/json?address="+cn+"&key=AIzaSyDunT2WYI4g1z--h5nm6yInWa0qlBgqR2Q",{},function(data){
                console.log(data);
            
                if(data.status=='OK'){
                        if(data.results[0].geometry.bounds){
                            $("textarea[name='region_bounds_northeast_lng']").text(data.results[0].geometry.bounds.northeast.lng);
                            $("textarea[name='region_bounds_northeast_lat']").text(data.results[0].geometry.bounds.northeast.lat);
                            $("textarea[name='region_bounds_southwest_lng']").text(data.results[0].geometry.bounds.southwest.lng);
                            $("textarea[name='region_bounds_southwest_lat']").text(data.results[0].geometry.bounds.southwest.lat);
                        }else{
                            alert('<?php echo @$lang['t27'];?>');
                            return false;
                        }
                    }else{
                        alert('<?php echo @$lang['t27'];?>');
                        return false;
                    }
             },'json');
        });
</script>
<?php echo $footer;?>