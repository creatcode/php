<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo @$lang['t14'];?></span>
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
                    <li ><a href="<?php echo $user_rating_action; ?>" data-toggle="tab"><?php echo @$lang['t15'];?></a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo @$lang['t16'];?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                 <select name="time_type" id="time_select"  class="input-sm" onchange="addrat()">
                                    <option value="0"><?php echo @$lang['t9'];?></option>
                                    <?php if(!empty($time_type)) {?>
                                    <?php foreach($time_type as $k=>$v){?>
                                    <option value="<?php echo $k?>" <?php echo $k == @$filter['time_type'] ? 'selected' : ''; ?>><?php echo $v?></option>
                                    <?php }?>
                                    <?php }?>
                                </select>
                                <input type="text" name="add_time" value="<?php echo isset($filter_add_time) ? $filter_add_time : ''; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 200px;" placeholder="<?php echo @$lang['t22'];?>"/>

                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;<?php echo @$lang['t17'];?></button>
                                </div>
                            </div>
                        </form>
                        <div class="clearfix">
                            <div class="col-sm-6 col-xs-12">
                                <h4><?php echo @$lang['t18'];?></h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            <?php echo @$lang['t19'];?>：<strong><?php echo $total; ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box-body chart-responsive">
                                            <div class="chart" id="sales-chart" style="height: 300px;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-sm-6 col-xs-12">
                                <h4><?php echo @$lang['t20'];?></h4>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px;background: #76767f; color: white;">
                                            <?php echo @$lang['t21'];?>：<strong><?php echo $label_total; ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box-body chart-responsive">
                                            <div class="chart" id="sales-chart1" style="height: 300px;"></div>
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

    //DONUT CHART
    var donut = new Morris.Donut({
        element: 'sales-chart',
        resize: true,
        colors: ["#3c8dbc", "#f56954", "#00a65a" ,"#3c8dbc", "#f56954", "#00a65a","#3c8dbc", "#f56954", "#00a65a","#3c8dbc", "#f56954", "#00a65a","#3c8dbc", "#f56954"],
        data: <?php echo $total ? $data : "[{'label':'','value':''}]" ?>,
        hideHover: 'auto'
    });


    //DONUT CHART
    var donut = new Morris.Donut({
        element: 'sales-chart1',
        resize: true,
        colors: ["#3c8dbc", "#f56954", "#00a65a" ,"#3c8dbc", "#f56954", "#00a65a","#3c8dbc", "#f56954", "#00a65a","#3c8dbc", "#f56954", "#00a65a","#3c8dbc", "#f56954"],
        data: <?php echo $label_total ? $label_data : "[{'label':'','value':''}]" ?>,
    hideHover: 'auto'
    });

</script>
<?php echo $footer;?>
