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
                                    <div class="col-sm-8">
                                        <div class="input-group col-sm-6">
                                        <input type="text" name="city_name" value="<?php echo isset($data['city_name'])?$data['city_name']:''; ?>" class="form-control" />
                                        <?php if (isset($error['city_name'])) { ?><div class="text-danger"><?php echo $error['city_name']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t9'];?></label>
                                    <div class="col-sm-8">
                                        <div class="input-group col-sm-6">
                                            <select <?php if(empty($data['city_id'])){ ?>name="region_id"<?php } ?> class="form-control" onchange="change_init(this)" <?php if(!empty($data['city_id'])){echo 'disabled="disabled"';}?>>
                                            
                                            <option value="" >--<?php echo @$lang['t10'];?>--</option>
                                            <?php foreach($regions as $v) { ?>
                                            <option value="<?php echo $v['region_id']; ?>" <?php if ((string)$v['region_id'] == @$data['region_id']) { ?>selected<?php } ?>><?php echo $v['region_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if(isset($error['region_id'])){ ?><div class="text-danger"><?php echo $error['region_id']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if(!empty($data['city_id'])){ ?><input type="hidden" name="region_id" value="<?php echo @$data['region_id']; ?>" /><?php } ?>
                                <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t11'];?></label>
                                <div class="col-sm-8">
                                    <div class="input-group col-sm-6">
                                    <input type="text" name="consumer_limit" value="<?php echo @$data['consumer_limit']; ?>" class="form-control">
                                    <?php if (isset($error['consumer_limit'])) { ?><div class="text-danger"><?php echo $error['consumer_limit']; ?></div><?php } ?>
                                    </div>
                                </div>
                            </div>
                           
                                <!-- <label class="col-sm-2 control-label">方&nbsp;&nbsp;&nbsp;&nbsp;案</label> -->
                                <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t12'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-6">
                                            <input type="text" name="deposit" value="<?php echo @$data['deposit']; ?>" class="form-control">
                                            <?php if (isset($error['deposit'])) { ?><div class="text-danger"><?php echo $error['deposit']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t13'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-6">
                                            <input type="text" name="monthly_card_money" value="<?php echo @$data['monthly_card_money']; ?>" class="form-control">
                                            <?php if (isset($error['monthly_card_money'])) { ?><div class="text-danger"><?php echo $error['monthly_card_money']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t14'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-6">
                                            <input type="text" name="yearly_card_money" value="<?php echo @$data['yearly_card_money']; ?>" class="form-control">
                                            <?php if (isset($error['yearly_card_money'])) { ?><div class="text-danger"><?php echo $error['yearly_card_money']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t15'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-6">
                                            <input type="number" name="calculate_unit" value="<?php echo @$data['calculate_unit']; ?>" class="form-control" placeholder="<?php echo @$lang['t16'];?>" onkeyup="change_unit(this)">
                                            <?php if (isset($error['calculate_unit'])) { ?><div class="text-danger"><?php echo $error['calculate_unit']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t17'];?><span class="calculate_unit"><?php echo empty($data['calculate_unit'])?'N':$data['calculate_unit'];?></span><?php echo @$lang['t18'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-6">
                                            <input type="text" name="cards_first_half" value="<?php echo @$data['cards_first_half']; ?>" class="form-control">
                                            <?php if (isset($error['cards_first_half'])) { ?><div class="text-danger"><?php echo $error['cards_first_half']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t19'];?><span class="calculate_unit"><?php echo empty($data['calculate_unit'])?'N':$data['calculate_unit'];?></span><?php echo @$lang['t18'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-6">
                                            <input type="text" name="cards_afterwards_half" value="<?php echo @$data['cards_afterwards_half']; ?>" class="form-control">
                                            <?php if (isset($error['cards_afterwards_half'])) { ?><div class="text-danger"><?php echo $error['cards_afterwards_half']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t17'];?><span class="calculate_unit"><?php echo empty($data['calculate_unit'])?'N':$data['calculate_unit'];?></span><?php echo @$lang['t20'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-6">
                                            <input type="text" name="first_half" value="<?php echo @$data['first_half']; ?>" class="form-control">
                                            <?php if (isset($error['first_half'])) { ?><div class="text-danger"><?php echo $error['first_half']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t19'];?><span class="calculate_unit"><?php echo empty($data['calculate_unit'])?'N':$data['calculate_unit'];?></span><?php echo @$lang['t20'];?></label>
                                        <div class="col-sm-8">
                                            <div class="input-group col-sm-6">
                                            <input type="text" name="afterwards_half" value="<?php echo @$data['afterwards_half']; ?>" class="form-control">
                                            <?php if (isset($error['afterwards_half'])) { ?><div class="text-danger"><?php echo $error['afterwards_half']; ?></div><?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                
          
                               <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t21'];?></label>
                                <div class="col-sm-8">
                                    <div class="input-group col-sm-6">
                                        <span class="input-group-addon"><?php echo @$lang['t22'];?></span>
                                        <input type="text" name="free_start" value="<?php echo @$data['free_start']; ?>" class="form-control text-center date-range">
                                        <span class="input-group-addon" style="border-left: 0;border-right: 0;"><?php echo @$lang['t23'];?></span>
                                        <input type="text" name="free_end" value="<?php echo @$data['free_end']; ?>" class="form-control text-center date-range">
         
                                    </div>
                                    <?php if (isset($error['free_start'])) { ?><div class="text-danger"><?php echo $error['free_start']; ?></div><?php } ?>
                                    <?php if (isset($error['free_end'])) { ?><div class="text-danger"><?php echo $error['free_end']; ?></div><?php } ?>
                                </div>
                            </div>

                                <?php if(isset($data['city_id'])) { ?>
                                <input type="hidden" value="<?php echo $data['city_id'] ?>" name="city_id" />
                                <?php } ?>

                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div  style="text-align:center">
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5" style="margin-right: 40px;padding:0 30px;height:40px;font-size:14px;"><?php echo @$lang['t24'];?></button>
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding:0 30px;height:40px;font-size:14px;line-height:40px"><?php echo @$lang['t25'];?></a>
                                    </div>
                                </div>
                            </div>
                                            <input type="hidden" name="city_bounds_northeast_lng" value="<?php echo @$data['city_bounds_northeast_lng']; ?>">
                                            <input type="hidden" name="city_bounds_northeast_lat" value="<?php echo @$data['city_bounds_northeast_lat']; ?>">
                                            <input type="hidden" name="city_bounds_southwest_lng" value="<?php echo @$data['city_bounds_southwest_lng']; ?>">
                                            <input type="hidden" name="city_bounds_southwest_lat" value="<?php echo @$data['city_bounds_southwest_lat']; ?>">
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
     var region=new Array();
    <?php
        foreach($regions as $key=>$val){
    ?>
        region[<?php echo $val['region_id']?>]=new Array();
    <?php
            foreach($val as $key2=>$val2){
    ?>
        region[<?php echo $val['region_id']?>]['<?php echo $key2 ?>']='<?php echo $val2?>';
    <?php
            }
    ?>
    <?php
        }
    ?>
    function change_init(t){
        var region_id=0;
        if($(t).val()){
            region_id=$(t).val();
        }
        if(region_id>0){
        console.log(region);
            $('input[name="consumer_limit"]').val(region[region_id]['consumer_limit']);
            $('input[name="deposit"]').val(region[region_id]['deposit']);
            $('input[name="monthly_card_money"]').val(region[region_id]['monthly_card_money']);
            $('input[name="yearly_card_money"]').val(region[region_id]['yearly_card_money']);
            $('input[name="cards_first_half"]').val(region[region_id]['cards_first_half']);
            $('input[name="cards_afterwards_half"]').val(region[region_id]['cards_afterwards_half']);
            $('input[name="first_half"]').val(region[region_id]['first_half']);
            $('input[name="afterwards_half"]').val(region[region_id]['afterwards_half']);
            $('input[name="calculate_unit"]').val(region[region_id]['calculate_unit']);
            $('input[name="free_start"]').val(region[region_id]['free_start']);
            $('input[name="free_end"]').val(region[region_id]['free_end']);
            $('.calculate_unit').html(region[region_id]['calculate_unit']);
        }
    }
</script>
<script>
        $("input[name='city_name']").blur(function(){
            var cn=$("input[name='city_name']").val();
            $.get("https://maps.googleapis.com/maps/api/geocode/json?address="+cn+"&key=AIzaSyDunT2WYI4g1z--h5nm6yInWa0qlBgqR2Q",{},function(data){
                console.log(data);
            
                if(data.status=='OK'){
                        if(data.results[0].geometry.bounds){
                            $("input[name='city_bounds_northeast_lng']").val(data.results[0].geometry.bounds.northeast.lng);
                            $("input[name='city_bounds_northeast_lat']").val(data.results[0].geometry.bounds.northeast.lat);
                            $("input[name='city_bounds_southwest_lng']").val(data.results[0].geometry.bounds.southwest.lng);
                            $("input[name='city_bounds_southwest_lat']").val(data.results[0].geometry.bounds.southwest.lat);
                        }else{
                            alert('<?php echo @$lang['t27'];?>');
                            return false;
                        }
                    }else{
                        alert('<?php echo @$lang['t26'];?>');
                        return false;
                    }
             },'json');
        });
</script>
<?php echo $footer;?>
