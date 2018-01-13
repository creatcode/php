<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo @$lang['t2'];?></span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo @$lang['t3'];?></a></li>
                    <li ><a href="<?php echo $user_rating_chart_action; ?>" data-toggle="tab"><?php echo @$lang['t4'];?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form class="search_form" id="search_form" action="<?php echo $action; ?>" method="get">
                            <!-- 搜索 -->
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">

                                <select class="input-sm" name="">
                                    <option value="">--<?php echo @$lang['t5'];?>--</option>
                                  
                                </select>
                                <select class="input-sm" name="">
                                    <option value="">--<?php echo @$lang['t6'];?>--</option>
                                  
                                </select>
                                <input type="text" name="mobile" value="<?php echo isset($filter_mobile) ? $filter_mobile : ''; ?>" id="filter_text" class="input-sm" style="border: 1px solid #a9a9a9;" placeholder="<?php echo @$lang['t7'];?>"/>
                                <input type="text" name="star_num" value="<?php echo isset($filter_star_num) ? $filter_star_num : ''; ?>" id="filter_text" class="input-sm" style="border: 1px solid #a9a9a9;" placeholder="<?php echo @$lang['t8'];?>"/>
                                <select name="time_type" id="time_select"  class="input-sm" onchange="addrat()">
                                    <option value="0"><?php echo @$lang['t9'];?></option>
                                    <?php if(!empty($time_type)) {?>
                                    <?php foreach($time_type as $k=>$v){?>
                                    <option value="<?php echo $k?>" <?php echo $k == @$filter['time_type'] ? 'selected' : ''; ?>><?php echo $v?></option>
                                    <?php }?>
                                    <?php }?>
                                </select>
                                <input type="text" name="add_time" value="<?php isset($filter_add_time) ? $filter_add_time : ''; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 200px;" placeholder="<?php echo @$lang['t10'];?>"/>

                                <div class="pull-right">
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fa fa-search"></i>&nbsp;<?php echo @$lang['t11'];?></button>
                                </div>
                            </div>
                        </form>
                        <!-- 新增 -->
                        <div class="form-group">
                            <!--<button class="btn btn-default btn-sm button-upload" data-action="<?php echo $import_action; ?>"><i class="fa fa-upload"></i>&nbsp;导入</button>-->
                            <button class="btn btn-default btn-sm" form="search_form" formmethod="post" formaction="<?php echo $export_action; ?>"><i class="fa fa-download"></i>&nbsp;<?php echo @$lang['t13'];?></button>
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
                                    <th style="min-width:130px;"><?php echo @$lang['t12'];?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data_rows as $data) { ?>
                                <tr>
                                    <!--<td><input type="checkbox" name="selected[]" value="<?php echo $data['user_id']?>"></td>-->
                                    <!--<td><?php echo $data['user_id']?></td>-->
                                    <td><?php echo $data['user_name']?></td>

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
    $(function(){
            addrat();
    });
        var b = "";
        function addrat(){
        var a = $("#time_select").val();
        if(a==1){
            b = "YYYY";
        }else if(a==2){
            b = "YYYY-MM";
        }else if(a==3){
            b = "YYYY-MM-DD";
        }else if(a==0){
            b = "YYYY-MM-DD";
        }
        $('.date-range').daterangepicker({
            "showDropdowns": true,
            locale:{
                format: b,
                isAutoVal:false,
            }
      });
        
    };

    $("#filter_type").change(function() {
        $("#filter_text").attr("name", $(this).val());
    });
</script>
<?php echo $footer;?>