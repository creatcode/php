<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>提现管理</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">提现列表</a></li>
                    <!-- <li><a href="<?php echo $cooperation_cashapply_url; ?>" data-toggle="tab">合伙人提现统计</a></li> -->
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
                                <select name="pdc_type" class="input-sm">
                                    <option value>退款类型</option>
                                    <?php foreach($cashapply_types as $k => $v) { ?>
                                    <option value="<?php echo $k;?>" <?php echo (string)$k == $filter['pdc_type'] ? 'selected' : ''; ?>><?php echo $v;?></option>
                                    <?php }?>
                                </select>
                                <select name="pdc_payment_code" class="input-sm">
                                    <option value>支付方式</option>
                                    <?php foreach ($payment_codes as $payment_code) { ?>
                                    <option value="<?php echo $payment_code['code'];?>" <?php echo (string)$payment_code['code'] == $filter['pdc_payment_code'] ? 'selected' : ''; ?>><?php echo $payment_code['text']; ?></option>
                                    <?php } ?>
                                </select>
                                <select name="pdc_payment_type" class="input-sm">
                                    <option value>支付途径</option>
                                    <?php foreach($payment_types as $k => $v) { ?>
                                    <option value="<?php echo $k;?>" <?php echo (string)$k == $filter['pdc_payment_type'] ? 'selected' : ''; ?>><?php echo $v;?></option>
                                    <?php }?>
                                </select>
                                <select name="pdc_payment_state" class="input-sm">
                                    <option value>提现支付状态</option>
                                    <?php foreach($payment_states as $state) { ?>
                                    <option value="<?php echo $state['value'];?>" <?php echo (string)$state['value'] == $filter['pdc_payment_state'] ? 'selected' : ''; ?>><?php echo $state['text'];?></option>
                                    <?php }?>
                                </select>
                                 <select name="time_type" id="time_select"  class="input-sm" onchange="addrat()">
                                    <option value="0">选择时间区间</option>
                                    <?php if(!empty($time_type)) {?>
                                    <?php foreach($time_type as $k=>$v){?>
                                    <option value="<?php echo $k?>" <?php echo $k == @$filter['time_type'] ? 'selected' : ''; ?>><?php echo $v?></option>
                                    <?php }?>
                                    <?php }?>
                                </select>
                                <input type="text" name="pdc_payment_time" value="<?php echo $filter['pdc_payment_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 200px;" placeholder="支付时间"/>

                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
                           <!-- <button class="btn btn-default btn-sm button-upload" data-action="<?php echo $import_action; ?>"><i class="fa fa-upload"></i>&nbsp;导入</button>-->
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
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['pdc_id']?>"></td>-->
                                    <td><?php echo $data['pdc_sn']?></td>
                                    <td><?php echo $data['pdc_user_name']?></td>
                                    <td><?php echo $data['pdc_type_name']?></td>
                                    <td><?php echo $data['pdr_sn']?></td>
                                    <td><?php echo $data['pdc_amount']?></td>
                                    <td><?php echo $data['pdc_payment_name']?></td>
                                    <td><?php echo $data['pdc_payment_type']?></td>
                                    <td><?php echo $data['pdc_payment_time']?></td>
                                    <td><?php echo $data['pdc_payment_state_text']?></td>
                                    <td><button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>
                                    <?php if($data['pdc_payment_state'] == '0' && $data['pdc_type'] == 1){ ?>
                                    <button data-url="<?php echo $data['apply_deposit_action']; ?>" type="button" class="btn btn-primary link"><i class="fa fa-fw fa-exchange"></i>押金退款申请</button>
                                    <?php } ?>
									<!-- add vincent :2017-08-10 转账申请[目前仅支持支付宝] -->
                                    <?php if($data['pdc_payment_state'] == '4' && $data['pdc_payment_code']=='alipay'){ ?>
                                    <button data-url="<?php echo $data['apply_trans_action']; ?>" type="button" class="btn btn-primary link"><i class="fa fa-fw fa-exchange"></i>转账申请</button>
                                    <?php } ?>
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
</script>
<?php echo $footer;?>
