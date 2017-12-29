<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>站点运维通知</span>
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
                    <li class="active"><a href="<?php echo $nofification_url;?>" data-toggle="tab">站点运维通知</a></li>
					<li><a href="<?php echo $threshold_url;?>" data-toggle="tab">阀值通知</a></li>
					<li><a href="<?php echo $unused_url;?>" data-toggle="tab">长时间未用通知</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">

						<form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="filter_type" id="filter_type" class="input-sm">
                                    
                                    <option value="" >筛选</option>
                                   <option value="" >指派员</option>
								   <option value="" >接收员</option>
                                </select>
                                <input type="text" name="" value="" id="filter_text" class="input-sm" style="border: 1px solid #a9a9a9;"/>
                                <select class="input-sm" name="fault_source">
                                    <option value>是否送达</option>
                                   <option value>是</option>
								   <option value>否</option>
                                </select>
                                
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
                            <a href="<?php echo $add_url; ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i>&nbsp;新增</a>

                        </div>
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
                                   
                                    <td><?php echo $data['type']?></td>
									<td><?php echo $data['operation_from']?></td>
                                    <td><?php echo $data['operation_to']?></td>
                                    <td><?php echo $data['send_status']?></td>
                                    <td><?php echo $data['create_time']?></td>
									<td><?php echo $data['operation_status']?></td>
                                    <td><?php echo $data['content']?></td>
                                   
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $data['edit_url']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>编辑</button>
                                       
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#" >删除</a></li>
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

<?php echo $footer;?>