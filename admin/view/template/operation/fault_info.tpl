<?php

echo $header; ?>
<!-- Content Header (Page header) -->
<style>
    .box {top:0;margin-bottom:5px}
    .box-header .box-tools {top:3px}
    .box-body{padding:0}
    .box-body .box-header {padding: 5px}
    .form-group {margin-bottom: 5px;}
</style>
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo @$lang['t20'];?></span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>', this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
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
                    <li class="active"><a href="javascirpt:;"><?php echo @$lang['t20'];?></a></li>
                    <li><a href="<?php echo $history_action; ?>"><?php echo @$lang['t21'];?></a></li>
                </ul>
                <div class="tab-content" style="    padding-bottom: 45px;">

                    <!-- 车辆信息 -->
                    <div class="box box-widget">
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo @$lang['t22'];?> <small><?php echo @$lang['t23'];?><?php echo $bicycle['bicycle_sn'];?></small></h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="box-body">
                            <?php if (isset($error['warning'])) { ?>
                            <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                            <?php } ?>
                            <form class="form-horizontal" method="post">
                                <div class="row">
                                    <div class="form-group col-sm-6">
                                        <label for="" class="col-sm-4 control-label"><?php echo @$lang['t23'];?></label>
                                        <div class="col-sm-8" style="padding-top: 7px;">
                                            <span><?php echo $bicycle['bicycle_sn']; ?></span>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="" class="col-sm-4 control-label"><?php echo @$lang['t24'];?></label>
                                        <div class="col-sm-8" style="padding-top: 7px;">
                                            <span><?php echo $bicycle['lock_sn']; ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="form-group col-sm-6">
                                        <label for="" class="col-sm-4 control-label"><?php echo @$lang['t25'];?></label>
                                        <div class="col-sm-8" style="padding-top: 7px;">
                                            <span><?php echo $region['region_name']; ?></span>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-6">
                                        <label for="" class="col-sm-4 control-label"><?php echo @$lang['t26'];?></label>
                                        <div class="col-sm-8" style="padding-top: 7px;">
                                            <span>城市名</span>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="" class="col-sm-2 control-label"><?php echo @$lang['t27'];?></label>
                                        <div class="col-sm-8">
                                            <div  class="col-sm-12 img-thumbnail" style="height: 350px;">
                                                <div id="container"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12">
                                        <label for="" class="col-sm-2 control-label"><?php echo @$lang['t28'];?></label>
                                        <div class="col-sm-8">
                                            <span><input ondblclick="this.select()" class="col-sm-8" type="text" readonly value="" id="formattedAddress" /></span>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 故障列表 -->
                    <div class="box box-widget" >
                        <div class="box-header with-border">
                            <h3 class="box-title"><?php echo @$lang['t29'];?> (<?php echo !empty($faultList) ? count($faultList) : 0; ?>)</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="box-body" >
                            <?php if (!empty($faultList)): ?>
                            <?php foreach ($faultList AS $k => $fault): ?>
                            <form class="form_fault" method="post" action="<?php echo $handling_action; ?>">
                                <div class="form-group col-sm-12">
                                    <div class="form-group col-sm-2"></div>
                                    <div class="form-group col-sm-8">
                                        <div class="box-header bg-blue-gradient">
                                            <h3 class="box-title">#<?php echo $fault['fault_id']; ?> <?php echo $fault['fault_type']; ?></h3>
                                            <div class="box-tools pull-right">
                                                <button type="button" href="#<?php echo 'collapse_fault'.$k; ?>" class="btn btn-box-tool" data-toggle="collapse"><i class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                        <div class="panel-body collapse<?php echo true?' in':'';?>" id="<?php echo 'collapse_fault'.$k; ?>" style="border:solid 1px #0089db;border-bottom-left-radius: 4px!important;; border-bottom-right-radius: 4px!important;">
                                            <div class="form-group col-sm-4">
                                                <label for="" class="col-sm-4 control-label"><?php echo @$lang['t30'];?></label>
                                                <div class="col-sm-8">
                                                    <span><?php echo $fault['fault_type']; ?></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label for="" class="col-md-4 control-label"><?php echo @$lang['t31'];?></label>
                                                <div class="col-sm-8">
                                                    <span><?php echo $fault['user_name']; ?></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-4">
                                                <label for="" class="col-sm-4 control-label"><?php echo @$lang['t32'];?></label>
                                                <div class="col-sm-8">
                                                    <span><?php echo $fault['add_time']; ?></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12">
                                                <label for="" class="col-sm-2 control-label" style="width:10%"><?php echo @$lang['t33'];?></label>
                                                <div class="col-sm-10">
                                                    <span><?php echo $fault['fault_content']; ?></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12">
                                                <label for="" class="col-sm-2 control-label" style="width:10%"><?php echo @$lang['t34'];?></label>
                                                <div class="col-sm-10">
                                                    <?php if($fault['fault_image']) {?>
                                                    <a target="_blank" href="<?php echo $fault['fault_image']; ?>"><i class="fa fa-file-image-o text-danger"></i></a>
                                                    <?php }?>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12">
                                                <label for="" class="col-sm-2 control-label" style="width:10%"><?php echo @$lang['t35'];?></label>
                                                <div class="col-sm-10">
                                                    <label title="<?php echo @$lang['t36'];?>" class="text-blue text-bold checkbox-inline"><input checked type="checkbox" name="is_handling" value="1"/><?php echo @$lang['t37'];?></label>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12">
                                                <label for="" class="col-sm-2 control-label" style="width:10%"><?php echo @$lang['t38'];?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline pull-left">
                                                        <input type="radio" checked name="how_to_fill" value="1" /><?php echo @$lang['t39'];?>
                                                    </label>
                                                    <label class="radio-inline pull-left">
                                                        <input type="radio" name="how_to_fill" value="2" /><?php echo @$lang['t40'];?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12 repair_type hide">
                                                <label for="" class="col-sm-2 control-label" style="width:10%"><?php echo @$lang['t41'];?></label>
                                                <div class="col-sm-10">
                                                    <label class="radio-inline pull-left">
                                                        <input type="radio" name="repair_type" value="1" /><?php echo @$lang['t42'];?>
                                                    </label>
                                                    <label class="radio-inline pull-left">
                                                        <input type="radio" name="repair_type" value="2" /><?php echo @$lang['t43'];?>
                                                    </label>
                                                    <label class="radio-inline pull-left">
                                                        <input type="radio" name="repair_type" value="3" /><?php echo @$lang['t44'];?>
                                                    </label>
                                                    <label class="radio-inline pull-left">
                                                        <input type="radio" name="repair_type" value="4" /><?php echo @$lang['t45'];?>
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12 handle_content hide">
                                                <label for="" class="col-sm-2 control-label" style="width:10%"><?php echo @$lang['t46'];?></label>
                                                <div class="col-sm-10">
                                                    <span><textarea class="col-sm-10" rows="3" name="handle_content" placeholder="<?php echo @$lang['t47'];?>"></textarea></span>
                                                </div>
                                            </div>
                                            <div class="form-group col-sm-12 handle_image hide">
                                                <label for="" class="col-sm-2 control-label" style="width:10%"><?php echo @$lang['t48'];?></label>
                                                <div class="col-sm-10">
                                                    <button type="button" class="btn btn-primary btn-sm button-upload" style="outline: none;" data-tag="image" data-action="<?php echo $upload_url; ?>" data-tage="image">
                                                        <i class="fa fa-upload"></i>
                                                        <div class="inline"><?php echo @$lang['t49'];?></div>
                                                        <input type="hidden" name="handle_image" value="" class="filepath"/>
                                                        <span class="filepath"></span>
                                                    </button>
                                                    <?php if (isset($error['filename'])) { ?><div class="text-danger"><?php echo $error['filename']; ?></div><?php } ?>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <div class="col-sm-12">
                                                    <div class="pull-right">
                                                        <input type="hidden" name="fault_id" value="<?php echo $fault['fault_id']; ?>" />
                                                        <input type="hidden" name="parking_id" value="<?php echo $fault['parking_id']; ?>" />
                                                        <a href="javascript:;" class="btn btn-sm btn-primary handlingSubmit"><?php echo @$lang['t50'];?></a>
                                                        <!--<a href="javascript:;" class="btn btn-sm btn-default cancel">取消</a>-->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-2"></div>
                                </div>
                            </form>
                            <?php endforeach;?>
                            <?php endif; ?>
                            
                        </div>
                                                
                    </div>
                        
                                                
                                                
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div style="text-align:center">
                   
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default"  style="padding:0 30px;height:40px;font-size:14px;line-height:40px"><?php echo @$lang['t51'];?></a>
                                    </div>
                                </div>
                            </div>
                </div>
                                                
            </div>
                                                
        </div>
                                                
    </div>

    <div class="modal fade" id="handling-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-blue-gradient" style="padding:10px">
                    <button type="button" class="close" data-dismiss="modal">
                        <span aria-hidden="true">×</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel"><?php echo @$lang['t52'];?></h4>
                </div>
                <div class="modal-body">
                    <form method="post" action="<?php echo $batch_handling_action; ?>">
                        <div class="box-body">
                            <div class="form-group col-sm-12">
                                <label for="" class="col-sm-2 control-label" style="width:12%"><?php echo @$lang['t10'];?></label>
                                <div class="col-sm-10">
                                    <span><?php echo $admin_name; ?></span>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 repair_type">
                                <label for="" class="col-sm-3 control-label" style="width:12%"><?php echo @$lang['t41'];?></label>
                                <div class="col-sm-9">
                                    <label class="radio-inline pull-left">
                                        <input type="radio" name="repair_type" value="1" /><?php echo @$lang['t42'];?>
                                    </label>
                                    <label class="radio-inline pull-left">
                                        <input type="radio" name="repair_type" value="2" /><?php echo @$lang['t43'];?>
                                    </label>
                                    <label class="radio-inline pull-left">
                                        <input type="radio" name="repair_type" value="3" /><?php echo @$lang['t44'];?>
                                    </label>
                                    <label class="radio-inline pull-left">
                                        <input type="radio" name="repair_type" value="4" /><?php echo @$lang['t45'];?>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 handle_content">
                                <label for="" class="col-sm-2 control-label" style="width:12%"><?php echo @$lang['t46'];?></label>
                                <div class="col-sm-10">
                                    <span><textarea class="col-sm-10" rows="3" name="handle_content" placeholder="<?php echo @$lang['t47'];?>"></textarea></span>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 handle_image">
                                <label for="" class="col-sm-2 control-label" style="width:12%"><?php echo @$lang['t48'];?></label>
                                <div class="col-sm-10">
                                    <button type="button" class="btn btn-primary btn-sm button-upload" style="outline: none;" data-tag="image" data-action="<?php echo $upload_url; ?>" data-tage="image">
                                        <i class="fa fa-upload"></i>
                                        <div class="inline"><?php echo @$lang['t49'];?></div>
                                        <input type="hidden" name="handle_image" value="" class="filepath"/>
                                        <span class="filepath"></span>
                                    </button>
                                    <?php if (isset($error['filename'])) { ?><div class="text-danger"><?php echo $error['filename']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div style="text-align:center">
                                        <input type="hidden" name="is_batch_handling" value="1" />
                                        <input type="hidden" name="fault_ids" value="" />
                                        <input type="hidden" name="bicycle_sn" value="<?php echo $bicycle['bicycle_sn']; ?>" />
                                        <a href="javascript:;" class="btn btn-sm btn-primary batchHandlingSubmit"  style="padding:0 30px;height:40px;font-size:14px;line-height:40px"><?php echo @$lang['t50'];?></a>
                                        <!--<a href="javascript:;" class="btn btn-sm btn-default" data-dismiss="modal">取消</a>-->
                                    </div>
                                </div>
                            </div>
                        </div>
                       
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<link rel="stylesheet" href="//cache.amap.com/lbs/static/main1119.css"/>

    
<script type="text/javascript"  src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDykSoVc_Z96D_rLGPhQOf9XHYluROzceI"></script>
<script type="text/javascript">
    
    <?php if (isset($bicycle['lng']) && isset($bicycle['lat'])) { ?>
      

     var myCenter=new google.maps.LatLng('<?php echo $bicycle['lat']; ?>','<?php echo $bicycle['lng']; ?>');

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
    
    
    
<script type="text/javascript">
    //故障处理提交
    $(document).on('click', '.handlingSubmit', function () {
        if ($(this).parents('form:first').find('input[name=how_to_fill]:radio:checked').val() == 1) {
            $('#handling-modal').modal();
            return false;
        }
        if (!$(this).parents('form:first').find('input[name=repair_type]:radio:checked').size()) {
            alert('<?php echo @$lang['t53'];?>');
            return false;
        }
        $.ajax('index.php?route=operation/fault/handling', {
            dataType: 'json',
            data: $(this).parents('form:first').serialize(),
            method: 'POST',
            success: function (result) {
                window.location.reload();
            }
        });
    });

    //批量故障处理提交
    $(document).on('click', '.batchHandlingSubmit', function () {
        if (!$(this).parents('form:first').find('input[name=repair_type]:radio:checked').size()) {
            alert('<?php echo @$lang['t53'];?>');
            return false;
        }
        var fault_ids = [];
        $('form.form_fault').each(function () {
            var is_handling = $(this).find('input[name=is_handling]:checkbox:first').prop('checked');
            var how_to_fill = $(this).find('input[name=how_to_fill]:radio:checked').val() == 1;
            var fault_id = $(this).find('input[name=fault_id]:first').val();
            if (is_handling && how_to_fill && fault_id) {
                fault_ids.push(fault_id);
            }
        });
        $(this).parents('form:first').find('input[name="fault_ids"]:first').val(fault_ids);
        $.ajax('index.php?route=operation/fault/batchHandling', {
            dataType: 'json',
            data: $(this).parents('form:first').serialize(),
            method: 'POST',
            success: function (result) {
                window.location.reload();
            }
        });
    });

    $(function () {
        $('.btn-box-tool').on('click', function () {
            $('#content-wrapper').css('height', '100%');
        });

        $('input[name=how_to_fill]:radio').change(function () {
            if ($(this).val() == 2) {
                $(this).parents('form:first').find('.repair_type, .handle_content, .handle_image').removeClass('hide');
            } else {
                $(this).parents('form:first').find('.repair_type, .handle_content, .handle_image').addClass('hide');
            }
        });

    });
</script>
<?php echo $footer;?>
