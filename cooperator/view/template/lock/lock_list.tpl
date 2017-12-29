<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>单车管理</span>
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
                        <li class=""><a href="<?php echo $bicycle_action; ?>">单车明细</a></li>
                        <li class="active"><a href="javascript:;">车锁管理</a></li>
                    </ul>
                    <div class="tab-content">
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
                            <select name="lock_status" class="input-sm">
                                <option value>状态</option>
                                <?php foreach($lock_status as $k => $v) { ?>
                                <option value="<?php echo $k; ?>" <?php echo (string)$k == $filter['lock_status'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                <?php } ?>
                            </select>

                            <div class="pull-right">
                                <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                            </div>
                        </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
                            <a href="<?php echo $add_action; ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;新增</a>
                            <button class="btn btn-default btn-sm button-upload" data-action="<?php echo $import_action; ?>"><i class="fa fa-upload"></i>&nbsp;导入</button>
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
                                <th style="min-width:130px;">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($data_rows as $data) { ?>
                            <tr>
                                <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['lock_sn']?>"></td>-->
                                <td><?php echo $data['lock_sn']?></td>
                                <td><?php echo $data['lock_name']?></td>
                                <td><?php echo $data['battery']?></td>
                                <td><?php echo $data['open_nums']?></td>
                                <td><?php echo $data['system_time']?></td>
                                <td><?php echo $data['lock_status']?></td>
                                <td>
                                    <div class="btn-group">
                                        <button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>
                                        <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                            <span class="caret"></span>
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <ul class="dropdown-menu" role="menu">
                                            <li><a href="<?php echo $data['edit_action']; ?>">编辑</a></li>
                                            <li><a href="<?php echo $data['delete_action']; ?>">删除</a></li>
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