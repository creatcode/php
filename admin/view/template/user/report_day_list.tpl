<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>财务报表</span>
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
                    <li><a href="<?php echo $month_report_action; ?>" data-toggle="tab">财务月报表</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">财务日报表</a></li>
                    <li><a href="<?php echo $summary_report_action; ?>" data-toggle="tab">财务汇总表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <!-- <select class="input-sm" name="cooperator_id">
                                    <option value="">合伙人</option>
                                    <?php if (is_array($cooperators) && !empty($cooperators)) { ?>
                                    <?php foreach($cooperators as $cooperator) { ?>
                                    <option value="<?php echo $cooperator['cooperator_id']; ?>" <?php echo $cooperator['cooperator_id']==$filter['cooperator_id'] ? 'selected' : ''; ?>><?php echo $cooperator['cooperator_name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select> -->
                                <select name="region_id" id="region_id" class="input-sm" onchange="show_city(this)">
                                    <option value="">--请选择区域--</option>
                                    <?php foreach($filter_regions as $k => $v) { ?>
                                    <option value="<?php echo $v['region_id']; ?>" <?php echo (string)$v['region_id'] == @$filter['region_id'] ? 'selected' : ''; ?>><?php echo $v['region_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <select name="city_id" id="city_id" class="input-sm">
                                    <option value="">--请选择城市--</option>
                                    
                                </select>
                                <select class="input-sm" name="user_type">
                                    <option value>用户类型</option>
                                    <?php foreach($user_types as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == $filter['user_type'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>
                                 <select name="time_type" id="time_select"  class="input-sm" onchange="addrat()">
                                    <option value="0">选择时间区间</option>
                                    <?php if(!empty($time_type)) {?>
                                    <?php foreach($time_type as $k=>$v){?>
                                    <option value="<?php echo $k?>" <?php echo $k == @$filter['time_type'] ? 'selected' : ''; ?>><?php echo $v?></option>
                                    <?php }?>
                                    <?php }?>
                                </select>
                                <input type="text" name="search_time" value="<?php echo $filter['search_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9; width: 200px;" placeholder="时间范围"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
                            <!--<button class="btn btn-default btn-sm button-upload" data-action="<?php echo $import_action; ?>"><i class="fa fa-upload"></i>&nbsp;导入</button>-->
                            <button class="btn btn-default btn-sm" form="search_form" formmethod="post" formaction="<?php echo $export_action; ?>"><i class="fa fa-download"></i>&nbsp;导出</button>
                        </div>
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>&nbsp;<?php echo $error['warning']; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <?php if (isset($success)) { ?>
                        <div class="alert bg-light-blue"><i class="fa fa-check-circle"></i>&nbsp;<?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form id="table_form" class="table_form" method="post">
                            <table class="table table-bordered table-hover finance-table" role="grid">
                                <thead>
                                <tr>
                                    <th width="20%">项目</th>
                                    <?php if (isset($titles) && is_array($titles) && !empty($titles)) { ?>
                                    <?php foreach($titles as $title) { ?>
                                    <th><?php echo $title; ?></th>
                                    <?php } ?>
                                    <?php } ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (isset($list) && is_array($list) && !empty($list)) { ?>
                                <?php foreach($list as $row) { ?>
                                <tr>
                                    <?php foreach($row as $item) { ?>
                                    <td><?php echo $item; ?></td>
                                    <?php } ?>
                                </tr>
                                <?php } ?>
                                <?php } ?>
                                </tbody>
                            </table>
                        </form>
                        <div class="row"><div class="col-sm-6 text-left"><?php echo $pagination; ?></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
<style type="text/css">
    .finance-table tr:nth-child(3) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(4) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(6) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(7) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(8) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(9) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
	.finance-table tr:nth-child(11) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(12) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
</style>
<script type="text/javascript">
   $(function(){
            addrat();
    });
        var b = "";
        function addrat(){
        var a = $("#time_select").val();
        if(a==1){
            b = "YYYY";
        }else if(a==2){
            b = "YYYY-MM";
        }else if(a==3){
            b = "YYYY-MM-DD";
        }else if(a==0){
            b = "YYYY-MM-DD";
        }
        $('.date-range').daterangepicker({
            "showDropdowns": true,
            locale:{
                format: b,
                isAutoVal:false,
            }
      });
        
    };
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
        var a='<option value="">--请选择城市--</option>';
        if(region_id){
            region_data[region_id].forEach(function (item,index,input) {
        a+="<option value="+index+">"+item+"</option>";
            });
            $("#city_id").html(a); 
        }

    }
    function init_city(){
        var region_id="<?php echo $filter['region_id'];?>";
        var city_id="<?php echo $filter['city_id'];?>";
        var a='<option value="">--请选择城市--</option>';
        if(region_id&&city_id){
            region_data[region_id].forEach(function (item,index,input) {
        a+="<option value="+index;
                if(index==city_id){
                    a+=" selected ";
                }
                a+=">"+item+"</option>";
            });
        }
        $("#city_id").html(a); 
    }
    $(function(){
         init_city();
    });
    
</script>
<script type="text/javascript">
    <?php if (!empty($filter['cooperator_id'])) { ?>
        $(".finance-table tr").eq(2).hide();
        $(".finance-table tr").eq(3).hide();
        $(".finance-table tr").eq(4).hide();
        $(".finance-table tr").eq(5).hide();
        $(".finance-table tr").eq(6).hide();
        $(".finance-table tr").eq(7).hide();
    <?php } ?>

   

    $("#filter_type").change(function() {
        $("#filter_text").attr("name", $(this).val());
    });
</script>
<?php echo $footer;?>