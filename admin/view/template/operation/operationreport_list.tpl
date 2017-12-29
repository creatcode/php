<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>维修情况统计</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">维修情况列表</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <input type="text" name="date" value="<?php echo $filter['date']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 180px;" placeholder="使用时间(默认本月)"/>
								<select name="region_id" style="height: 30px; margin-left: 8px; width:160px; " onchange="alert('假设改变会改城市选项')">
                                    
                                    <option selected="selected" >区域</option>
									<option selected="selected" >a区域</option>
									<option selected="selected" >b区域</option>
                                    
                                </select>
                                <select name="region_id" style="height: 30px; margin-left: 8px; width:160px; ">
                                    <?php foreach($regionList as $v){
                                        if($region_id ==$v['region_id']){
                                    ?>
                                    <option selected="selected" value="<?php echo $v['region_id']; ?>"><?php echo $v['region_name']; ?> </option>
                                    <?php
                                        }else{
                                    ?>
                                    <option value="<?php echo $v['region_id']; ?>"><?php echo $v['region_name']; ?> </option>
                                    <?php }
                                     } ?>
                                </select>
								<button class="btn btn-default btn-sm" form="search_form" formmethod="post" formaction="<?php echo $export_action; ?>"><i class="fa fa-download"></i>&nbsp;导出</button>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
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
                                    <!--<th style="width: 1px;" class="text-center"><input type="checkbox" onclick="$('input[name*=\'selected\']:enabled').prop('checked', this.checked);"></th>-->
                                    <?php foreach ($data_columns as $column) { ?>
                                    <th><?php echo $column['text']; ?></th>
                                    <?php } ?>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($list as $vdata) { ?>
                                <tr>
                                    <td><?php echo $vdata['user_name']?></td>
                                    <td><?php echo $vdata['bike_sn']?></td>
                                    <td><?php echo $vdata['lock_sn']?></td>
                                    <td><?php echo $vdata['part']?></td>
									<td><?php echo $vdata['city']?></td>
									<td><?php echo $vdata['create_time']?></td>
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
            customRangeLabel:'自定义'
        },
        ranges : {
			
            
            '今日': [moment(), moment()],
            '本周': [moment().startOf('week'), moment()],
			'本月': [moment().startOf('month'), moment()],
            '本季': [moment().startOf('quarter'), moment()],
            '今年': [moment().startOf('year'), moment()],
            /*'最近7日': [moment().subtract('days', 6), moment()],
            '最近30日': [moment().subtract('days', 29), moment()],
            '全部': ['2016-01-01', moment()] //起始日期别设置太小*/
        }
    });
 

</script>
<?php echo $footer;?>