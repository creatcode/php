<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>计费标准设置</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">计费标准设置</a></li>
                </ul>

                <form class="search_form" action="<?php echo $return_action; ?>" method="get">
                    <!-- 搜索 -->
                    <!--
                    <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">

                        <select name="adv_region_id" class="input-sm">
                            <option value="">平台</option>
                            <?php if (!empty($region_list)) { ?>
                            <?php foreach($region_list as $v) { ?>
                            <option value="<?php echo $v['region_id']; ?>" <?php echo $v['region_id'] == $adv_region_id ? 'selected' : ''; ?>><?php echo $v['region_name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
                        <div class="pull-right">
                            <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                        </div>
                    </div>-->
                </form>



                <div class="tab-content">
                    <div class="form-group">
                        <a href="<?php echo $add_action; ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;新增</a>
                    </div>
                    <div class="tab-pane active" id="bicycle">
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
                                    <td><?php echo $regions[$data['region_id']]['region_name'];?></td>
                                    <td><?php echo $cities[$data['city_id']]['city_name'];?></td>
                                    <td><?php echo $bicycle_types[$data['bicycle_type']];?></td>
                                    <td><?php echo $user_types[$data['user_type']];?></td>

                                    <td><?php echo $data['deposit']?></td>

                                    <td><?php echo $data['monthly_card_money']?></td>
                                    <td><?php echo $data['yearly_card_money']?></td>
                                    <td><?php echo $data['cards_first_half']?></td>
                                    <td><?php echo $data['cards_afterwards_half']?></td>
                                    <td><?php echo $data['first_half']?></td>
                                    <td><?php echo $data['afterwards_half']?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $data['edit_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-pencil"></i>编辑</button>
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
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