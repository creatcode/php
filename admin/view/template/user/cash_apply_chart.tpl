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
                    <li class="active"><a href="javascript:;" data-toggle="tab">统计图表</a></li>
                    <li><a href="<?php echo $index_action; ?>" data-toggle="tab">提现列表</a></li>
                    <!-- <li><a href="<?php echo $cooperation_cashapply_url; ?>" data-toggle="tab">合伙人提现统计</a></li> -->
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;">
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
                                 <select name="time_type" id="time_select"  class="input-sm" onchange="addrat()">
                                    <option value="0">选择时间区间</option>
                                    <?php if(!empty($time_type)) {?>
                                    <?php foreach($time_type as $k=>$v){?>
                                    <option value="<?php echo $k?>" <?php echo $k == @$filter['time_type'] ? 'selected' : ''; ?>><?php echo $v?></option>
                                    <?php }?>
                                    <?php }?>
                                </select>
                                <input type="text" name="add_time" value="<?php echo $filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 300px;" placeholder="下单时间"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="dataTables_length fa-border clearfix" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                    <span class="col-sm-5 col-lg-2">押金总计：<strong><?php echo $depositTotal; ?></strong>元</span>
                                    <span class="col-sm-5">余额总计：<strong><?php echo $balanceTotal; ?></strong>元</span>
                                    <!-- <div class="pull-right">
                                                <a href="#" style="color: #fff;" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-calendar"></i> <i class="caret"></i></a>
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
<!-- <script type="text/javascript">
    $('#range a').on('click', function(e) {
        e.preventDefault();
        $(this).parent().parent().find('li').removeClass('active');
        $(this).parent().addClass('active');
    });

    $('#range .active a').trigger('click');
</script> -->

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