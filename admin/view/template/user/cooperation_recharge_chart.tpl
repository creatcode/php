<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>充值管理</span>
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
                    <li ><a href="<?php echo $char_url; ?>" data-toggle="tab">统计图表</a></li>
                    <li><a href="<?php echo $index_action; ?>" data-toggle="tab">充值记录列表</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">合伙人统计图表</a></li>
                    <li><a href="<?php echo $card_char_url; ?>" data-toggle="tab">充值卡图表</a></li>
                    <li><a href="<?php echo $card_list_url; ?>" data-toggle="tab">充值卡列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;">
                                <input type="text" name="add_time" value="<?php echo $filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 300px;width: 200px;" placeholder="下单时间"/>
                                <select name="cooperator_id" style="height: 30px; margin-left: 8px; width:160px; color: #0b0b0b; ">
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
                        <div class="clearfix">
                            <div class="col-sm-6">
                                <h4>余额充值</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            余额充值总计：<strong><?php echo $balanceOrderTotal; ?></strong>元
                                            <!-- <div class="pull-right">
                                                <a href="#" style="color: #fff;" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i  class="fa fa-calendar"></i> <i class="caret"></i></a>
                                                <ul id="range" class="dropdown-menu dropdown-menu-right">
                                                    
                                                    <li class="active"><a href="#">年</a></li>
                                                    <li class=""><a href="#">月</a></li>
                                                    <li class=""><a href="#">季</a></li>
                                                    <li class=""><a href="#">周</a></li>
                                                    <li class=""><a href="#">日</a></li>
                                                </ul>
                                            </div> -->
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box-body chart-responsive">
                                            <div class="chart" id="balance-chart" style="height: 300px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <h4>押金充值</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            押金充值总计：<strong><?php echo $depositOrderTotal; ?></strong>元
                                        </div>
                                        
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box-body chart-responsive">
                                            <div class="chart" id="deposit-chart" style="height: 300px;"></div>
                                        </div>
                                    </div>
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

    var line = new Morris.Line({
        element: 'balance-chart',
        resize: true,
        data: <?php echo $balanceOrderData; ?>,
        xkey: 'date',
        ykeys: ['amount'],
        labels: ['金额'],
        lineColors: ['#3c8dbc'],
        hideHover: 'auto'
    });

    var line = new Morris.Line({
        element: 'deposit-chart',
        resize: true,
        data: <?php echo $depositOrderData; ?>,
        xkey: 'date',
        ykeys: ['amount'],
        labels: ['金额'],
        lineColors: ['#3c8dbc'],
        hideHover: 'auto'
    });
</script>
<!-- <script type="text/javascript">
    $('#range a,#range1 a').on('click', function(e) {
        e.preventDefault();
        $(this).parent().parent().find('li').removeClass('active');
        $(this).parent().addClass('active');
    });

    $('#range .active a').trigger('click');
    $('#range1 .active a').trigger('click');
</script> -->
<?php echo $footer;?>