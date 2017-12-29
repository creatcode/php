<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>智能分析</span>
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
					<?php
						foreach($tab_data as $key=>$val){
							if($val['type']===$data_type){
								echo '<li class="active"><a href="javascript:;" data-toggle="tab">'.$val['name'].'</a></li>';
							}else{
								echo '<li class=""><a href="'.$val['url'].'" data-toggle="tab">'.$val['name'].'</a></li>';
							}
						}
					?>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                      
                        
                    
                        <form id="table_form" class="table_form" method="post">
                            <table class="table table-bordered table-hover dataTable" role="grid">
                                <thead>
                                <tr>
                                    <!--<th style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']:enabled').prop('checked', this.checked);"></th>-->
                                    <?php foreach ($data_columns as $column) { ?>
                                    <th><?php echo $column; ?></th>
                                    <?php } ?>
                                    <th style="min-width:130px;">操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php 
									if($data_type=='bicycle'){
										foreach ($data_rows as $data) {
											foreach ($data as $detail) {
								?>
                                <tr>
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $detail['bicycle_id']?>"></td>-->
                                    <td><?php echo $detail['bicycle_sn']?></td>
                                    <td><?php echo $detail['lock_sn']?></td>
                                    <td><?php echo $detail['type']?></td>
                                    <td><?php echo $detail['lock_type']?></td>
									<td><?php echo $detail['region_name']?></td>
									<td><?php echo $detail['cooperator_name']?></td>
									<td><?php echo $detail['is_using']?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $detail['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo $detail['edit_action']; ?>">编辑</a></li>
                                                <li><a href="<?php echo $detail['delete_action']; ?>">删除</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                                <?php 
											}
										}
									}elseif($data_type=='lock'){ 
										foreach ($data_rows as $data) {
											foreach ($data as $detail) {
								?>
								<tr>
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $detail['bicycle_id']?>"></td>-->
                                    <td><?php echo $detail['lock_sn']?></td>
									<td><?php echo $detail['lock_name']?></td>
									<td><?php echo $detail['cooperator_name']?></td>
									<td><?php echo $detail['battery']?></td>
									<td><?php echo $detail['open_nums']?></td>
									<td><?php echo $detail['system_time']?></td>
									<td><?php echo $detail['lock_status']?></td>
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $detail['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo $detail['edit_action']; ?>">编辑</a></li>
                                                <li><a href="<?php echo $detail['delete_action']; ?>">删除</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
								<?php
											}
										}
									}
								?>
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