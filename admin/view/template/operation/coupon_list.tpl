<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>优惠券管理</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">优惠券列表</a></li>
                    <!--<li><a href="<?php echo $chart_action; ?>" data-toggle="tab">优惠券使用统计</a></li>
                    <li><a href="<?php echo $cooperation_action; ?>" data-toggle="tab">合伙人优惠券统计</a></li>-->
                    <li><a href="<?php echo $region_action; ?>" data-toggle="tab">区域优惠券统计</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="coupon_list">
                        <!-- 新增 -->
                        <div class="form-group">
                            <a href="<?php echo $add_action; ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;新增</a>
                            <!--<button class="btn btn-default btn-sm button-upload" data-action="<?php echo $import_action; ?>"><i class="fa fa-upload"></i>&nbsp;导入</button>
                            <button class="btn btn-default btn-sm" form="table_form" formaction="<?php echo $export_action; ?>"><i class="fa fa-download"></i>&nbsp;导出</button>-->
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
                                    <?php foreach ($data_columns as $column) { ?>
                                    <th><?php echo $column['text']; ?></th>
                                    <?php } ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <td><?php echo $data['mobile']?></td>
                                    <!--<td><?php echo $data['coupon_code']?></td>-->
                                    <td><?php echo $data['coupon_name']?></td>
                                    <td><?php echo $data['used']?></td>
                                    <td><?php echo $data['admin_name']?></td>
									<td>后台发放</td>
                                    <td><?php echo $data['effective_time']?></td>
                                    <td><?php echo $data['failure_time']?></td>
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
<?php echo $footer;?>