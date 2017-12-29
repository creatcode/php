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
                    <li><a href="<?php echo $index_action; ?>" data-toggle="tab">消费记录列表</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">合伙人排行</a></li>
                    <li><a href="<?php echo $order_free_chart; ?>" data-toggle="tab">免费单车图表</a></li>
                    <li><a href="<?php echo $order_free_list; ?>" data-toggle="tab">免费单车列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="add_time" value="<?php echo $filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 300px;" placeholder="时间范围"/>
                                <select name="order_state" class="input-sm">
                                    <option value>用户类别</option>
                                    <?php foreach($user_types as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == $filter['order_state'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-sm-4">
                                <table class="table table-bordered table-hover dataTable" role="grid">
                                    <thead>
                                    <tr>
                                        <th colspan="2" class="text-center">总订单数排行（<?php echo $time_horizon; ?>）</th>
                                    </tr>
                                    <tr>
                                        <td>合伙人</td>
                                        <td>订单数</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($all_orders_ranking)) { ?>
                                    <?php foreach ($all_orders_ranking as $v) { ?>
                                    <tr>
                                        <td><?php echo $v['cooperator_name']?></td>
                                        <td><?php echo $v['total']?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-4">
                                <table class="table table-bordered table-hover dataTable" role="grid">
                                    <thead>
                                    <tr>
                                        <th colspan="2" class="text-center">日均订单数排行（<?php echo $time_horizon; ?>）</th>
                                    </tr>
                                    <tr>
                                        <td>合伙人</td>
                                        <td>订单数</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (!empty($daily_orders_ranking)) { ?>
                                    <?php foreach ($daily_orders_ranking as $v) { ?>
                                    <tr>
                                        <td><?php echo $v['cooperator_name']?></td>
                                        <td><?php echo $v['avg_orders']?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-sm-4">
                                <table class="table table-bordered table-hover dataTable" role="grid">
                                    <thead>
                                    <tr>
                                        <th colspan="2" class="text-center">单车使用率排行（<?php echo $time_horizon; ?>）</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr>
                                        <td>合伙人</td>
                                        <td>使用率</td>
                                    </tr>
                                    <?php if (!empty($daily_usage_bicycle_ranking)) { ?>
                                    <?php foreach ($daily_usage_bicycle_ranking as $v) { ?>
                                    <tr>
                                        <td><?php echo $v['cooperator_name']?></td>
                                        <td><?php echo $v['daily_usage']?></td>
                                    </tr>
                                    <?php } ?>
                                    <?php } ?>
                                    </tbody>
                                </table>
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
</script>
<?php echo $footer;?>