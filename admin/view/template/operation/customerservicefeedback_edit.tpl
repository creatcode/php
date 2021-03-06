<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>客服反馈</span>
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
                                    <label class="col-sm-2 control-label">用户姓名</label>
                                    <div class="col-sm-5">
                                        <input  type="text"  name="user_name" value="<?php echo @$data['user_name']; ?>" class="form-control" disabled="disabled">
                                        <?php if (isset($error['number'])) { ?><div class="text-danger"><?php echo $error['number']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">反馈类型</label>
                                    <div class="col-sm-5">
										<?php foreach($type as $key=>$val){ ?>
                                        <label class="radio-inline">
                                            <input type="radio" name="type_string" value="<?php echo $key;?>" <?php echo (string)$key == $data['type_string'] ? 'checked' : ''; ?>/><?php echo $val;?>
                                        </label>
                                        <?php }?>
                                        <?php if (isset($error['type_string'])) { ?><div class="text-danger"><?php echo $error['type_string']; ?></div><?php } ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">反馈内容</label>
                                    <div class="col-sm-5">
                                        <textarea name="content" class="form-control date" rows="5"><?php echo @$data['content']; ?></textarea>
                                        <?php if (isset($error['mobiles'])) { ?><div class="text-danger"><?php echo $error['mobiles']; ?></div><?php } ?>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div style="text-align:center">
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5" style="margin-right: 40px;padding:0 30px;height:40px;font-size:14px;" onclick="return false;">提交</button>
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding:0 30px;height:40px;font-size:14px;line-height:40px">返回</a>
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