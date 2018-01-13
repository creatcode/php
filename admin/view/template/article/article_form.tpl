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
                                <!-- <div class="form-group">
                                    <label class="col-sm-2 control-label">选择语言版本</label>
                                    <div class="col-sm-5">
                                        <select name="lan_type" class="form-control" id="lan" >
                                            <?php foreach($lan_type as $k => $v) { ?>
                                            <option value="<?php echo $k; ?>" <?php if ((string)$k == $data['lan_type']) { ?>selected<?php } ?>><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['lan_type1'])) { ?><div class="text-danger"><?php echo $error['lan_type1']; ?></div><?php } ?>
                                    </div>
                                </div> -->
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">选择语言版本</label>
                                    <div class="btn-group col-sm-5" id="lan">
                                        <button type="button" class="btn btn-default  active" name="cn">中文</button>
                                        <button type="button" class="btn btn-default " name="en">英文</button>
                                        <button type="button" class="btn btn-default " name="it">意大利语</button>
                                    </div>
                                </div>

                                <div class="form-group" >
                                    <label class="col-sm-2 control-label">文章内容</label>
                                    <div class="col-sm-5"  id='tab'>
                                        <div  class="active">
                                            <div class="summernote "  >
                                            <?php echo htmlspecialchars_decode($data['article_content']); ?>
                                            </div>
                                        </div>
                                        <div class="hidden" >
                                            <div class="summernote1 "  >
                                            <?php echo htmlspecialchars_decode($data['article_content1']); ?>
                                            </div>
                                        </div>
                                        <div  class="hidden">
                                            <div class="summernote2 "  >
                                            <?php echo htmlspecialchars_decode($data['article_content2']); ?>
                                            </div>
                                        </div>
                    
                                        <?php if (isset($error['article_content'])) { ?><div class="text-danger"><?php echo $error['article_content']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <input type="hidden" value=""  name="article_content"  />
                                <input type="hidden" value=""  name="article_content1"  />
                                <input type="hidden" value=""  name="article_content2"  />
                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div class="text-center">
                                        <a href="javascript:;" class="btn btn-sm btn-success margin-r-5" id="post-data" style="padding: 0 30px;height: 40px;font-size: 14px;line-height: 40px;">提交</a>
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding: 0 30px;height: 40px;font-size: 14px;line-height: 40px;">返回</a>

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
    // $(function () {
    //      var lan = $("#lan").val();
    //         $("#tab>div").eq(lan).removeClass("hidden").siblings().addClass('hidden');
    // })
    // $("#lan").change(function () {
    //     var lan = $("#lan").val();
    //         $("#tab>div").eq(lan).removeClass("hidden").siblings().addClass('hidden');
    // })
           
            
        $("#lan button").click(function(){
            $("#lan button").eq($(this).index()).addClass("active").siblings().removeClass('active');
                var a =$(this).attr("name");
            $("#lan").attr('name',a);
            $("#tab>div").eq($("#lan button").index(this)).removeClass("hidden").siblings().addClass('hidden');
        });
    

        $(document).ready(function() {
            $("a").unbind();
            //$("a").unbind();

            $('.summernote,.summernote1,.summernote2').summernote({
                maxHeight : 1000,
                minHeight : 300,
                dialogsFade : true,
                dialogsInBody : true,
                disableDragAndDrop : false,
            });
            $("#post-data").click(function(){
                var content = $('.summernote').summernote("code");
                var content1 = $('.summernote1').summernote("code");
                var content2 = $('.summernote2').summernote("code");
                $("input[name=article_content]").val(content);
                $("input[name=article_content1]").val(content1);
                $("input[name=article_content2]").val(content2);
                $("#form").submit();
            });


        });



</script>
<?php echo $footer;?>