<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>合伙人管理</span>
        <a href="javascript:;"><i class="fa fa-star-o text-gray"></i></a>
    </h1>
</section>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <!-- tab 标签 -->
                <ul class="nav nav-tabs">
                    <li><a href="<?php echo $cooperator_action; ?>" data-toggle="tab">合伙人基本信息</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">操作员管理</a></li>
                    <li><a href="<?php echo $role_action; ?>" data-toggle="tab">操作员角色管理</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="admin_name" value="<?php echo $filter['admin_name']; ?>" class="input-sm" placeholder="登录名" style="border: 1px solid #a9a9a9;">
                                <input type="text" name="nickname" value="<?php echo $filter['nickname']; ?>" class="input-sm" placeholder="姓名" style="border: 1px solid #a9a9a9;">
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
                            <a href="<?php echo $add_action; ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;新增</a>
                            <button class="btn btn-default btn-sm button-upload" data-action="<?php echo $import_action; ?>"><i class="fa fa-upload"></i>&nbsp;导入</button>
                            <button class="btn btn-default btn-sm" form="table_form" formaction="<?php echo $export_action; ?>"><i class="fa fa-download"></i>&nbsp;导出</button>
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
                                    <th style="min-width:130px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <td><?php echo $data['admin_name']?></td>
                                    <td><?php echo $data['nickname']?></td>
                                    <td><?php echo $data['role_name']?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-default btn-flat btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                共 <span class="number"><?php echo $data['regions_num']; ?></span> 个区域 <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu ztree" id="region-tree-<?php echo $data['admin_id']?>" data-admin_id="<?php echo $data['admin_id']?>"></ul>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $data['edit_action']; ?>" type="button" class="btn btn-info link btn-sm"><i class="fa fa-fw fa-eye"></i>编辑</button>
                                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
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
    </div>
</section>
<link rel="stylesheet" href="<?php echo HTTP_IMAGE . 'AdminLTE-2.3.7/'; ?>plugins/ztree/zTreeStyle/zTreeStyle.css" />
<script type="text/javascript" src="<?php echo HTTP_IMAGE . 'AdminLTE-2.3.7/'; ?>plugins/ztree/jquery.ztree.core.js"></script>
<script type="text/javascript" src="<?php echo HTTP_IMAGE . 'AdminLTE-2.3.7/'; ?>plugins/ztree/jquery.ztree.excheck.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // 异步更新数据
        function update(event, treeId, treeNode) {
            var tree = $('#' + treeId),
                    admin_id = tree.data('admin_id'),
                    checkedNodes = $.fn.zTree.getZTreeObj(treeId).getCheckedNodes(true),
                    theButton = tree.parents('.dropdown').children('button'),
                    checkedIds = [];

            theButton.find(".number").html(checkedNodes.length);

            for(var i in checkedNodes) {
                checkedIds.push(checkedNodes[i].id);
            }

            var url = '<?php echo $update_admin_region_action; ?>';
            var params = {admin_id: admin_id, regions: checkedIds.join()};
            console.log(params);
            $.post(url, params, function(data) {
                // 区域变更提示
            });
        }

        var setting = {
            check: {
                enable: true
            },
            callback: {
                onCheck: update
            },
            data: {
                simpleData: {
                    enable: true
                }
            }
        };

        for (var i in regions) {
            $.fn.zTree.init($("#region-tree-" + i), setting, regions[i]);
        }
    });

    var regions = <?php echo $regions; ?>;
</script>
<script type="text/javascript">
    $("#filter_type").change(function() {
        $("#filter_text").attr("name", $(this).val());
    });
</script>
<?php echo $footer;?>
