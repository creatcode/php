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
                    <li class="active"><a href="javascript:;" data-toggle="tab">统计图表</a></li>
                    <li><a href="<?php echo $index_action; ?>" data-toggle="tab">充值记录列表</a></li>
                   <!--  <li><a href="<?php echo $cooperation_char_url; ?>" data-toggle="tab">合伙人充值统计</a></li> -->
                    <li><a href="<?php echo $card_char_url; ?>" data-toggle="tab">充值卡图表</a></li>
                    <li><a href="<?php echo $card_list_url; ?>" data-toggle="tab">充值卡列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
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
                                 <select name="time_type" id="time_select"  class="input-sm" onchange="addrat()">
                                    <option value="0">选择时间区间</option>
                                    <?php if(!empty($time_type)) {?>
                                    <?php foreach($time_type as $k=>$v){?>
                                    <option value="<?php echo $k?>" <?php echo $k == @$filter['time_type'] ? 'selected' : ''; ?>><?php echo $v?></option>
                                    <?php }?>
                                    <?php }?>
                                </select>
                                <input id="add_time" type="text" name="add_time" value="<?php echo $filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 300px;" placeholder="下单时间"/>
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

    var line = new Morris.Line({
        element: 'balance-chart',
        resize: true,
        data: <?php echo $balanceOrderData; ?>,
  //       data:[
  //   { date: '2008', amount: 20 },
  //   { date: '2009', amount: 10 }
  // ],
        xkey: 'date',
        ykeys: ['amount'],
        labels: ['金额'],
        lineColors: ['#3c8dbc'],
        hideHover: 'auto',
        parseTime: false  
    });

    var line = new Morris.Line({
        element: 'deposit-chart',
        resize: true,
        data: <?php echo $depositOrderData; ?>,
        xkey: 'date',
        ykeys: ['amount'],
        labels: ['金额'],
        lineColors: ['#3c8dbc'],
        hideHover: 'auto',
        parseTime: false
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