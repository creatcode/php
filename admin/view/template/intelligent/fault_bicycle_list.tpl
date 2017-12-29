<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>智能分析</span>
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
                    <li><a href="<?php echo $chart_url; ?>"  data-toggle="tab">单车故障饼图</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">故障单车次数统计</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="processed" id="processed" class="input-sm">
                                    <option value="-1">处理状态</option>
                                    <option <?php echo $filter['processed'] == '1' ? 'selected' : ''; ?> value="1">已处理</option>
                                    <option <?php echo $filter['processed'] == '0' ? 'selected' : ''; ?> value="0">未处理</option>
                                </select>
                                <select name="fault_type_id" id="fault_type_id" class="input-sm">
                                    <option value="">故障类型</option>
                                    <?php foreach($fault_type_list as $key => $val) { ?>
                                    <option value="<?php echo $val['fault_type_id']; ?>" <?php echo $val['fault_type_id'] == $filter['fault_type_id'] ? 'selected' : ''; ?>><?php echo $val['fault_type_name']; ?></option>
                                    <?php } ?>
                                </select>
								<input type="text" name="add_time" value="<?php echo $filter['add_time'];?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 300px;width: 200px;" placeholder="报障时间"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
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
                                    <th>单车编号</th>
                                    <th>故障数</th>
                                    <th style="min-width:130px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <td><?php echo $data['bicycle_sn'] ?></td>
                                    <td><?php echo $data['fault_num'] ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>
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
<script type="text/javascript">
    $("#export").click(function() {
        $('#myModal').modal('hide')
    });
    $('.date-range').daterangepicker({
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false
        }
    });

</script>
<?php echo $footer;?>