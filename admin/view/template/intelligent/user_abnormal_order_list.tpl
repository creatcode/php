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
                                    <th><?php echo @$lang['t15'];?></th>
                                    <th><?php echo @$lang['t16'];?></th>
                                    <th><?php echo @$lang['t17'];?></th>
                                    <th><?php echo @$lang['t18'];?></th>
                     
                                    <th><?php echo @$lang['t19'];?></th>
                                    <th><?php echo @$lang['t20'];?></th>
                                    <th><?php echo @$lang['t21'];?></th>
                                    <th><?php echo @$lang['t22'];?></th>
                                    <th><?php echo @$lang['t23'];?></th>
                                    <th><?php echo @$lang['t23'];?></th>
                                    <th style="min-width:130px;"><?php echo @$lang['t13'];?></th>
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
             
                                    <td><?php echo $data['region_name']?></td>
                                    <td><?php echo $data['order_state']?></td>
                                    <td><?php echo $data['pay_amount']?></td>
                                    <td><?php echo $data['start_time']?></td>
                                    <td><?php echo $data['end_time']?></td>
                                    <td><?php echo $data['add_time']?></td>
                                    <td><button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i><?php echo @$lang['t14'];?></button></td>
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