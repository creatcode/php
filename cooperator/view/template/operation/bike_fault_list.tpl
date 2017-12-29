<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>故障单车列表</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">故障单车列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="filter_type" id="filter_type" class="input-sm">
                                    <?php if (!empty($filter_types) && is_array($filter_types)) { ?>
                                    <?php foreach($filter_types as $key => $val) { ?>
                                    <option value="<?php echo $key; ?>" <?php echo (string)$key == $filter_type ? 'selected' : ''; ?>><?php echo $val; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                                <input type="text" name="<?php echo $filter_type; ?>" value="<?php echo isset($filter[$filter_type]) ? $filter[$filter_type] : ''; ?>" id="filter_text" class="input-sm" style="border: 1px solid #a9a9a9;"/>
                                <select class="input-sm" name="lock_type">
                                    <option value>锁类型</option>
                                    <option <?php echo $filter['lock_type'] == 1 ? 'selected' : ''?> value="1">GPRS</option>
                                    <option <?php echo $filter['lock_type'] == 2 ? 'selected' : ''?> value="2">杭州蓝牙锁</option>
                                    <option <?php echo $filter['lock_type'] == 3 ? 'selected' : ''?> value="3">机械</option>
                                    <option <?php echo $filter['lock_type'] == 4 ? 'selected' : ''?> value="4">GPRS+蓝牙</option>
                                    <option <?php echo $filter['lock_type'] == 5 ? 'selected' : ''?> value="5">深圳蓝牙锁</option>
                                </select>
                                <select class="input-sm" name="lost_time">
                                    <option value>失联时间</option>
                                    <option <?php echo $filter['lost_time'] == 1 ? 'selected' : ''?> value ='1'>失联大于1小时</option>
                                    <option <?php echo $filter['lost_time'] == 24 ? 'selected' : ''?> value ='24'>大于24小时</option>
                                </select>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
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
                            <table class="table table-bordered table-hover dataTable" role="grid">
                                <thead>
                                <tr>
                                    <!--<th style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']:enabled').prop('checked', this.checked);"></th>-->
                                    <?php foreach ($data_columns as $column) { ?>
                                    <th><?php echo $column['text']; ?></th>
                                    <?php } ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['parking_id']?>"></td>-->
                                    <td><?php echo $data['bicycle_sn']?></td>
                                    <td><?php echo $data['cooperator_name']?></td>
                                    <td><?php echo $data['lock_sn']?></td>
                                    <td><?php echo $data['lock_type']?></td>
                                    <td><?php echo $data['battery']?></td>
                                    <td><?php echo $data['fault_num']?></td>
                                    <td><?php echo $data['last_update_time']?></td>
                                    <td><?php echo $data['lost_day']?></td>
                                    <td><?php echo $data['position']?></td>
                                    <td><?php echo $data['user_name']?></td>
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
    $("#filter_type").change(function() {
        $("#filter_text").attr("name", $(this).val());
    });
    $('.date-range').daterangepicker({
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
        }
    });
</script>
<?php echo $footer;?>