<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>管理员</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">管理员列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="admin_name" value="<?php echo $filter['admin_name']; ?>" class="input-sm" style="border: 1px solid #a9a9a9;" placeholder="用户名称"/>
                                <input type="text" name="nickname" value="<?php echo $filter['nickname']; ?>" class="input-sm" style="border: 1px solid #a9a9a9;" placeholder="姓名"/>
                                <select name="role_id" class="input-sm">
                                    <option value>角色</option>
                                    <?php foreach($roles as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == $filter['role_id'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>
                                <select name="state" class="input-sm">
                                    <option value>状态</option>
                                    <?php foreach($state as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == $filter['state'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
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
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['bicycle_id']?>"></td>-->
                                    <td><?php echo $data['admin_name']?></td>
                                    <td><?php echo $data['nickname']?></td>
                                    <td><?php echo $data['role_name']?></td>
                                    <td><?php echo $data['login_time']?></td>
                                    <td><?php echo $data['state']?></td>
									
                                    <td>
                                        <button data-url="<?php echo $data['edit_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-edit"></i>编辑</button>
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