<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>工单列表</span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab">编辑</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">工单号</label>
                                    <div class="col-sm-5">
										<input  type="text" name="wo_id" value="<?php echo @$data['wo_id'] ?>" class="form-control" disabled>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">用户姓名</label>
                                    <div class="col-sm-5">
                                        <input  type="text" name="user_name" value="<?php echo @$data['user_name'] ?>" class="form-control"  disabled>
                                        <?php if (isset($error['number'])) { ?><div class="text-danger"><?php echo $error['number']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">客服</label>
                                    <div class="col-sm-5">
                                        <input  type="text" name="service_name" value="<?php echo @$data['service_name']?>" class="form-control"  disabled>
                                        <?php if (isset($error['number'])) { ?><div class="text-danger"><?php echo $error['number']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">运维</label>
                                    <div class="col-sm-5">
                                        <select name="admin_id" class="form-control">
                                            <option value="">请选择...</option>
                                            <?php foreach($operations as $k ) { ?>
                                            <option value="<?php echo $k['admin_id']; ?>" <?php if ((string)$k['admin_id'] == @$data['admin_id']) { ?>selected<?php } ?>><?php echo $k['nickname']; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['admin_id'])) { ?><div class="text-danger"><?php echo $error['admin_id']; ?></div><?php } ?>
                                        <?php if (isset($error['region_id'])) { ?><div class="text-danger"><?php echo $error['region_id']; ?></div><?php } ?>
                                    </div>
                                </div>
								
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">工单内容</label>
                                    <div class="col-sm-5">
                                        <textarea  name="content" class="form-control date" rows="5"><?php echo @$data['content'] ?></textarea>
                                        <?php if (isset($error['mobiles'])) { ?><div class="text-danger"><?php echo $error['mobiles']; ?></div><?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div class="pull-right">
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5" onclick="return false;">提交</button>
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default">返回</a>
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
<script type="text/javascript">

</script>
<?php echo $footer;?>