<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>消费记录</span>
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
                    <li><a href="<?php echo $chart_action; ?>" data-toggle="tab">统计图表</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">消费记录列表</a></li>
                    <!-- <li><a href="<?php echo $city_ranking_action; ?>" data-toggle="tab">合伙人排行</a></li> -->
                    <!-- <li><a href="<?php echo $order_free_chart; ?>" data-toggle="tab">免费单车图表</a></li>
                    <li><a href="<?php echo $order_free_list; ?>" data-toggle="tab">免费单车列表</a></li> -->
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="filter_type" id="filter_type" class="input-sm">
                                    <?php if (!empty($filter_types) && is_array($filter_types)) { ?>
                                    <?php foreach($filter_types as $key => $val) { ?>
                                    <option value="<?php echo $key; ?>" <?php echo (string)$key == $filter_type ? 'selected' : ''; ?>><?php echo $val; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                                <input type="text" name="<?php echo $filter_type; ?>" value="<?php echo isset($filter[$filter_type]) ? $filter[$filter_type] : ''; ?>" id="filter_text" class="input-sm" style="border: 1px solid #a9a9a9;"/>
                                <select name="order_state" class="input-sm">
                                    <option value>状态</option>
                                    <?php foreach($order_state as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == $filter['order_state'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>
                                <select name="user_type" class="input-sm">
                                    <option value>用户类型</option>
                                    <?php foreach($user_type as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == $filter['user_type'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>
                                <select name="region_id" id="region_id" class="input-sm" onchange="show_city(this)">
                                    <option value="">--全部区域--</option>
                                    <?php foreach($filter_regions as $k => $v) { ?>
                                    <option value="<?php echo $v['region_id']; ?>" <?php echo (string)$v['region_id'] == @$filter['region_id'] ? 'selected' : ''; ?>><?php echo $v['region_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <select name="city_id" id="city_id" class="input-sm">
                                    <option value="">--全部城市--</option>
                                    
                                </select>
                                 <select name="time_type" id="time_select"  class="input-sm" onchange="addrat()">
                                    <option value="0">选择时间区间</option>
                                    <?php if(!empty($time_type)) {?>
                                    <?php foreach($time_type as $k=>$v){?>
                                    <option value="<?php echo $k?>" <?php echo $k == @$filter['time_type'] ? 'selected' : ''; ?>><?php echo $v?></option>
                                    <?php }?>
                                    <?php }?>
                                </select>
                                <input type="text" name="add_time" value="<?php echo $filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 150px;" placeholder="下单时间"/>
                                <input type="text" name="start_time" value="<?php echo $filter['start_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 150px;" placeholder="开始时间"/>
                                <input type="text" name="end_time" value="<?php echo $filter['end_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 150px;" placeholder="结束时间"/>
                                <input type="text" name="settlement_time" value="<?php echo $filter['settlement_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 150px;" placeholder="结算时间"/>
                                <input type="text" name="amount" value="<?php echo $filter['amount']; ?>" class="input-sm" style="border: 1px solid #a9a9a9;" placeholder="订单金额大于"/>
                                <input type="text" name="ride_time" value="<?php echo $filter['ride_time']; ?>" class="input-sm" style="border: 1px solid #a9a9a9;" placeholder="骑行超过多少分钟"/>

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
                            <table class="table table-bordered table-hover dataTable" role="grid">
                                <thead>
                                <tr>
                                    <!--<th style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']:enabled').prop('checked', this.checked);"></th>-->
                                    <?php foreach ($data_columns as $column) { ?>
                                    <th><?php echo $column['text']; ?></th>
                                    <?php } ?>
                                    <th style="min-width:130px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['order_id']?>"></td>-->
                                    <td><?php echo $data['region_name']?></td>
                                    <td><?php echo $data['city_name']?></td>
                                    <td><?php echo $user_type[$data['user_type']]?></td>
                                    <td><?php echo $data['order_sn']?></td>
                                    <td><?php echo $data['lock_sn']?></td>
                                    <td><?php echo $data['bicycle_sn']?></td>
                                    <td><?php echo $data['user_name']?></td>
                                    
                                    <td><?php echo $data['order_state']?></td>
                                    <td><?php echo $data['pay_amount']?></td>
                                    <td><?php echo $data['refund_amount']?></td>
                                    <td><?php echo $data['start_time']?></td>
                                    <td><?php echo $data['end_time']?></td>
                                    <td><?php echo $data['add_time']?></td>
                                    <td><?php echo $data['settlement_time']?></td>
                                    <td><button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>   <?php if('已完成' == $data['order_state'] && $data['pay_amount']>0){ ?><button data-url="<?php echo $data['edit_order_amount_action']; ?>" type="button" class="btn btn-primary link"><i class="fa fa-fw fa-edit"></i>修改订单</button><?php }?></td>
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
        var a='<option value="">--全部城市--</option>';
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
        var a='<option value="">--全部城市--</option>';
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

    $("#filter_type").change(function() {
        $("#filter_text").attr("name", $(this).val());
    });
</script>
<?php echo $footer;?>