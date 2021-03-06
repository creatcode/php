<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>工单列表</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>', this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">工单列表</a></li>

                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="coupon_list">


                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="filter_type" id="filter_type" class="input-sm">

                                    <option value="" >筛选</option>
                                    <option value="" >工单号</option>
                                    <option value="" >运维人员</option>
                                    <option value="" >客服人员</option>
                                </select>
                                <input type="text" name="" value="" id="filter_text" class="input-sm" style="border: 1px solid #a9a9a9;"/>
                                <select name="filter_status" id="filter_status" class="input-sm">

                                    <option value="" >工单状态</option>
                                    <option value="" >已处理</option>
                                    <option value="" >处理中</option>
                                    <option value="" >待处理</option>
                                </select>

                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
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
                                        <?php foreach ($data_columns as $column) { ?>
                                        <th><?php echo $column['text']; ?></th>
                                        <?php } ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($data_rows as $data) { ?>
                                    <tr>
                                        <td><?php echo $data['wo_id']?></td>

                                        <td><?php echo $operations[$data['admin_id']]['nickname']?></td>
                                        <td><?php echo $data['service_name']?></td>
                                        <td><?php echo $data['content']?></td>
                                        <td><?php echo $data['status_string']?></td>
                                        <td><?php echo $data['create_time']?></td>
                                        <td>
                                            <div class="btn-group">
                                                <button data-url="<?php echo $data['edit_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>编辑</button>

                                                <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                    <span class="caret"></span>
                                                    <span class="sr-only">Toggle Dropdown</span>
                                                </button>
                                                <ul class="dropdown-menu" role="menu">
                                                    <li><a href="<?php echo $data['delete_action']; ?>" onclick="javascript:void(0);">删除</a></li>
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
<!-- /.content -->
<?php echo $footer;?>