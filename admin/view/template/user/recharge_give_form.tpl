<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>充值优惠设置</span>
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
                                    <label class="col-sm-2 control-label">区域名称</label>
                                    <div class="col-sm-5">
                                        
                                        <select name="region_id" class="form-control" onchange="show_city(this)">
                                            
                                            <option value="" >--所在区域--</option>
                                            <?php foreach ($region_activity_options as $key => $option) { ?>
                                            <option value="<?php echo $key; ?>" <?php if ($key == $region_id) echo 'selected'; ?> ><?php echo $option; ?></option>
                                            <?php }?>
                                        </select>
                                        <?php if (isset($error['region_id'])) { ?><div class="text-danger"><?php echo $error['region_id']; ?></div><?php } ?>
                                    
                                    </div>
                                </div>
                                        
                            
                            <div class="form-group">
                                    <label class="col-sm-2 control-label">城市名称</label>
                                    <div class="col-sm-5">
                                        
                                        <select name="city_id" class="form-control" id="city_id">
                                            
                                            <option value=""  >选择城市</option>
                                            
                                        </select>
                                        <?php if (isset($error['city_id'])) { ?><div class="text-danger"><?php echo $error['city_id']; ?></div><?php } ?>
                                        
                                    </div>
                                </div>
                            
        
                            <div class="form-group">
                                    <label class="col-sm-2 control-label">充值金额</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" id="recharge_amount" name="recharge_amount" value="<?php echo $data['recharge_amount']; ?>" placeholder="充值金额"  class="form-control">
                                            <span class="input-group-addon">元</span>
                                        </div>
                                         <?php if (isset($error['recharge_amount'])) { ?>
                                        <div class="text-danger"><?php echo $error['recharge_amount']; ?></div><?php } ?>
                                    </div>
                                </div>
                            <div class="form-group">
                                    <label class="col-sm-2 control-label">赠送金额</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" id="present_amount" name="present_amount" value="<?php echo $data['present_amount']; ?>" placeholder="赠送金额"  class="form-control">
                                            <span class="input-group-addon">元</span>
                                        </div>
                                         <?php if (isset($error['present_amount'])) { ?>
                                        <div class="text-danger"><?php echo $error['present_amount']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">状态</label>
                                    <div class="col-sm-5">
                                        <input type="checkbox" id="state" name="state" value="1" placeholder="状态" class="bootstrap-switch in-list" data-on-text="启用" data-off-text="停用" data-label-width="5" <?php echo $data['state']==1 ? 'checked' : ''; ?> />
                                        <?php if (isset($error['state'])) { ?><div class="text-danger"><?php echo $error['state']; ?></div><?php } ?>
                                    </div>
                                </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-2"></div>
                            <div class="col-sm-5">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-sm btn-success margin-r-5">提交</button>
                                    <a href="<?php echo $return_action;?>" class="btn btn-sm btn-default">返回</a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
<link rel="stylesheet" href="<?php echo HTTP_CATALOG . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.css" />
<script type="text/javascript" src="<?php echo HTTP_CATALOG . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $('input.bootstrap-switch').bootstrapSwitch();
});
</script>

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
        var a='<option value="">--选择城市--</option>';
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
        var a='<option value="">--选择城市--</option>';
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