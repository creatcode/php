<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>财务报表</span>
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
                    <li><a href="<?php echo $month_report_action; ?>" data-toggle="tab">财务月报表</a></li>
                    <li><a href="<?php echo $day_report_action; ?>" data-toggle="tab">财务日报表</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">财务汇总表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="search_time" value="<?php echo $filter['search_time']; ?>" class="input-sm date-month" style="border: 1px solid #a9a9a9; width: 200px;" placeholder="时间范围"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
                            <!--<button class="btn btn-default btn-sm button-upload" data-action="<?php echo $import_action; ?>"><i class="fa fa-upload"></i>&nbsp;导入</button>-->
                            <button class="btn btn-default btn-sm" form="search_form" formmethod="post" formaction="<?php echo $export_action; ?>"><i class="fa fa-download"></i>&nbsp;导出</button>
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
                            <table class="table table-bordered table-hover finance-table" role="grid">
                                <thead>
                                <tr>
                                    <th width="20%">项目</th>
                                    <?php if (isset($titles) && is_array($titles) && !empty($titles)) { ?>
                                    <?php foreach($titles as $title) { ?>
                                    <th><?php echo $title; ?></th>
                                    <?php } ?>
                                    <?php } ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php if (isset($list) && is_array($list) && !empty($list)) { ?>
                                <?php foreach($list as $row) { ?>
                                <tr>
                                    <?php foreach($row as $item) { ?>
                                    <td><?php echo $item; ?></td>
                                    <?php } ?>
                                </tr>
                                <?php } ?>
                                <?php } ?>
                                </tbody>
                            </table>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
<style type="text/css">
    .finance-table tr:nth-child(3) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(4) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(6) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(7) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(8) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
    .finance-table tr:nth-child(9) td:nth-child(1){color: #a5a5a5; text-indent: 1em;}
</style>
<script type="text/javascript">
    $(".finance-table tr").eq(2).hide();
    $(".finance-table tr").eq(3).hide();
    $(".finance-table tr").eq(4).hide();
    $(".finance-table tr").eq(5).hide();
    $(".finance-table tr").eq(6).hide();
    $(".finance-table tr").eq(7).hide();
    $('.date-month').daterangepicker({
        showDropdowns : true,
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