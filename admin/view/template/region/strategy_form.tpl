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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo $title; ?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <?php if (isset($success)) { ?>
                        <div class="alert bg-light-blue"><i class="fa fa-check-circle"></i>&nbsp;<?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <?php if(isset($data['strategy_id'])){ ?>
                            <input type="hidden" name="strategy_id" value="<?php echo @$data['strategy_id']; ?>"  />
                            <?php } ?>

                            <div class="row">
                                <div class="col-sm-6">

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-5">
                                           <label>条件</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">区域</label>
                                        <div class="col-sm-5">
                                            <select name="region_id" class="form-control">
                                                <?php if (!empty($regions)) { ?>
                                                <?php foreach($regions as $k => $region) { ?>
                                                <option value="<?php echo $region['region_id']; ?>" <?php echo $region['region_id'] == @$data['region_id'] ? 'selected' : ''; ?> ><?php echo $region['region_name']; ?></option>
                                                <?php } ?>
                                                <?php } ?>
                                            </select>
                                            <?php if (isset($error['region_id'])) { ?><div class="text-danger"><?php echo $error['region_id']; ?></div><?php } ?>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">城市</label>
                                        <div class="col-sm-5" >
                                            <select name="city_id" class="form-control" id="city">
                                                <?php if (!empty(@$data['city_id'])) { ?>
                                                <?php foreach($cities as $k => $v) { ?>
                                                <option value="<?php echo $v['city_id']; ?>" <?php echo $v['city_id'] == $data['city_id'] ? 'selected' : ''; ?>><?php echo $v['city_name']; ?></option>
                                                <?php } ?>
                                                <?php } ?>
                                            </select>
                                            <?php if (isset($error['city_id'])) { ?><div class="text-danger"><?php echo $error['city_id']; ?></div><?php } ?>
                                        </div>
                                    </div>


                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">单车类型</label>
                                        <div class="col-sm-5">
                                            <select name="bicycle_type" class="form-control">
                                                <?php if (!empty($bicycle_types)) { ?>
                                                <?php foreach($bicycle_types as $k => $v) { ?>
                                                <option value="<?php echo $k; ?>" <?php echo $k == @$data['bicycle_type'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                                <?php } ?>
                                                <?php } ?>
                                            </select>
                                            <?php if (isset($error['bicycle_type'])) { ?><div class="text-danger"><?php echo $error['bicycle_type']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">用户类型</label>
                                        <div class="col-sm-5">
                                            <select name="user_type" class="form-control">
                                                <?php if (!empty($user_types)) { ?>
                                                <?php foreach($user_types as $k => $v) { ?>
                                                <option value="<?php echo $k; ?>" <?php echo $k == @$data['user_type'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                                <?php } ?>
                                                <?php } ?>
                                            </select>
                                            <?php if (isset($error['user_type'])) { ?><div class="text-danger"><?php echo $error['user_type']; ?></div><?php } ?>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-sm-6">
                                    　<!--右边这旮旯-->
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label"></label>
                                        <div class="col-sm-5">
                                            <label>方案</label>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">押金</label>
                                        <div class="col-sm-5">
                                            <input type="text" name="deposit" value="<?php echo @$data['deposit']; ?>" class="form-control">
                                            <?php if (isset($error['deposit'])) { ?><div class="text-danger"><?php echo $error['deposit']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">月卡</label>
                                        <div class="col-sm-5">
                                            <input type="text" name="monthly_card_money" value="<?php echo @$data['monthly_card_money']; ?>" class="form-control">
                                            <?php if (isset($error['monthly_card_money'])) { ?><div class="text-danger"><?php echo $error['monthly_card_money']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">年卡</label>
                                        <div class="col-sm-5">
                                            <input type="text" name="yearly_card_money" value="<?php echo @$data['yearly_card_money']; ?>" class="form-control">
                                            <?php if (isset($error['yearly_card_money'])) { ?><div class="text-danger"><?php echo $error['yearly_card_money']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">第一个半小时（月卡用户）</label>
                                        <div class="col-sm-5">
                                            <input type="text" name="cards_first_half" value="<?php echo @$data['cards_first_half']; ?>" class="form-control">
                                            <?php if (isset($error['cards_first_half'])) { ?><div class="text-danger"><?php echo $error['cards_first_half']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">最后一个半小时（月卡用户）</label>
                                        <div class="col-sm-5">
                                            <input type="text" name="cards_afterwards_half" value="<?php echo @$data['cards_afterwards_half']; ?>" class="form-control">
                                            <?php if (isset($error['cards_afterwards_half'])) { ?><div class="text-danger"><?php echo $error['cards_afterwards_half']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">第一个半小时（非月卡用户）</label>
                                        <div class="col-sm-5">
                                            <input type="text" name="first_half" value="<?php echo @$data['first_half']; ?>" class="form-control">
                                            <?php if (isset($error['first_half'])) { ?><div class="text-danger"><?php echo $error['first_half']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">最后一个半小时（非月卡用户）</label>
                                        <div class="col-sm-5">
                                            <input type="text" name="afterwards_half" value="<?php echo @$data['afterwards_half']; ?>" class="form-control">
                                            <?php if (isset($error['afterwards_half'])) { ?><div class="text-danger"><?php echo $error['afterwards_half']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-7">
                                        <div class="pull-right">
                                            <button type="submit" class="btn btn-sm btn-success margin-r-5">提交</button>
                                            <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default">返回</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.css" />
<script type="text/javascript" src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $("select[name=region_id]").change(function(){
            var region_id = $(this).val();
            get_city(region_id);
        });

        function get_city(region_id){
            var url = "<?php echo $get_city_action; ?>";
            $.post(url,{region_id:region_id},function(response,status){
                var data = response.data;
                var html = '';
                for(var i = 0; i < data.length;i++ ){
                    html += '<option value="'+data[i]['city_id']+'">'+data[i]['city_name']+'</option>';
                }
                $("#city").html(html);
            });
        }

        //初始化城市
        <?php if (empty(@$data['city_id'])) { ?>
        var region_id = $("select[name=region_id]").val();
        get_city(region_id);
        <?php } ?>

        $('input.bootstrap-switch').bootstrapSwitch();
    });
    $('.date-range').daterangepicker({
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
        }
    });
</script>


<?php echo $footer;?>