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
                    <li class="active"><a href="javascript:;" data-toggle="tab">统计图表</a></li>
                    <li><a href="<?php echo $index_action; ?>" data-toggle="tab">消费记录列表</a></li>
                    <!-- <li><a href="<?php echo $order_free_chart; ?>" data-toggle="tab">免费单车图表</a></li> -->
                    <li><a href="<?php echo $order_free_list; ?>" data-toggle="tab">免费单车列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="add_time" value="<?php echo $filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 300px;" placeholder="下单时间"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="dataTables_length fa-border clearfix" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                    <span class="col-sm-4 col-lg-2">消费总计：<strong><?php echo $orderAmountTotal; ?></strong>元</span>
                                    <span class="col-sm-4 col-lg-2">退回总计：<strong><?php echo $refundAmountTotal; ?></strong>元</span>
                                    <span class="col-sm-4">订单数：<strong><?php echo $ordersTotal; ?></strong></span>
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
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
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
        ykeys: ['amount', 'refund', 'number'],
        labels: ['消费金额', '退回金额', '订单数'],
        lineColors: ['#f56954', '#00a65a', '#3c8dbc'],
        hideHover: 'auto'
    });
</script>
<?php echo $footer;?>