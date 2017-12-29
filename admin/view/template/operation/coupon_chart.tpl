<?php

echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>优惠券管理</span>
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
                    <li><a href="<?php echo $list_action; ?>" data-toggle="tab">优惠券列表</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">优惠券使用统计</a></li>
                    <li><a href="<?php echo $cooperation_action; ?>" data-toggle="tab">合伙人优惠券统计</a></li>
                    <li><a href="<?php echo $region_action; ?>" data-toggle="tab">区域优惠券统计</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="coupon_chart">
                        <!-- 新增 -->
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="date" value="<?php echo $filter['date']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 180px;width: 200px;" placeholder="使用时间(默认本月)"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>

                        <div class="clearfix">
                            <div class="col-sm-6 col-xs-12">
                                <h4>优惠券使用情况</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            <span class="col-md-4">发放合计：<strong><?php echo $totalCountSum; ?></strong>张</span>
                                            <span class="">已使用：<strong><?php echo $usedCountSum; ?></strong>张</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box-body chart-responsive">
                                            <div class="chart" id="coupon-chart" style="height: 300px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xs-12">
                                <h4>优惠券类型统计</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            <span class="col-md-4">按时间：<strong><?php echo !empty($countByTypeSum[1]['number_sum']) ? $countByTypeSum[1]['number_sum'] : 0; ?></strong>分钟</span>
                                            <span class="col-md-4">按次数：<strong><?php echo !empty($countByTypeSum[2]['number_sum']) ? $countByTypeSum[2]['number_sum'] : 0; ?></strong>次</span>
                                            <span class="">按金额：<strong><?php echo !empty($countByTypeSum[3]['number_sum']) ? $countByTypeSum[3]['number_sum'] : 0; ?></strong>元</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box-body chart-responsive">
                                            <div class="chart" id="coupon-type-chart" style="height: 300px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-sm-6 col-xs-12">
                                <h4>优惠券来源统计</h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            <span class="col-md-4">发放合计：<strong><?php echo $totalCountSum; ?></strong>张</span>
                                            <span class="">已使用：<strong><?php echo $usedCountSum; ?></strong>张</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box-body chart-responsive">
                                            <div class="chart" id="coupon-obtain-chart" style="height: 300px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                        
                        
                        
                        <div class="panel panel-success">
                            <div class="panel-heading">优惠券总量</div>
                            <div class="panel-body panel-body-height">
                                <span class="col-md-2">总张数：<strong><?php echo $couponSum['countAddTotal']['count']; ?></strong>张</span>
                                <span class="col-md-2 text-green">已使用：<strong><?php echo $couponSum['countUsedTotal']['count']; ?></strong>张</span>
                                <span class="col-md-2 text-gray">已失效：<strong><?php echo $couponSum['countFailTotal']['count']; ?></strong>张</span>
                                <span class="col-md-2 text-danger">可用(库存)：<strong><?php echo $couponSum['countAddTotal']['count'] - $couponSum['countFailTotal']['count'] - $couponSum['countUsedTotal']['count']; ?></strong>张</span>
                                <span class="col-md-2">今日发放：<strong><?php echo $couponSum['countAddToday']['count']; ?></strong>张</span>
                                <span class="">今日已使用：<strong><?php echo $couponSum['countUsedToday']['count']; ?></strong>张</span>
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
            customRangeLabel:'自定义'
        },
        ranges : {
            '本月': [moment().startOf('month'), moment()],
            '今日': [moment(), moment()],
            '本周': [moment().startOf('week'), moment()],
            '今年': [moment().startOf('year'), moment()],
            '最近7日': [moment().subtract('days', 6), moment()],
            '最近30日': [moment().subtract('days', 29), moment()],
            '全部': ['2016-01-01', moment()] //起始日期别设置太小
        }
    });
 
    var line = new Morris.Line({
        element: 'coupon-chart',
        resize: true,
        data: <?php echo json_encode($countByDay); ?>,
        xkey: 'date',
        ykeys: ['total_count', 'used_count'],
        labels: ['发券张数', '使用张数'],
        lineColors: ['#3c8dbc','#f56954'],
        hideHover: 'auto'
    });
    
    var bar = new Morris.Bar({
        element: 'coupon-type-chart',
        resize: true,
        data: <?php echo json_encode($countByType); ?>,
        xkey: 'coupon_type',
        ykeys: ['total_count', 'used_count', 'fail_count'],
        labels: ['发券张数', '使用张数', '失效张数'],
        barColors: ['#3c8dbc','#f56954','#d2d6de'],
        //barColors: ['#edc240', '#cb4b4b', '#9440ed'],
        hideHover: 'auto'
    });
    
    var bar2 = new Morris.Bar({
        element: 'coupon-obtain-chart',
        resize: true,
        data: <?php echo json_encode($countByObtain); ?>,
        xkey: 'obtain',
        ykeys: ['total_count', 'used_count', 'fail_count'],
        labels: ['发券张数', '使用张数', '失效张数'],
        barColors: ['#3c8dbc','#f56954','#d2d6de'],
        //barColors: ['#edc240', '#cb4b4b', '#9440ed'],
        hideHover: 'auto'
    });

</script>
<style>#content-wrapper{height:100%};</style>
<?php echo $footer;?>