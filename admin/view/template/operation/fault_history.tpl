<?php echo $header; ?>
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
        <span>故障详情</span>
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
                    <li><a href="<?php echo $info_action; ?>">故障详情</a></li>
                    <li class="active"><a href="javascirpt:;">故障历史</a></li>
                </ul>
                <div class="tab-content">
                    <!-- 故障历史 -->
                    <div class="box box-widget">
                        <div class="box-header with-border">
                            <h3 class="box-title">故障历史 (<?php echo !empty($faultHistoryList) ? count($faultHistoryList) : 0; ?>) <small>单车编号<?php echo $bicycle['bicycle_sn'];?></small></h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="box-body">
                        <?php if (!empty($faultHistoryList)): ?>
                        <?php foreach ($faultHistoryList AS $k => $faultHistory): ?>
                            <div class="form-group col-sm-12">
                                <div class="form-group col-sm-2"></div>
                                <div class="form-group col-sm-8">
                                    <div class="box-header bg-success">
                                        <h3 class="box-title">#<?php echo $faultHistory['fault_id']; ?> <?php echo $faultHistory['repair_type']; ?></h3>
                                        <div class="box-tools pull-right">
                                            <button type="button" href="#<?php echo 'collapse_fault_history'.$k; ?>" class="btn btn-box-tool" data-toggle="collapse"><i class="fa fa-minus"></i></button>
                                        </div>
                                    </div>
                                    <div class="panel-body collapse in" id="<?php echo 'collapse_fault_history'.$k; ?>" style="border:solid 1px #d6e9c6;border-bottom-left-radius: 4px!important;; border-bottom-right-radius: 4px!important;">
                                        <div class="form-group col-sm-4">
                                            <label for="" class="col-sm-4 control-label">故障类型</label>
                                            <div class="col-sm-8">
                                                <span><?php echo $faultHistory['fault_type']; ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label for="" class="col-md-4 control-label">上报人</label>
                                            <div class="col-sm-8">
                                                <span><?php echo $faultHistory['user_name']; ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label for="" class="col-sm-4 control-label">上报时间</label>
                                            <div class="col-sm-8">
                                                <span><?php echo $faultHistory['add_time']; ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-12">
                                            <label for="" class="col-sm-2 control-label" style="width:10%">故障描述</label>
                                            <div class="col-sm-10">
                                                <span><?php echo $faultHistory['fault_content']; ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-12">
                                            <label for="" class="col-sm-2 control-label" style="width:10%">故障图片</label>
                                            <div class="col-sm-10">
                                                <?php if($faultHistory['fault_image']) {?>
                                                <span><a target="_blank" href="<?php echo $faultHistory['fault_image']; ?>"><i class="fa fa-file-image-o text-danger"></i></a></span>
                                                <?php }?>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label for="" class="col-sm-4 control-label">故障处理</label>
                                            <div class="col-sm-8">
                                                <span title="处理时长：<?php echo $faultHistory['repair_time_delta']; ?>"><?php echo $faultHistory['repair_time']; ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label for="" class="col-md-4 control-label">处理时长</label>
                                            <div class="col-sm-8">
                                                <span><?php echo $faultHistory['repair_time_delta']; ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label for="" class="col-sm-4 control-label">处理人</label>
                                            <div class="col-sm-8">
                                                <span><?php echo $faultHistory['admin_name']; ?></span>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-12">
                                            <label for="" class="col-sm-2 control-label" style="width:10%">维修方式</label>
                                            <div class="col-sm-10">
                                                <?php echo $faultHistory['repair_type']; ?>
                                            </div>
                                        </div>
                                        <div class="form-group col-sm-12">
                                            <label for="" class="col-sm-2 control-label" style="width:10%">备注说明</label>
                                            <div class="col-sm-10">
                                                <?php echo $faultHistory['repair_remarks']; ?>
                                            </div>
                                        </div>
                                        <!--<div class="form-group col-sm-12">
                                            <label for="" class="col-sm-2 control-label" style="width:10%">处理图片</label>
                                            <div class="col-sm-10">
                                                <?php if($faultHistory['repair_image']) {?>
                                                <a target="_blank" href="<?php echo $faultHistory['repair_image']; ?>"><i class="fa fa-file-image-o text-success"></i></a>
                                                <?php }?>
                                            </div>
                                        </div>-->
                                    </div>
                                </div>
                                <div class="form-group col-sm-2"></div>
                            </div>
                        <?php endforeach;?>
                        <?php endif; ?>
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
                    <h4 class="modal-title" id="myModalLabel">统一故障处理</h4>
                </div>
                <div class="modal-body">
                    <form method="post" action="<?php echo $batch_handling_action; ?>">
                        <div class="box-body">
                            <div class="form-group col-sm-12">
                                <label for="" class="col-sm-2 control-label" style="width:12%">处理人</label>
                                <div class="col-sm-10">
                                    <span><?php echo $admin_name; ?></span>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 repair_type">
                                <label for="" class="col-sm-3 control-label" style="width:12%">维修方式</label>
                                <div class="col-sm-9">
                                    <label class="radio-inline pull-left">
                                        <input type="radio" name="repair_type" value="1" />现场维修
                                    </label>
                                    <label class="radio-inline pull-left">
                                        <input type="radio" name="repair_type" value="2" />返仓维修
                                    </label>
                                    <label class="radio-inline pull-left">
                                        <input type="radio" name="repair_type" value="3" />报废回收
                                    </label>
                                    <label class="radio-inline pull-left">
                                        <input type="radio" name="repair_type" value="4" />其他
                                    </label>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 handle_content">
                                <label for="" class="col-sm-2 control-label" style="width:12%">备注说明</label>
                                <div class="col-sm-10">
                                    <span><textarea class="col-sm-10" rows="3" name="handle_content" placeholder="请输入处理说明"></textarea></span>
                                </div>
                            </div>
                            <div class="form-group col-sm-12 handle_image">
                                <label for="" class="col-sm-2 control-label" style="width:12%">上传图片</label>
                                <div class="col-sm-10">
                                    <button type="button" class="btn btn-primary btn-sm button-upload" style="outline: none;" data-tag="image" data-action="<?php echo $upload_url; ?>" data-tage="image">
                                        <i class="fa fa-upload"></i>
                                        <div class="inline">上传</div>
                                        <input type="hidden" name="handle_image" value="" class="filepath"/>
                                        <span class="filepath"></span>
                                    </button>
                                       <?php if (isset($error['filename'])) { ?><div class="text-danger"><?php echo $error['filename']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-12">
                                    <div class="pull-right">
                                        <input type="hidden" name="is_batch_handling" value="1" />
                                        <input type="hidden" name="fault_ids" value="" />
                                        <a href="javascript:;" class="btn btn-sm btn-primary batchHandlingSubmit">提交处理</a>
                                        <a href="javascript:;" class="btn btn-sm btn-default" data-dismiss="modal">取消</a>
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
<script type="text/javascript"  src="//webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d&plugin=AMap.Geocoder&callback=initMap"></script>
<script src="<?php echo HTTP_CATALOG;?>js/coordinate.js"></script>
<script type="text/javascript">
    <?php if (isset($bicycle['lng']) && isset($bicycle['lat'])) { ?>
            var lnglat = wgs84togcj02(parseFloat(<?php echo $bicycle['lng']; ?>), parseFloat(<?php echo $bicycle['lat']; ?>));
    <?php } ?>

            var initMap = function () {
                if (typeof AMap != 'undefined') {
                    var marker, map = new AMap.Map("container", {
                        resizeEnable: true,
                        zoom: 13
                    });
                    marker = new AMap.Marker({
                        map: map
                    });
                    var geocoder = new AMap.Geocoder({
                        radius: 1000,
                        extensions: "all"
                    });
                    if (typeof lnglat != 'undefined') {
                        marker.setPosition(lnglat);
                        map.setCenter(lnglat);
                        geocoder.getAddress(lnglat, function (status, result) {
                            if (status === 'complete' && result.info === 'OK') {
                                $('#formattedAddress').val(result.regeocode.formattedAddress);
                            }
                        });
                    }
                }
            }
</script>
<script type="text/javascript">
    //故障处理提交
    $(document).on('click', '.handlingSubmit', function () {
        if ($(this).parents('form:first').find('input[name=how_to_fill]:radio:checked').val() == 1) {
            $('#handling-modal').modal();
            return false;
        }
        if ( ! $(this).parents('form:first').find('input[name=repair_type]:radio:checked').size()) {
            alert('请选择一种维修方式');
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
        if ( ! $(this).parents('form:first').find('input[name=repair_type]:radio:checked').size()) {
            alert('请选择一种维修方式');
            return false;
        }
        var fault_ids = [];
        $('form.form_fault').each(function(){
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
