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
                        <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo $title?></a></li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane active" id="bicycle">
                            <?php if (isset($error['warning'])) { ?>
                            <div class="alert alert-danger" style="opacity: 0.8;"><i
                                        class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                            <?php } ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">短信平台</label>
                                <div class="col-sm-5">
                                    <?php echo $data['sms_platform']; ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">模板内容</label>
                                <div class="col-sm-5 original-template">
                                    <?php echo $data['template_text']; ?>
                                </div>
                            </div>
                            <?php for($i=0;$i<$data['block'];$i++){ ?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">填写地方<?php echo $i+1?></label>
                                <div class="col-sm-5">
                                    <input type="text" name="message_block_<?php echo $i?>"
                                           value="<?php echo isset($info['message_block'][$i])?$info['message_block'][$i]:''; ?>"
                                           class="form-control message_block_<?php echo $i?>">
                                </div>
                            </div>
                            <?php }?>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">编辑后的模板</label>
                                <div class="col-sm-5">
                                    <textarea readonly name="template_text" class="form-control" rows="3"><?php echo isset($info['template_text'])?$info['template_text']:'' ; ?></textarea>
                                    <span class="template-text-num"></span>
                                    <?php if (isset($error['template_text'])) { ?>
                                    <div class="text-danger"><?php echo $error['template_text']; ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">手机号码</label>
                                <div class="col-sm-5">
                                        <textarea name="mobiles" class="form-control" rows="3"
                                                  placeholder="请输入用户手机号，每行一个"><?php echo isset($info['mobiles']) ? $info['mobiles'] : '' ; ?></textarea>
                                    <?php if (isset($error['mobiles'])) { ?>
                                    <div class="text-danger"><?php echo $error['mobiles']; ?></div>
                                    <?php } ?>
                                </div>
                            </div>
                            <input type="hidden" name="message_blocks" value="<?php echo $info['message_blocks']?>">
                            <input type="hidden" name="template_id" value="<?php echo $data['template_id']?>">
                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div class="pull-right">
                                        <button type="button" onclick="combineStrings()"
                                                class="btn btn-sm btn-warning margin-r-5">生成模板
                                        </button>
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5">发送</button>
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
<script type="text/javascript">
    function combineStrings() {
        var block_num = <?php echo $data['block']; ?>;
        var template = $.trim($('.original-template').text());
        var template_array = template.split('#');
        var message_blocks = '';
        for (var i = 0; i < block_num; i++) {
            message_blocks+=$('.message_block_' + i).val()+'|';
            template_array[i+i+1] = $('.message_block_' + i).val();
        }
        var new_template = template_array.join('');
        $('[name="template_text"]').val(new_template);
        $('.template-text-num').text(new_template.length);
        message_blocks = message_blocks.substring(0,message_blocks.length-1);
        $('[name="message_blocks"]').val(message_blocks);
        console.log(message_blocks);
    }
</script>

<?php echo $footer;?>