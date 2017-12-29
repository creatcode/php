<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>文章管理</span>
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
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>" id="form">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">文章标题</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="article_title" value="<?php echo $data['article_title']; ?>" class="form-control" />
                                        <?php if (isset($error['article_title'])) { ?><div class="text-danger"><?php echo $error['article_title']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">文章分类</label>
                                    <div class="col-sm-5">
                                        <select name="category_id" class="form-control">
                                            <?php foreach($categories as $k => $v) { ?>
                                            <option value="<?php echo $k; ?>" <?php if ((string)$k == $data['category_id']) { ?>selected<?php } ?>><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['category_id'])) { ?><div class="text-danger"><?php echo $error['category_id']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">文章关键字</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="article_code" value="<?php echo $data['article_code']; ?>" class="form-control">
                                        <?php if (isset($error['article_code'])) { ?><div class="text-danger"><?php echo $error['article_code']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">文章排序</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="article_sort" value="<?php echo $data['article_sort']; ?>" class="form-control">
                                        <?php if (isset($error['article_sort'])) { ?><div class="text-danger"><?php echo $error['article_sort']; ?></div><?php } ?>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="col-sm-2 control-label">文章内容</label>
                                    <div class="col-sm-5">
                                        <div id="summernote">
                                            <?php echo htmlspecialchars_decode($data['article_content']); ?>
                                        </div>
                                        <?php if (isset($error['article_content'])) { ?><div class="text-danger"><?php echo $error['article_content']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <input type="hidden" value=""  name="article_content"  />
                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div class="pull-right">
                                        <a href="javascript:;" class="btn btn-sm btn-success margin-r-5" id="post-data">提交</a>
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

<link rel="stylesheet" href="<?php echo HTTP_CATALOG ;?>js/plugins/summernote-0.8.8-dist/dist/summernote.css" />
<script type="text/javascript" src="<?php echo HTTP_CATALOG ;?>js/plugins/summernote-0.8.8-dist/dist/summernote.js"></script>
<script type="text/javascript">

        $(document).ready(function() {
            $("a").unbind();
            //$("a").unbind();

            $('#summernote').summernote();
            $("#post-data").click(function(){
                var content = $('#summernote').summernote("code");
                $("input[name=article_content]").val(content);
                $("#form").submit();
            });


        });



</script>
<?php echo $footer;?>