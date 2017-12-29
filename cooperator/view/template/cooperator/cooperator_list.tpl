<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>合伙人管理</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">合伙人列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="cooperator_name" value="<?php echo $filter['cooperator_name']; ?>" class="input-sm" placeholder="合伙人" style="border: 1px solid #a9a9a9;">
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
                                    <td><?php echo $data['cooperator_name']?></td>
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-default btn-flat btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                共 <span class="number"><?php echo $data['regions_num']; ?></span> 个区域 <span class="caret"></span>
                                            </button>
                                            <ul class="dropdown-menu ztree" id="region-tree-<?php echo $data['cooperator_id']?>" data-cooperator_id="<?php echo $data['cooperator_id']?>"></ul>
                                        </div>
                                    </td>
                                    <td><?php echo $data['state']?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info btn-sm link"><i class="fa fa-fw fa-eye"></i>查看</button>
                                            <button type="button" class="btn btn-info btn-sm dropdown-toggle" data-toggle="dropdown">
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
    </div>
</section>

<link rel="stylesheet" href="<?php echo $static . 'AdminLTE-2.3.7/'; ?>plugins/jquery.treegrid/css/jquery.treegrid.css" />
<link rel="stylesheet" href="<?php echo $static . 'AdminLTE-2.3.7/'; ?>plugins/ztree/zTreeStyle/zTreeStyle.css" />
<script type="text/javascript" src="<?php echo $static . 'AdminLTE-2.3.7/'; ?>plugins/jquery.treegrid/js/jquery.treegrid.js"></script>
<script type="text/javascript" src="<?php echo $static . 'AdminLTE-2.3.7/'; ?>plugins/jquery.treegrid/js/jquery.treegrid.bootstrap3.js"></script>
<script type="text/javascript" src="<?php echo $static . 'AdminLTE-2.3.7/'; ?>plugins/ztree/jquery.ztree.core.js"></script>
<script type="text/javascript" src="<?php echo $static . 'AdminLTE-2.3.7/'; ?>plugins/ztree/jquery.ztree.excheck.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        var regions = <?php echo $regions; ?>;
        $('table.treegrid').treegrid({
            treeColumn: 1,
            expanderExpandedClass: 'fa fa-angle-down',
            expanderCollapsedClass: 'fa fa-angle-right'
        });

        // 异步更新数据
        function update(event, treeId, treeNode) {
            var tree = $('#' + treeId),
                    cooperator_id = tree.data('cooperator_id'),
                    checkedNodes = $.fn.zTree.getZTreeObj(treeId).getCheckedNodes(true),
                    theButton = tree.parents('.dropdown').children('button'),
                    checkedIds = [];

            theButton.find(".number").html(checkedNodes.length);

            for(var i in checkedNodes) {
                checkedIds.push(checkedNodes[i].id);
            }

            var url = '<?php echo $update_cooperator_region_action; ?>';
            var params = {cooperator_id: cooperator_id, regions: checkedIds.join()};
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

        $("#filter_type").change(function() {
            $("#filter_text").attr("name", $(this).val());
        });
    });
</script>
<?php echo $footer;?>