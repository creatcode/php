<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>系统消息</span>
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
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">标题</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="msg_title" value="<?php echo $data['msg_title']; ?>" class="form-control">
                                        <?php if (isset($error['msg_title'])) { ?><div class="text-danger"><?php echo $error['msg_title']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">消息图片</label>
                                    <div class="col-sm-5">
                                        <button type="button" class="img-thumbnail button-upload" style="outline: none;" data-tag="logo" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                            <img src="<?php echo $data['msg_image_url']; ?>" alt="消息图片" style="max-width: 100px; max-height: 100px;" class="imageurl">
                                            <input type="hidden" name="msg_image" value="<?php echo $data['msg_image']; ?>" placeholder="消息图片" class="filepath">
                                        </button>
                                        <?php if (isset($error['msg_image'])) { ?><div class="text-danger"><?php echo $error['msg_image']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">用户</label>
                                    <div class="col-sm-5">
                                        <textarea name="mobiles" class="form-control" rows="3"><?php echo $data['mobiles']; ?></textarea>
                                        <?php if (isset($error['mobiles'])) { ?><div class="text-danger"><?php echo $error['mobiles']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">摘要</label>
                                    <div class="col-sm-5">
                                        <textarea name="msg_abstract" class="form-control" rows="3"><?php echo $data['msg_abstract']; ?></textarea>
                                        <?php if (isset($error['msg_abstract'])) { ?><div class="text-danger"><?php echo $error['msg_abstract']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">外部链接</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="msg_link" value="<?php echo $data['msg_link']; ?>" class="form-control">
                                        <?php if (isset($error['msg_link'])) { ?><div class="text-danger"><?php echo $error['msg_link']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">正文</label>
                                    <div class="col-sm-5">
                                        <div class="margin-bottom custom_content">
                                            <textarea name="msg_content" class="form-control" rows="5"><?php echo $data['msg_content']; ?></textarea>
                                        </div>
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
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php echo $footer;?>