<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>用户评分</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">用户评分列表</a></li>
                    <li ><a href="<?php echo $user_rating_chart_action; ?>" data-toggle="tab">用户评分统计</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">

                                <select class="input-sm" name="cooperator_id">
                                    <option value="">合伙人</option>
                                    <?php if (is_array($cooperators) && !empty($cooperators)) { ?>
                                    <?php foreach($cooperators as $cooperator) { ?>
                                    <option value="<?php echo $cooperator['cooperator_id']; ?>" <?php echo $cooperator['cooperator_id']==$cooperator_id ? 'selected' : ''; ?>><?php echo $cooperator['cooperator_name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>

                                <input type="text" name="mobile" value="<?php echo isset($filter_mobile) ? $filter_mobile : ''; ?>" id="filter_text" class="input-sm" style="border: 1px solid #a9a9a9;" placeholder="用户手机号码"/>
                                <input type="text" name="star_num" value="<?php echo isset($filter_star_num) ? $filter_star_num : ''; ?>" id="filter_text" class="input-sm" style="border: 1px solid #a9a9a9;" placeholder="评价星数"/>
                                <input type="text" name="add_time" value="<?php isset($filter_add_time) ? $filter_add_time : ''; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 200px;" placeholder="评分时间"/>

                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
                            <!--<button class="btn btn-default btn-sm button-upload" data-action="<?php echo $import_action; ?>"><i class="fa fa-upload"></i>&nbsp;导入</button>-->
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
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['user_id']?>"></td>-->
                                    <!--<td><?php echo $data['user_id']?></td>-->
                                    <td><?php echo $data['user_name']?></td>
                                    <td><?php echo $data['cooperator_name']?></td>
                                    <td><?php echo $data['bicycle_sn']?></td>
                                    <td><?php echo $data['comment']?></td>
                                    <td><?php echo $data['star_num']?></td>
                                    <td><?php echo $data['comment_tag_text']?></td>
                                    <td ><?php echo $data['use_time']?></td>
                                    <td><?php echo date("Y-m-d",$data['add_time']);?></td>
                                    <td>
                                        <!--<button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>
                                 --></td>
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