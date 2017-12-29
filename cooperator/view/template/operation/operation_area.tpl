<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>运维记录列表</span>
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
                    <li><a href="<?php echo $location_url?>" data-toggle="tab">运维人员定位</a></li>
                    <li><a href="<?php echo $repair_url?>" data-toggle="tab">运维记录统计列表</a></li>
                    <li><a href="<?php echo $repair_detail_url?>" data-toggle="tab">运维记录明细列表</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">运维区域</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="admin_name" value="<?php echo isset($filter['admin_name']) ? $filter['admin_name'] : ''; ?>" id="admin_name" class="input-sm" placeholder="运维名称" style="border: 1px solid #a9a9a9;"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
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
                                    <th>合伙人</th>
                                    <th>运维账号</th>
                                    <th>运维名称</th>
                                    <th>运维区域</th>
                                    <th style="min-width:130px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <td><?php echo isset($data['cooperator_name'])?$data['cooperator_name']:'平台'?></td>
                                    <td><?php echo $data['admin_name']?></td>
                                    <td><?php echo $data['nickname']?></td>
                                    <td><?php echo $data['region_name']?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $info_url.'&admin_id='.$data['admin_id']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>修改区域</button>
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
<?php echo $footer;?>