<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo @$lang['t21'];?></span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo @$lang['t22'];?></a></li>
                    <!--<li class=""><a href="<?php echo $lock_action; ?>" data-toggle="tab">车锁管理</a></li>-->
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="time_type" id="time_select"  class="input-sm" onchange="addrat()">
                                    <option value="0"><?php echo @$lang['t23'];?></option>
                                    <?php if(!empty($time_type)) {?>
                                    <?php foreach($time_type as $k=>$v){?>
                                    <option value="<?php echo $k?>" <?php echo $k == @$filter['time_type'] ? 'selected' : ''; ?>><?php echo $v?></option>
                                    <?php }?>
                                    <?php }?>
                                </select>

                                <input type="text" name="search_time" value="<?php echo $filter['search_time'];?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 300px;width: 200px;" placeholder="<?php echo @$lang['t25'];?>"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;<?php echo @$lang['t23'];?></button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">

                            <button class="btn btn-default btn-sm" data-toggle="modal" data-target="#myModal"><i class="fa fa-download"></i>&nbsp;<?php echo @$lang['t26'];?></button>

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
                            <table class="table table-bordered table-hover dataTable" role="grid">
                                <thead>
                                    <tr>
                                        <!--<th style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']:enabled').prop('checked', this.checked);"></th>-->
                                        <?php foreach ($data_columns as $column) { ?>
                                        <th><?php echo $column['text']; ?></th>
                                        <?php } ?>
                                        <th style="min-width:130px;"><?php echo @$lang['t15'];?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data_rows as $data) { ?>
                                    <tr>
                                        <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['bicycle_id']?>"></td>-->
                                        <td><?php echo $data['bicycle_sn']?></td>
                                        <td><?php echo $data['lock_sn']?></td>

            
                                        <td><?php echo $data['lbs_times']?></td>
                                        <td><?php echo number_format(abs(($data['etime']-$data['stime'])/60),2);?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i><?php echo @$lang['t16'];?></button>
                                                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a href="<?php echo $data['edit_action']; ?>"><?php echo @$lang['t17'];?></a></li>
                                                    <li><a href="<?php echo $data['delete_action']; ?>"><?php echo @$lang['t18'];?></a></li>
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
<!-- /.content -->
<div class="modal fade" id="myModal">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">提示</h4>
            </div>
            <div class="modal-body  text-center">
                <p>确认导出？</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">取消</button>
                <button id="export" class="btn btn-primary" form="search_form" formmethod="post" formaction="<?php echo $export_action; ?>">导出</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<script type="text/javascript">
    $("#export").click(function () {
        $('#myModal').modal('hide')
    });
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

</script>
<?php echo $footer;?>