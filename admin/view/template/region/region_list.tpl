<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>区域管理</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">区域列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="region">
                        <!-- 新增 -->
                        <div class="form-group">
                            <a href="<?php echo $add_action; ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;新增</a>
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
                            <table class="table table-bordered table-hover dataTable tree" role="grid">
                                <thead>
                                <tr>
                                    <?php foreach ($data_columns as $column) { ?>
                                    <th><?php echo $column['text']; ?></th>
                                    <?php } ?>
                                    <th style="min-width:130px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php $tree_index = 0; ?>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr class="treegrid-<?php  echo $tree_index++; ?>  " >
                                   
                                    <td>
                                        <?php echo $data['region_name']?> <span class="fa fa-plus get-city" data-index="<?php echo $tree_index-1; ?>" data-region="<?php echo $data['region_id']; ?>"></span>
                                    </td>
                                    <td></td>
                                    <td></td>
                                    <!--<td>每<?php echo $data['region_charge_time']?>分钟<?php echo $data['region_charge_fee']?>元</td>-->
                                    <td></td>
                                    <td><?php echo $data['bike_total']?></td>
                                    <!--<td><?php echo $data['lock_total']?></td>-->
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $data['edit_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-pencil"></i>编辑</button>
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo $data['delete_action']; ?>">删除</a></li>
                                                <li><a href="<?php echo $yunyin_action; ?>">运营设置</a></li>
                                            </ul>
                                        </div>
                                    </td>
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

<style type="text/css">
    .fa-plus{
        cursor:pointer;
        color:darkmagenta;
    }
</style>

<!-- /.content -->
<link rel="stylesheet" href="<?php echo HTTP_IMAGE;?>AdminLTE-2.3.7/plugins/treegrid/css/jquery.treegrid.css" />
<script type="text/javascript" src="<?php echo HTTP_IMAGE;?>AdminLTE-2.3.7/plugins/treegrid/js/jquery.treegrid.js" ></script>

<script type="text/javascript">
    $(document).ready(function () {
        var global_tree_index = <?php echo $tree_index; ?>;
        var city_page = {};
        var station_page = {};
        $(".tree").delegate(".get-city","click",function(){
            var tree_index = $(this).data('index');
            var region_id = $(this).data('region');
            var url = "<?php echo $city_action; ?>";
            var that = this;

            if(city_page[region_id] == undefined){
                city_page[region_id] = 1;
            }else{
                city_page[region_id]++;
            }

            $.post(url,{region_id:region_id,page:city_page[region_id]},function(response,status){
                var data  = response.data;
                if(data.length){
                    for(var i = 0;i < data.length;i++ ){
                        $(that).parent().parent().after("<tr class=\"treegrid-"+global_tree_index+" treegrid-parent-"+tree_index+"\">" +
                            "<td></td>" +
                            "<td></td>" +
                            "<td>"+data[i]["city_name"]+"<span class=\"fa fa-plus get-station\" data-index=\""+global_tree_index+"\" data-city=\""+data[i]["city_id"]+"\"></span></td>" +
                            "<td></td>" +
                            "<td>"+data[i]["total_bicycle"]+"</td>" +
                            "<td>" +
                                "<div class=\"btn-group\">"+
                                "<button data-url=\"<?php echo $data['edit_action']; ?>\" type=\"button\" class=\"btn btn-info link\"><i class=\"fa fa-fw fa-pencil\"></i>编辑</button>"+
                                "<button type=\"button\" class=\"btn btn-info dropdown-toggle\" data-toggle=\"dropdown\">"+
                                "<span class=\"caret\"></span>"+
                                "<span class=\"sr-only\">Toggle Dropdown</span>"+
                                "</button>"+
                                "<ul class=\"dropdown-menu\" role=\"menu\">"+
                                "<li><a href=\"<?php echo $data['delete_action']; ?>\">删除</a></li>"+
                                "</ul>"+
                                "</div>"+
                            "</td>"+
                            "</tr>");
                        global_tree_index++;
                    }
                    $(".tree").treegrid();
                }else{
                    alert("没有了");
                    //去掉 "+" 符号
                }
                if(data.length < 10){
                    $(that).remove();
                }

            },'JSON');
        });

        $(".tree").delegate(".get-station","click",function(){
            var tree_index = $(this).data('index');
            var city_id = $(this).data('city');
            var url = "<?php echo $station_action; ?>";
            var that = this;

            if(station_page[city_id] == undefined){
                station_page[city_id] = 1;
            }else{
                station_page[city_id]++;
            }

            $.post(url,{city_id:city_id,page:station_page[city_id]},function(response,status){
                var data  = response.data;
                if(data.length){
                    for(var i = 0;i < data.length;i++ ){
                        $(that).parent().parent().after("<tr class=\"treegrid-"+global_tree_index+" treegrid-parent-"+tree_index+"\">"+
                            "<td></td>"+
                            "<td></td>"+
                            "<td></td>"+
                            "<td>"+data[i]["station_sn"]+"</td>"+
                            "<td>"+data[i]["used"]+"</td>" +
                            "<td>" +
                                "<div class=\"btn-group\">"+
                                    "<button data-url=\"<?php echo $data['edit_action']; ?>\" type=\"button\" class=\"btn btn-info link\"><i class=\"fa fa-fw fa-pencil\"></i>编辑</button>"+
                                    "<button type=\"button\" class=\"btn btn-info dropdown-toggle\" data-toggle=\"dropdown\">"+
                                    "<span class=\"caret\"></span>"+
                                    "<span class=\"sr-only\">Toggle Dropdown</span>"+
                                    "</button>"+
                                    "<ul class=\"dropdown-menu\" role=\"menu\">"+
                                    "<li><a href=\"<?php echo $data['delete_action']; ?>\">删除</a></li>"+
                                    "</ul>"+
                                "</div>"+
                            "</td>"+
                            "</tr>");
                        global_tree_index++;
                    }
                    $(".tree").treegrid();
                }else{
                    alert("没有数据");
                }
                if(data.length < 10){
                    $(that).remove();
                }

            },'JSON');
        });
        //$(".tree").treegrid();
    });

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