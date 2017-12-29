<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>管理员</span>
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
                                    <label class="col-sm-2 control-label">管理员名称</label>
                                    <div class="col-sm-5">
                                        <?php if (empty($admin_id)) { ?>
                                        <input type="text" name="admin_name" value="<?php echo $data['admin_name']; ?>" class="form-control" />
                                        <?php if (isset($error['admin_name'])) { ?><div class="text-danger"><?php echo $error['admin_name']; ?></div><?php } ?>
                                        <?php } else { ?>
                                        <h5><?php echo $data['admin_name']; ?></h5>
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">昵称</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="nickname" value="<?php echo $data['nickname']; ?>" class="form-control" />
                                        <?php if (isset($error['nickname'])) { ?><div class="text-danger"><?php echo $error['nickname']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">角色</label>
                                    <div class="col-sm-5">
                                        <select name="role_id" class="form-control">
                                            <?php foreach($roles as $k => $v) { ?>
                                            <option value="<?php echo $k; ?>" <?php echo (string)$k == $data['role_id'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['role_id'])) { ?><div class="text-danger"><?php echo $error['role_id']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">密码</label>
                                    <div class="col-sm-5">
                                        <input type="password" name="password" class="form-control"  placeholder="不修改密码时可不填" />
                                        <?php if (isset($error['password'])) { ?><div class="text-danger"><?php echo $error['password']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">重复密码</label>
                                    <div class="col-sm-5">
                                        <input type="password" name="confirm" class="form-control"  placeholder="不修改密码时可不填" />
                                        <?php if (isset($error['confirm'])) { ?><div class="text-danger"><?php echo $error['confirm']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">状态</label>
                                    <div class="col-sm-5">
                                        <input type="checkbox" name="state" value="1" placeholder="状态" class="bootstrap-switch in-list" data-on-text="启用" data-off-text="停用" data-label-width="5" <?php echo $data['state']==1 ? 'checked' : ''; ?> />
                                        <?php if (isset($error['state'])) { ?><div class="text-danger"><?php echo $error['state']; ?></div><?php } ?>
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
<link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.css" />
<script type="text/javascript" src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
<script type="text/javascript">
        $(document).ready(function() {
            $('input.bootstrap-switch').bootstrapSwitch();
        });
</script>
<?php echo $footer;?>