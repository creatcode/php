<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>充值优惠设置（针对所有区域）列表</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
    <?php echo $statistics_in_page_header;?>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="nav-tabs-custom">
            <!-- tab 标签 -->
            <ul class="nav nav-tabs">
                <li class="active"><a href="javascript:;" data-toggle="tab">充值优惠列表</a></li>
            </ul>
            <div class="tab-content">
                <div class="tab-pane active" id="bicycle">
                    <!-- 新增 -->
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
                                <th style="min-width:130px;">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($data_rows as $data) { ?>
                            <tr>
                                <td class="open-marker" data-bicycle-id="<?php echo $data['bicycle_id']?>" title="充值金额<?php echo $data['recharge_amount'] ?>"><?php echo $data['recharge_amount']?></td>
                                <td><?php echo $data['present_amount']?></td>
                                <td><?php echo $data['start_time']?></td>
                                <td><?php echo $data['end_time']?></td>
                                <td><?php echo $data['state']?></td>
                                <td>
                                    <div class="btn-group">
                                        <button data-url="<?php echo $data['edit_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>编辑</button>
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
</section>
<!-- /.content -->
<?php echo $footer;?>