<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>个人中心</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
    <?php echo $statistics_in_page_header;?>
</section>
<style>
    #level{
        width: 150px;
        margin-left: 0px;
        margin-top: 10px;
    }
    #level div:nth-child(1){
        border-radius: 5px 0px 0px 5px;
        border-right: 1px solid #ecf0f5;
    }
    #level div:nth-child(3){
        border-radius: 0px 5px 5px 0px;
        border-left: 1px solid #ecf0f5;
    }
</style>
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
                        <?php if (isset($success)) { ?>
                        <div class="alert bg-light-blue"><i class="fa fa-check-circle"></i>&nbsp;<?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">用户名</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $data['admin_name']; ?></h5>
                                    </div>
                                </div>
                                <!--<div class="form-group">
                                    <label class="col-sm-2 control-label">姓名</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="nickname" class="form-control" />
                                        <?php if (isset($error['nickname'])) { ?><div class="text-danger"><?php echo $error['nickname']; ?></div><?php } ?>
                                    </div>
                                </div>-->
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">密码</label>
                                    <div class="col-sm-5">
                                        <input type="password" name="password" class="form-control"  placeholder="不修改密码时可不填" />
                                        <div id="level" class="row text-center"><div class="col-xs-4 bg-gray">弱</div><div class="col-xs-4 bg-gray">中</div><div class="col-xs-4 bg-gray">强</div></div>
                                        <?php if (isset($error['password'])) { ?><div class="text-danger"><?php echo $error['password']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">重复密码</label>
                                    <div class="col-sm-5">
                                        <input type="password" name="confirm" class="form-control"  placeholder="不修改密码时可不填" />
                                        <div id="confirm-level" class="text-danger" style="display: none">密码不一致</div>
                                        <?php if (isset($error['confirm'])) { ?><div class="text-danger"><?php echo $error['confirm']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">注册时间</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $data['add_time']; ?></h5>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <div class="pull-right">
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5">提交</button>
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
    var password_pass = true;

    $(function(){
        $("input[name$='password']").keyup(function () {
            reg($(this).val(),'level');
        });
    })

    function reg(password,name){
        var strongRegex = new RegExp("^(?=.{6,})(?=.*[A-Z])(?=.*[a-z])(?=.*[0-9])(?=.*\\W).*$", "g");
        var mediumRegex = new RegExp("^(?=.{6,})(((?=.*[A-Z])(?=.*[a-z]))|((?=.*[A-Z])(?=.*[0-9]))|((?=.*[a-z])(?=.*[0-9]))).*$", "g");
        var enoughRegex = new RegExp("(?=.{6,}).*", "g");
        if (false == enoughRegex.test(password)) {
            $('#'+name+' div:nth-child(1)').removeClass('bg-gray');
            $('#'+name+' div:nth-child(2)').removeClass('bg-yellow');
            $('#'+name+' div:nth-child(3)').removeClass('bg-green');
            $('#'+name+' div:nth-child(1)').addClass('bg-red');
            password_pass = false;
        }
        else if (strongRegex.test(password)) {
            $('#'+name+' div:nth-child(1)').addClass('bg-red');
            $('#'+name+' div:nth-child(2)').addClass('bg-yellow');
            $('#'+name+' div:nth-child(3)').addClass('bg-green');
        }
        else if (mediumRegex.test(password)) {
            $('#'+name+' div:nth-child(1)').addClass('bg-red');
            $('#'+name+' div:nth-child(2)').addClass('bg-yellow');
            $('#'+name+' div:nth-child(3)').removeClass('bg-green');
            $('#'+name+' div:nth-child(3)').addClass('bg-gray');
        }
        else {
            $('#'+name+' div:nth-child(1)').removeClass('bg-gray');
            $('#'+name+' div:nth-child(2)').removeClass('bg-yellow');
            $('#'+name+' div:nth-child(3)').removeClass('bg-green');
            $('#'+name+' div:nth-child(1)').addClass('bg-red');
        }
    }
</script>
<?php echo $footer;?>