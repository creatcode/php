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
                    <li ><a href="<?php echo $cashapply_chart; ?>" data-toggle="tab">统计图表</a></li>
                    <li><a href="<?php echo $index_action; ?>" data-toggle="tab">提现列表</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">合伙人提现统计</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="add_time" value="<?php echo $filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 300px;" placeholder="下单时间"/>
                                <select name="cooperator_id" style="height: 30px; margin-left: 8px; width:160px; ">
                                    <option value="0">其他</option>
                                    <?php foreach($cooperList as $v){
                                        if($cooperator_id ==$v['cooperator_id']){
                                    ?>
                                    <option selected="selected" value="<?php echo $v['cooperator_id']; ?>"><?php echo $v['cooperator_name']; ?> </option>
                                    <?php
                                        }else{
                                    ?>
                                    <option value="<?php echo $v['cooperator_id']; ?>"><?php echo $v['cooperator_name']; ?> </option>
                                    <?php }
                                     } ?>
                                </select>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="dataTables_length fa-border clearfix" style="margin: 10px 0; padding: 10px; background: #76767f; color: white;">
                                    <span class="col-sm-5 col-lg-2">押金总计：<strong><?php echo $depositTotal; ?></strong>元</span>
                                    <span class="col-sm-5">余额总计：<strong><?php echo $balanceTotal; ?></strong>元</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box-body chart-responsive">
                                    <div class="chart" id="line-chart" style="height: 300px;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<script type="text/javascript">
    $('.date-range').daterangepicker({
        "showDropdowns": true,
        // "minDate":'2016/01/01',
        // "maxDate":'2017/12/13',
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
            applyLabel: '确认',
            cancelLabel: '取消',
            fromLabel : '起始时间',
            toLabel : '结束时间',
            customRangeLabel : '自定义',
            firstDay : 1
        },
        ranges : {
            //'最近1小时': [moment().subtract('hours',1), moment()],
            '本日': [moment().startOf('day'), moment()],
            // '昨日': [moment().subtract('days', 1).startOf('day'), moment().subtract('days', 1).endOf('day')],
            // '最近7日': [moment().subtract('days', 6), moment()],
            // '最近30日': [moment().subtract('days', 29), moment()],
            '本周': [moment().startOf("week"),moment().endOf("week")],
            '本季度': [moment().startOf("quarter"),moment().endOf("quarter")],
            '本月': [moment().startOf("month"),moment().endOf("month")],
            '上个月': [moment().subtract(1,"month").startOf("month"),moment().subtract(1,"month").endOf("month")],
            '本年度': [moment().startOf("year"),moment().endOf("year")]
            
        }
    });

    $("#filter_type").change(function() {
        $("#filter_text").attr("name", $(this).val());
    });

    // LINE CHART
    var line = new Morris.Line({
        element: 'line-chart',
        resize: true,
        data: <?php echo $orderData; ?>,
        xkey: 'date',
        ykeys: ['balance','deposit'],
        labels: ['余额', '押金'],
        lineColors: ['#3c8dbc', '#f56954'],
        hideHover: 'auto'
    });
</script>
<?php echo $footer;?>