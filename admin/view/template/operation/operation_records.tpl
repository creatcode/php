<?php echo $header; ?>
<link rel="stylesheet" href="<?php echo HTTP_IMAGE; ?>AdminLTE-2.3.7/plugins/jquery.treegrid/css/jquery.treegrid.css">
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">运维记录统计列表</a></li>
                    <li><a href="<?php echo $repair_detail_url?>" data-toggle="tab">运维记录明细列表</a></li>
                    <li><a href="<?php echo $area_url?>" data-toggle="tab">运维区域</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="cooperator_id" id="cooperator_id" class="input-sm">
                                    <option value=""> 全部 </option>
                                    <option <?php echo $filter['cooperator_id'] === 999 ? 'selected' : ''; ?> value="999"> 平台 </option>
                                    <?php if (!empty($cooperators) && is_array($cooperators)) { ?>
                                    <?php foreach($cooperators as $cooperator) { ?>
                                    <option value="<?php echo $cooperator['cooperator_id']; ?>" <?php echo $cooperator['cooperator_id'] == $filter['cooperator_id'] ? 'selected' : ''; ?>><?php echo $cooperator['cooperator_name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                                <input type="text" name="admin_name" value="<?php echo isset($filter['admin_name']) ? $filter['admin_name'] : ''; ?>" id="filter_text" class="input-sm" placeholder="运维人员" style="border: 1px solid #a9a9a9;"/>
                                <input type="text" name="add_time" value="<?php echo $filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 200px;" placeholder="上报时间"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <table class="tree table table-bordered">
                            <thead>
                            <tr>
                                <th>合伙人</th>
                                <th>运维人员</th>
                                <th>待处理故障数</th>
                                <th>5天未挪动单车</th>
                                <th>处理次数</th>
                                <th>最后处理时间</th>
                                <th style="min-width:130px;">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($data_rows as $k=>$v){ ?>
                            <tr class="treegrid-<?php echo $k?>">
                                <td><?php echo $v['cooperator_name']?></td>
                                <td><?php echo $v['operator_count']?>个</td>
                                <td><?php echo $v['need_fault_count']?></td>
                                <td><?php echo $v['not_used_count']?></td>
                                <td><?php echo $v['repair_count']?></td>
                                <td></td>
                                <td></td>
                                <?php if(isset($v['operators'])){ ?>
                                <?php foreach($v['operators'] as $operator){ ?>
                            <tr class="treegrid-parent-<?php echo $k?>">
                                <td></td>
                                <td><?php echo $operator['nickname']?> (<?php echo $operator['admin_name']?>)</td>
                                <td></td>
                                <td></td>
                                <td><?php echo $operator['repair_info']['handle_num']?></td>
                                <td><?php echo $operator['repair_info']['add_time']?></td>
                                <td>
                                    <div class="btn-group">
                                        <button data-url="<?php echo $operator['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>
                                    </div>
                                </td>
                            </tr>
                            <?php }?>
                            <?php }?>
                            </tr>
                            <?php }?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">

    $('.date-range').daterangepicker({
        ranges : {
            '本月': [moment().startOf('month'), moment()],
            '今日': [moment(), moment()],
            '本周': [moment().startOf('week'), moment()],
            '今年': [moment().startOf('year'), moment()],
            '最近7日': [moment().subtract('days', 6), moment()],
            '最近30日': [moment().subtract('days', 29), moment()],
            '全部': ['2016-01-01', moment()] //起始日期别设置太小
        },
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
            customRangeLabel:'自定义'
        }
    });

</script>

<!--<script src="<?php echo HTTP_IMAGE;?>AdminLTE-2.3.7/plugins/jquery.treegrid/js/jquery.treegrid.js"></script>
<script src="<?php echo HTTP_IMAGE;?>AdminLTE-2.3.7/plugins/jquery.treegrid/js/jquery.treegrid.bootstrap3.js"></script>-->

<script type="text/javascript">
    $(document).ready(function() {
        var HTTP_IMAGE = '<?php echo HTTP_IMAGE;?>';
        $.getScript(HTTP_IMAGE+'AdminLTE-2.3.7/plugins/jquery.treegrid/js/jquery.treegrid.js',function(){
            $.getScript(HTTP_IMAGE+'AdminLTE-2.3.7/plugins/jquery.treegrid/js/jquery.treegrid.bootstrap3.js',function(){
                $('.tree').treegrid({
                    treeColumn: 1,
                    initialState:'collapsed'
                });
            });

        });
    });
</script>

<?php echo $footer;?>