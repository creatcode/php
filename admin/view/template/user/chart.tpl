<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo @$lang['t2'];?></span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo @$lang['t3'];?></a></li>
                    <!-- <li><a href="<?php echo $cooperation_chart_url; ?>" data-toggle="tab">合伙人统计图表</a></li> -->
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="region_id" id="region_id" class="input-sm" onchange="show_city(this)">
                                    <option value="">--<?php echo @$lang['t4'];?>--</option>
                                    <?php foreach($filter_regions as $k => $v) { ?>
                                    <option value="<?php echo $v['region_id']; ?>" <?php echo (string)$v['region_id'] == @$filter['region_id'] ? 'selected' : ''; ?>><?php echo $v['region_name']; ?></option>
                                    <?php } ?>
                                </select>
                                <select name="city_id" id="city_id" class="input-sm">
                                    <option value="">--<?php echo @$lang['t5'];?>--</option>
                                    
                                </select>
                                 <select name="time_type" id="time_select"  class="input-sm" onchange="addrat()">
                                    <option value="0"><?php echo @$lang['t6'];?></option>
                                    <?php if(!empty($time_type)) {?>
                                    <?php foreach($time_type as $k=>$v){?>
                                    <option value="<?php echo $k?>" <?php echo $k == @$filter['time_type'] ? 'selected' : ''; ?>><?php echo $v?></option>
                                    <?php }?>
                                    <?php }?>
                                </select>
                                <input type="text" name="add_time" value="<?php echo $filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 300px;" placeholder="<?php echo @$lang['t7'];?>"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;<?php echo @$lang['t8'];?></button>
                                </div>
                            </div>
                        </form>
                        <div class="clearfix">
                            <div class="col-sm-6 col-xs-12">
                                <h4><?php echo @$lang['t9'];?></h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            <?php echo @$lang['t10'];?>:<strong><?php echo $depositOrderTotal; ?></strong><?php echo @$lang['t25'];?>
                                           
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
                            <div class="col-sm-6 col-xs-12">
                                <h4><?php echo @$lang['t14'];?></h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border clearfix" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            <span class="col-xs-12 col-md-6 col-lg-4"><?php echo @$lang['t15'];?>：<strong><?php echo $cashApplyDepositTotal; ?></strong><?php echo @$lang['t25'];?></span>
                                            <span class="col-xs-12 col-md-6"><?php echo @$lang['t16'];?>：<strong><?php echo $cashApplyBalanceTotal; ?></strong><?php echo @$lang['t25'];?></span>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box-body chart-responsive">
                                            <div class="chart" id="cashApplyData-chart" style="height: 300px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <h4><?php echo @$lang['t11'];?></h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            <?php echo @$lang['t12'];?>：<strong><?php echo $balanceOrderTotal; ?></strong><?php echo @$lang['t25'];?>
                                            
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
                            <div class="col-sm-6 col-xs-12">
                                <h4><?php echo @$lang['t17'];?></h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border clearfix" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            <span class="col-sm-3"><?php echo @$lang['t18'];?>：<strong><?php echo $orderAmountTotal; ?></strong><?php echo @$lang['t25'];?></span>
                                            <span class="col-sm-3"><?php echo @$lang['t19'];?>：<strong><?php echo $refundAmountTotal; ?></strong><?php echo @$lang['t25'];?></span>
                                            <span class="col-sm-3"><?php echo @$lang['t20'];?>：<strong><?php echo $ordersTotal; ?></strong></span>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box-body chart-responsive">
                                            <div class="chart" id="order-chart" style="height: 300px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-xs-12">
                                <h4><?php echo @$lang['t23'];?></h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            <?php echo @$lang['t24'];?>：<strong><?php echo $reginAmountTotal; ?></strong><?php echo @$lang['t25'];?>
                                            
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box-body chart-responsive">
                                            <div class="chart" id="regin-chart" style="height: 300px;"></div>
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
        var a='<option value="">--<?php echo @$lang['t5'];?>--</option>';
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
        var a='<option value="">--<?php echo @$lang['t5'];?>--</option>';
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

    var line = new Morris.Line({
        element: 'balance-chart',
        resize: true,
        data: <?php echo $balanceOrderData; ?>,
        xkey: 'date',
        ykeys: ['amount'],
        labels: ["<?php echo @$lang['t13'];?>"],
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
        labels: ["<?php echo @$lang['t13'];?>"],
        lineColors: ['#3c8dbc'],
        hideHover: 'auto',
        parseTime: false  
    });

    //提现
    var line = new Morris.Line({
        element: 'cashApplyData-chart',
        resize: true,
        data: <?php echo $cashApplyData; ?>,
        xkey: 'date',
        ykeys: ['balance','deposit'],
        labels: ["<?php echo @$lang['t26'];?>", "<?php echo @$lang['t27'];?>"],
        lineColors: ['#3c8dbc', '#f56954'],
        hideHover: 'auto',
        parseTime: false  
    });

    //消费
    var line = new Morris.Line({
        element: 'order-chart',
        resize: true,
        data: <?php echo $orderData; ?>,
        xkey: 'date',
        ykeys: ['amount', 'refund', 'number'],
        labels: ["<?php echo @$lang['t21'];?>", "<?php echo @$lang['t22'];?>", "<?php echo @$lang['t20'];?>"],
        lineColors: ['#f56954', '#00a65a', '#3c8dbc'],
        hideHover: 'auto',
        parseTime: false  
    });

    //注册金
    var line = new Morris.Line({
        element: 'regin-chart',
        resize: true,
        data: <?php echo $reginData; ?>,
        xkey: 'date',
        ykeys: ['amount'],
        labels: ["<?php echo @$lang['t24'];?>"],
        lineColors: ['#f56954'],
        hideHover: 'auto',
        parseTime: false  
    });
</script>
<!-- <script type="text/javascript">
    $('#range a,#range1 a,#range2 a,#range3 a').on('click', function(e) {
        e.preventDefault();
        $(this).parent().parent().find('li').removeClass('active');
        $(this).parent().addClass('active');
    });

    $('#range .active a').trigger('click');
    $('#range1 .active a').trigger('click');
    $('#range2 .active a').trigger('click');
    $('#range3 .active a').trigger('click');
</script> -->
<?php echo $footer;?>
