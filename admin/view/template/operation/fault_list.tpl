<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>故障记录列表</span>
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
                    <li <?php echo $bike_type==1?'class="active"':'';?>><a href="<?php echo $bike_type_1_url;?>" data-toggle="tab">桩车</a></li>
                    <li <?php echo $bike_type==2?'class="active"':'';?>><a href="<?php echo $bike_type_2_url;?>" data-toggle="tab">单车</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select name="filter_type" id="filter_type" class="input-sm">
                                    <?php if (!empty($filter_types) && is_array($filter_types)) { ?>
                                    <?php foreach($filter_types as $key => $val) { ?>
                                    <option value="<?php echo $key; ?>" <?php echo (string)$key == $filter_type ? 'selected' : ''; ?>><?php echo $val; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                                <input type="text" name="<?php echo $filter_type; ?>" value="<?php echo isset($filter[$filter_type]) ? $filter[$filter_type] : ''; ?>" id="filter_text" class="input-sm" style="border: 1px solid #a9a9a9;"/>
                                <select class="input-sm" name="fault_source">
                                    <option value>故障来源</option>
                                    <?php foreach($fault_source as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == $filter['fault_source'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>
                                <select class="input-sm" name="fault_type">
                                    <option value>故障类型</option>
                                    <?php foreach($fault_types[$bike_type] as $k => $v) { ?>
                                    <option value>--<?php echo $v['parent_type'];?>--</option>
                                    <?php
                                    foreach($v['list'] as $l_key=>$l_val){
                                    ?>
                                    <option value="<?php echo $l_key; ?>" <?php echo (string)$l_key == $filter['fault_type'] ? 'selected' : ''; ?>><?php echo $l_val; ?></option>
                                    <?php
                                    }
                                    ?>
                                    <?php } ?>
                                </select>
                                <select class="input-sm" name="processed">
                                    <option value>处理状态</option>
                                    <?php foreach($process_states as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == $filter['processed'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>
                                <input type="text" name="add_time" value="<?php echo $filter['add_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 200px;" placeholder="上报时间"/>
                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
                            <!-- <button class="btn btn-default btn-sm button-upload" data-action="<?php echo $import_action; ?>"><i class="fa fa-upload"></i>&nbsp;导入</button> -->
                            <button class="btn btn-default btn-sm" form="search_form" formmethod="post" formaction="<?php echo $export_action; ?>"><i class="fa fa-download"></i>&nbsp;导出</button>
                            <button class="btn btn-default btn-sm" form="search_form" formmethod="post" formaction="<?php echo $export_unused_action; ?>"><i class="fa fa-download"></i>&nbsp;导出五天未使用的单车</button>
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
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['fault_id']?>"></td>-->
                                    <td class="open-marker" data-bicycle-id="<?php echo $data['bicycle_id']?>" title="锁编号<?php echo $data['lock_sn']?>"><?php echo $data['bicycle_sn']?></td>
                                    <td><?php echo $data['fault_type']?></td>
                                    <td><?php echo $data['user_name']?></td>
                                    <td><?php echo $data['cooperator_name']?></td>
                                    <td title="<?php echo $data['add_time_delta']?>"><?php echo $data['add_time']?></td>
                                    <td><?php echo $data['processed']?></td>
                                    <td title="<?php echo $data['handling_time_delta']?>"><?php echo $data['handling_time']?></td>
                                    <td><?php echo $data['nickname']?></td>
                     
                                    <!--<td>
                                        <button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>
                                    </td>-->
                                    <td>
                                        <div class="btn-group">
                                            <button data-url="<?php echo $data['info_action']; ?>" type="button" class="btn btn-info link"><i class="fa fa-fw fa-eye"></i>查看</button>
                                            <?php if($data['fault_type_id'] == 12){ ?>
                                            <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                                                <span class="caret"></span>
                                                <span class="sr-only">Toggle Dropdown</span>
                                            </button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="#" onclick="finishOrder(<?php echo $data['lock_sn']?>, <?php echo $data['lng']?>, <?php echo $data['lat']?>)">快速处理</a></li>
                                            </ul>
                                            <?php } ?>
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

    /**
     * 结束订单
     * @param lock_sn
     * @param lng
     * @param lat
     */
    function finishOrder(lock_sn,lng,lat) {
        $.ajax('index.php?route=admin/index/finishOrder', {
            dataType: 'json',
            data: {
                device_id: lock_sn,
                lng: lng,
                lat: lat
            },
            method: 'POST',
            global: false,
            success: function (result) {
                if(!result.errorCode){
                    alert('操作成功');
                }
            }
        });
    }

    $('#bicycle').on('click', '.open-marker', function () {
        var action = "<?php echo $index_action; ?>";
        var bicycleId = $(this).data('bicycleId');
        getUrl(function(result) {
            if(result['route'] != 'admin/index'){
                var bicycle_id = bicycleId;
                localStorage.open_marker_bicycle_id = bicycle_id;
                window.location.href = action;
            }
        });
    });
</script>
<?php echo $footer;?>