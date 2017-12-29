<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>短信群发</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i
                    class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
    <?php echo $statistics_in_page_header;?>
</section>
<form class="form-horizontal" method="post" action="<?php echo $action; ?>">
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="nav-tabs-custom">
                    <!-- tab 标签 -->
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="javascript:;" data-toggle="tab">群发短信详情</a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="bicycle">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">编辑后的模板</label>
                                <div class="col-sm-5">
                                    <?php echo $data['template_text']?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">短信平台</label>
                                <div class="col-sm-5">
                                    <?php echo $data['sms_platform']?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">平台模板id</label>
                                <div class="col-sm-5">
                                    <?php echo $data['platform_template_id']?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">手机号码</label>
                                <div class="col-sm-5">
                                    <?php echo $data['mobiles']?>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div class="pull-right">
                                        <a href="javascript:" onclick="history.back(-1)" class="btn btn-sm btn-default">返回</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</form>

<?php echo $footer;?>