<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>短信群发</span>
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
                    <li><a href="<?php echo $templates_url?>" data-toggle="tab">模板列表</a></li>
                    <li class="active"><a href="javascript:;" data-toggle="tab">群发记录列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="filter_text" value="<?php echo isset($filter['filter_text']) ? $filter['filter_text'] : ''; ?>" id="filter_text" class="input-sm" placeholder="短信内容" style="border: 1px solid #a9a9a9;"/>
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
                                    <th>id</th>
                                    <th>原始模板id</th>
                                    <th>自定义的短信</th>
                                    <th>手机号码</th>
                                    <th>平台</th>
                                    <th>添加时间</th>
                                    <th>发送时间</th>
                                    <th>操作员</th>
                                    <th style="min-width:130px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <td><?php echo $data['id']?></td>
                                    <td><?php echo $data['template_id']?></td>
                                    <td><?php echo $data['template_text']?></td>
                                    <td><?php echo $data['mobiles_num']?>个</td>
                                    <td><?php echo $data['sms_platform']?></td>
                                    <td><?php echo date('Y-m-d H:i:s',$data['add_time'])?></td>
                                    <td><?php echo $data['send_time'] > 0 ? date('Y-m-d H:i:s',$data['send_time']) : '' ?></td>
                                    <td><?php echo $data['admin_name']?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $info_action.'&send_id='.$data['id']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>
                                            <?php if(!$data['send_time']){ ?>
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo $edit_action.'&template_id='.$data['template_id'].'&send_id='.$data['id']; ?>">编辑</a></li>
                                                <li><a href="<?php echo $delete_action.'&send_id='.$data['id']; ?>">删除</a></li>
                                            </ul>
                                            <?php } ?>
                                        </div>
                                        <?php if(!$data['send_time']){ ?>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $send_url.'&send_id='.$data['id']; ?>" type="button" class="btn btn-info link">发送</button>
                                        </div>
                                        <?php } ?>
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
<script type="text/javascript">
    $('.date-range').daterangepicker({
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false
        }
    });

</script>
<?php echo $footer;?>