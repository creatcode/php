<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo isset($title) ? $title : '用户异常订单记录'; ?></span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo isset($title) ? $title : '用户异常订单记录'; ?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <!-- 新增 -->
                        <form id="table_form" class="table_form" method="post">
                            <table class="table table-bordered table-hover dataTable" role="grid">
                                <thead>
                                <tr>
                                    <th>订单sn</th>
                                    <th>锁sn</th>
                                    <th>单车sn</th>
                                    <th>手机号</th>
                                    <th>合伙人</th>
                                    <th>区域</th>
                                    <th>状态</th>
                                    <th>实付金额</th>
                                    <th>开始时间</th>
                                    <th>结束时间</th>
                                    <th>下单时间</th>
                                    <th style="min-width:130px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['order_id']?>"></td>-->
                                    <td><?php echo $data['order_sn']?></td>
                                    <td><?php echo $data['lock_sn']?></td>
                                    <td><?php echo $data['bicycle_sn']?></td>
                                    <td><?php echo $data['user_name']?></td>
                                    <td><?php echo $data['cooperator_name']?></td>
                                    <td><?php echo $data['region_name']?></td>
                                    <td><?php echo $data['order_state']?></td>
                                    <td><?php echo $data['pay_amount']?></td>
                                    <td><?php echo $data['start_time']?></td>
                                    <td><?php echo $data['end_time']?></td>
                                    <td><?php echo $data['add_time']?></td>
                                    <td><button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button></td>
                                </tr>
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

<script type="text/javascript">
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