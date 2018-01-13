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
                            <input type="hidden" name="adv_id" value="<?php echo $data['adv_id']; ?>"  />
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t23'];?></label>
                                        <div class="col-sm-5">
                                            <select name="adv_type" class="form-control">
                                                <?php if (!empty($adv_types)) { ?>
                                                <?php foreach($adv_types as $k => $v) { ?>
                                                <option value="<?php echo $k; ?>" <?php echo $k == $data['adv_type'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                                <?php } ?>
                                                <?php } ?>
                                            </select>
                                            <?php if (isset($error['adv_type'])) { ?><div class="text-danger"><?php echo $error['adv_type']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group adv_image_5x">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t24'];?></label>
                                        <div class="col-sm-5">
                                            <button type="button" class="btn btn-primary btn-sm button-upload" data-ftype="upload" data-tag="image" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                                <i class="fa fa-upload"></i>
                                                <div class="inline"><?php echo @$lang['t25'];?></div>
                                                <input type="hidden" name="adv_image_5x" value="<?php echo $data['adv_image_5x']; ?>" class="filepath">
                                                <span class="filepath"><?php echo $data['adv_image_5x']; ?></span>
                                            </button>
                                            <img src="<?php echo $data['all_adv_image_5x']; ?>" class="filepath" height="35" >
                                            <small></small>
                                            <?php if (isset($error['adv_image_5x'])) { ?>
                                            <div class="text-danger"> <?php echo $error['adv_image_5x']; ?> </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group adv_image_4x">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t24'];?></label>
                                        <div class="col-sm-5">
                                            <button type="button" class="btn btn-primary btn-sm button-upload" data-ftype="upload" data-tag="image" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                                <i class="fa fa-upload"></i>
                                                <div class="inline"><?php echo @$lang['t25'];?></div>
                                                <input type="hidden" name="adv_image_4x" value="<?php echo $data['adv_image_4x']; ?>" class="filepath">
                                                <span class="filepath"><?php echo $data['adv_image_4x']; ?></span>
                                            </button>
                                            <img src="<?php echo $data['all_adv_image_4x']; ?>" class="filepath" height="35" >
                                            <small></small>
                                            <?php if (isset($error['adv_image_4x'])) { ?>
                                            <div class="text-danger"> <?php echo $error['adv_image_4x']; ?> </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group adv_image_3x">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t24'];?></label>
                                        <div class="col-sm-5">
                                            <button type="button" class="btn btn-primary btn-sm button-upload" data-useoriname="1" data-ftype="upload" data-tag="image" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                                <i class="fa fa-upload"></i>
                                                <div class="inline"><?php echo @$lang['t25'];?></div>
                                                <input type="hidden" name="adv_image_3x" value="<?php echo $data['adv_image_3x']; ?>" class="filepath">
                                                <span class="filepath"><?php echo $data['adv_image_3x']; ?></span>
                                            </button>
                                            <img src="<?php echo $data['all_adv_image_3x']; ?>" class="filepath" height="35" >
                                            <small></small>
                                            <?php if (isset($error['adv_image_3x'])) { ?>
                                            <div class="text-danger"> <?php echo $error['adv_image_3x']; ?> </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group adv_image_2x">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t24'];?></label>
                                        <div class="col-sm-5">
                                            <button type="button" class="btn btn-primary btn-sm button-upload" data-useoriname="1" data-ftype="upload" data-tag="image" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                                <i class="fa fa-upload"></i>
                                                <div class="inline"><?php echo @$lang['t25'];?></div>
                                                <input type="hidden" name="adv_image_2x" value="<?php echo $data['adv_image_2x']; ?>" class="filepath">
                                                <span class="filepath"><?php echo $data['adv_image_2x']; ?></span>
                                            </button>
                                            <img src="<?php echo $data['all_adv_image_2x']; ?>" class="filepath" height="35" >
                                            <small></small>
                                            <?php if (isset($error['adv_image_2x'])) { ?>
                                            <div class="text-danger"> <?php echo $error['adv_image_2x']; ?> </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group adv_image_1x">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t24'];?></label>
                                        <div class="col-sm-5">
                                            <button type="button" class="btn btn-primary btn-sm button-upload" data-useoriname="1" data-ftype="upload" data-tag="image" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                                <i class="fa fa-upload"></i>
                                                <div class="inline"><?php echo @$lang['t25'];?></div>
                                                <input type="hidden" name="adv_image_1x" value="<?php echo $data['adv_image_1x']; ?>" class="filepath">
                                                <span class="filepath"><?php echo $data['adv_image_1x']; ?></span>
                                            </button>
                                            <img src="<?php echo $data['all_adv_image_1x']; ?>" class="filepath" height="35" >
                                            <small></small>
                                            <?php if (isset($error['adv_image_1x'])) { ?>
                                            <div class="text-danger"> <?php echo $error['adv_image_1x']; ?> </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t26'];?></label>
                                        <div class="col-sm-5">

                                            <input type="text" name="adv_link" value="<?php echo $data['adv_link']; ?>" class="form-control" />
                                            <?php if (isset($error['adv_link'])) { ?>
                                            <div class="text-danger"> <?php echo $error['adv_link']; ?> </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t27'];?></label>
                                        <div class="col-sm-5">

                                            <input type="text" name="ios_link" value="<?php echo $data['ios_link']; ?>" class="form-control" />
                                            <?php if (isset($error['ios_link'])) { ?>
                                            <div class="text-danger"> <?php echo $error['ios_link']; ?> </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t28'];?></label>
                                        <div class="col-sm-5">
                                            <input type="text" name="adv_effect_time" value="<?php echo $data['adv_effect_time']; ?>" class="form-control date-range">
                                            <?php if (isset($error['adv_effect_time'])) { ?><div class="text-danger"><?php echo $error['adv_effect_time']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t29'];?></label>
                                        <div class="col-sm-5">
                                            <input type="text" name="adv_start_time" value="<?php echo $data['adv_start_time']; ?>" class="form-control date-range">
                                            <?php if (isset($error['adv_start_time'])) { ?><div class="text-danger"><?php echo $error['adv_start_time']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t30'];?></label>
                                        <div class="col-sm-5">
                                            <input type="text" name="adv_add_memo" value="<?php echo $data['adv_add_memo']; ?>" class="form-control" />
                                            <?php if (isset($error['adv_add_memo'])) { ?>
                                            <div class="text-danger"><?php echo $error['adv_add_memo']; ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t31'];?></label>
                                        <div class="col-sm-5">
                                            <input type="text" name="adv_max_version_android" value="<?php echo $data['adv_max_version_android']; ?>" class="form-control" />
                                            <?php if (isset($error['adv_max_version_android'])) { ?>
                                            <div class="text-danger"><?php echo $error['adv_max_version_android']; ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t32'];?></label>
                                        <div class="col-sm-5">
                                            <input type="text" name="adv_max_version_ios" value="<?php echo $data['adv_max_version_ios']; ?>" class="form-control" />
                                            <?php if (isset($error['adv_max_version_ios'])) { ?>
                                            <div class="text-danger"><?php echo $error['adv_max_version_ios']; ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t33'];?></label>
                                        <div class="col-sm-5">
                                            <input type="text" name="adv_sort" value="<?php echo $data['adv_sort']; ?>" class="form-control" />
                                            <?php if (isset($error['adv_sort'])) { ?>
                                            <div class="text-danger"><?php echo $error['adv_sort']; ?></div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t34'];?></label>
                                        <div class="col-sm-5">
                                            <select name="adv_region_id" class="form-control" onchange="show_city(this)">
                                                <option value="0"><?php echo @$lang['t35'];?></option>
                                                <?php if (!empty($region_list)) { ?>
                                                <?php foreach($region_list as $v) { ?>
                                                <option value="<?php echo $v['region_id']; ?>" <?php echo $v['region_id'] == $data['adv_region_id'] ? 'selected' : ''; ?>><?php echo $v['region_name']; ?></option>
                                                <?php } ?>
                                                <?php } ?>
                                                <!--<option value="-99999" <?php echo '-99999' == $data['adv_region_id'] ? 'selected' : ''; ?> >未开通区域</option>-->
                                            </select>
                                            <?php if (isset($error['adv_region_id'])) { ?><div class="text-danger"><?php echo $error['adv_region_id']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t36'];?></label>
                                        <div class="col-sm-5">
                                            <select name="adv_city_id" class="form-control" id="city_id">
                                               
                                            </select>
                                            <?php if (isset($error['adv_city_id'])) { ?><div class="text-danger"><?php echo $error['adv_city_id']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t37'];?></label>
                                        <div class="col-sm-5">
                                            <input type="text" name="msg_title" value="<?php echo $data['msg_title']; ?>" class="form-control">
                                            <?php if (isset($error['msg_title'])) { ?><div class="text-danger"><?php echo $error['msg_title']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t38'];?></label>
                                        <div class="col-sm-5">
                                            <button type="button" class="img-thumbnail button-upload" style="outline: none;" data-tag="logo" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                                <img src="<?php echo $data['msg_image_url']; ?>" alt="<?php echo @$lang['t38'];?>" style="max-width: 100px; max-height: 100px;" class="imageurl">
                                                <input type="hidden" name="msg_image" value="<?php echo $data['msg_image']; ?>" placeholder="<?php echo @$lang['t38'];?>" class="filepath">
                                            </button>
                                            <?php if (isset($error['msg_image'])) { ?><div class="text-danger"><?php echo $error['msg_image']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"><?php echo @$lang['t39'];?></label>
                                        <div class="col-sm-5">
                                            <textarea name="msg_abstract" class="form-control" rows="3"><?php echo $data['msg_abstract']; ?></textarea>
                                            <?php if (isset($error['msg_abstract'])) { ?><div class="text-danger"><?php echo $error['msg_abstract']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-12">
                                        <div style="text-align:center">
                                            <button type="submit" class="btn btn-sm btn-success margin-r-5"   style="margin-right: 40px;padding:0 30px;height:40px;font-size:14px;"><?php echo @$lang['t40'];?></button>
                                            <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding:0 30px;height:40px;font-size:14px;line-height:40px"><?php echo @$lang['t41'];?></a>
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
<link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.css" />
<script type="text/javascript" src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('input.bootstrap-switch').bootstrapSwitch();
    });
$('.date-range').daterangepicker({
    locale:{
        format: 'YYYY-MM-DD',
        isAutoVal:false,
    }
});
$('[name="adv_type"]').change(function() {
    var adv_type = $(this).val();
    if (adv_type == 0) {             // 常规广告
        $(".adv_image_3x small").text('<?php echo @$lang['t43'];?>');
        $(".adv_image_2x small").text('<?php echo @$lang['t44'];?>');
        $(".adv_image_1x small").text('<?php echo @$lang['t45'];?>');
        $(".adv_image_4x").hide();
        $(".adv_image_5x").hide();
    } else if (adv_type == 1) {      // 启动页广告
        $(".adv_image_5x small").text('<?php echo @$lang['t46'];?>');
        $(".adv_image_4x small").text('<?php echo @$lang['t47'];?>');
        $(".adv_image_3x small").text('<?php echo @$lang['t48'];?>');
        $(".adv_image_2x small").text('<?php echo @$lang['t49'];?>');
        $(".adv_image_1x small").text('<?php echo @$lang['t50'];?>');
        $(".adv_image_4x").show();
        $(".adv_image_5x").show();
    }
});
$('[name="adv_type"]').trigger('change');
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
        var a='<option value="">--<?php echo @$lang['t42'];?>--</option>';
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
        var a='<option value="">--<?php echo @$lang['t42'];?>--</option>';
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
<?php echo $footer;?>