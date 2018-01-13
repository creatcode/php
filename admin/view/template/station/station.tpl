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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo @$lang['t3'];?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="region_id" id="region_id" class="input-sm" onchange="show_city(this)">
                                    <option value="">--<?php echo @$lang['t4'];?>--</option>
                                    <?php foreach($filter_regions as $k => $v) { ?>
                                    <option value="<?php echo $v['region_id']; ?>" <?php echo (string)$v['region_id'] == @$filter['region_id'] ? 'selected' : ''; ?>><?php echo $v['region_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <select name="city_id" id="city_id" class="input-sm">
                                    <option value="">--<?php echo @$lang['t5'];?>--</option>
                                    
                                </select>
                                <!--<input type="text" name="<?php echo @$filter_type; ?>" value="<?php echo isset($filter[$filter_type]) ? @$filter[$filter_type] : ''; ?>" id="filter_text" class="input-sm" style="border: 1px solid #a9a9a9;"/>-->
                                <select  class="input-sm" name="station_state">
                                    <option value=''><?php echo @$lang['t6'];?></option>
                                    <?php foreach($station_states as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == @$filter['station_state'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>
                                <!--<select name="is_using" class="input-sm">
                                    <option value>是否使用中</option>
                                    <?php foreach($model['is_using'] as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == @$filter['is_using'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>-->
                                <select name="time_type" id="time_select"  class="input-sm" onchange="addrat()">
                                    <option value="0"><?php echo @$lang['t23'];?></option>
                                    <?php if(!empty($time_type)) {?>
                                    <?php foreach($time_type as $k=>$v){?>
                                    <option value="<?php echo $k?>" <?php echo $k == @$filter['time_type'] ? 'selected' : ''; ?>><?php echo $v?></option>
                                    <?php }?>
                                    <?php }?>
                                </select>
                                <input type="text" name="add_time" value="<?php echo @$filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 200px;" placeholder="<?php echo @$lang['t7'];?>"/>
                                <input type="text" name="" value="" class="input-sm " style="border: 1px solid #a9a9a9;width: 200px;" placeholder="<?php echo @$lang['t8'];?>"/>

                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;<?php echo @$lang['t9'];?></button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
                            <div class="btn-group">
                                <a href="<?php echo $add_action; ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;<?php echo @$lang['t10'];?></a>
                               
                            </div>

                            <!--<button class="btn btn-default btn-sm button-upload" data-action="<?php echo $import_action; ?>"><i class="fa fa-upload"></i>&nbsp;导入</button>-->
                            <a href="<?php echo $import_bicycle_action; ?>" class="btn btn-default btn-sm"><i class="fa fa-upload"></i>&nbsp;<?php echo @$lang['t11'];?></a>
                            <!--<button class="btn btn-default btn-sm" form="search_form" formmethod="post" formaction="<?php echo $export_action; ?>"><i class="fa fa-download"></i>&nbsp;导出</button>-->
                            <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#myModal"><i class="fa fa-download"></i>&nbsp;<?php echo @$lang['t12'];?></button>
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
                            <table class="table table-bordered table-hover dataTable" role="grid">
                                <thead>
                                <tr>
                                    <!--<th style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']:enabled').prop('checked', this.checked);"></th>-->
                                    <?php foreach ($data_columns as $column) { ?>
                                    <th><?php echo $column['text']; ?></th>
                                    <?php } ?>
                                    <th style="min-width:130px;"><?php echo @$lang['t13'];?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['bicycle_id']?>"></td>-->
                                    <td><?php echo $data['station_sn']?></td>
                                    <td><?php echo $cities[$data['city_id']]['city_name'];?></td>
                                    <td><?php echo $regions[$data['region_id']]['region_name'];?></td>
                                    <td><?php echo $data['station_name']?></td>
                                    <td><?php echo 'lng:'.$data['lng'].':lat'.$data['lat'];?></td>
                                    <td><?php echo $station_states[$data['station_state']];?></td>
                                    <td><?php echo $station_power_states[$data['power_state']];?></td>
                                    <td><?php echo $data['total']?></td>
                                    <td><?php echo $data['used']?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i><?php echo @$lang['t19'];?></button>
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo $data['stake_action']; ?>"><?php echo @$lang['t14'];?></a></li>
                                                <li><a href="<?php echo $data['alarm_action']; ?>"><?php echo @$lang['t15'];?></a></li>
                                                <!--<li><a href="<?php echo $data['adv_action']; ?>"><?php echo @$lang['t16'];?></a></li>-->
                                                <li><a href="<?php echo $data['edit_action']; ?>"><?php echo @$lang['t17'];?></a></li>
                                                <li><a href="<?php echo $data['delete_action']; ?>"><?php echo @$lang['t18'];?></a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
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
<div class="modal fade" id="myModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?php echo @$lang['t20'];?></h4>
            </div>
            <div class="modal-body  text-center">
                <p><?php echo @$lang['t21'];?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal"><?php echo @$lang['t22'];?></button>
                <button id="export" class="btn btn-primary" form="search_form" formmethod="post" formaction="<?php echo $export_action; ?>"><?php echo @$lang['t12'];?></button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
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

    $("#filter_type").change(function() {
        $("#filter_text").attr("name", $(this).val());
    });

    $("#export").click(function() {
        $('#myModal').modal('hide')
    });



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
        var a='<option value="">--<?php echo @$lang['t5'];?>--</option>';
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
        var a='<option value="">--<?php echo @$lang['t5'];?>--</option>';
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
<?php echo $footer;?>