<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>财务结算表</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">财务结算表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="city_id" class="input-sm">
                                    <option value="0">--选择城市--</option>
                                    <?php foreach($cityList as $v){
                                        if($city_id ==$v['city_id']){
                                    ?>
                                    <option selected="selected" value="<?php echo $v['city_id']; ?>"><?php echo $v['city_name']; ?> </option>
                                    <?php
                                        }else{
                                    ?>
                                    <option value="<?php echo $v['city_id']; ?>"><?php echo $v['city_name']; ?> </option>
                                    <?php }
                                     } ?>
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
                            <a href="<?php echo $add_action; ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;新增结算</a>
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
                                    <?php foreach ($data_columns as $column) { ?>
                                    <th><?php echo $column['text']; ?></th>
                                    <?php } ?>
                                    <th style="min-width:130px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <td><?php echo $data['city']?></td>
                                    <td width="100"><?php echo $data['payoff_start_time']?>-<?php echo $data['payoff_end_time']?></td>
                                    <td><?php echo $data['cooperator_name']?></td>
                                    <td><?php echo $data['bicycle_total']?></td>
                                    <td><?php echo $data['orders_total']?></td>
                                    <td><?php echo $data['daily_usage']?></td>
                                    <td><?php echo $data['orders_amount']?></td>
                                    <td><?php echo $data['commission_ratio']?></td>
                                    <td><?php echo $data['payoff_base']?></td>
                                    <td><?php echo $data['subsidy']?></td>
                                    <td><?php echo $data['payoff_amount']?></td>
                                    <td><?php echo $data['cost_recovery']?></td>
                                    <td width="150"><?php echo $data['account_payee']?></td>
                                    <td><?php echo $data['payoff_state']?></td>
                                    <td width="150"><?php echo $data['payoff_remarks']?></td>
                                    <td>
                                        <?php if ($data['show_pay_button']) { ?>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $data['pay_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-pencil"></i>已支付</button>
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo $data['delete_action']; ?>">删除</a></li>
                                            </ul>
                                        </div>
                                        <?php } ?>
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