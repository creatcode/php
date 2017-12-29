<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo $title; ?></span>
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
                                <input type="hidden" name="order_id" value="<?php echo $order_info['order_id'];?>">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">礼品：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $order_info['gift_name'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">兑换数量：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $order_info['gift_num'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">状态：</label>
                                    <div class="col-sm-5">
                                        <select name="state" class="form-control">
                                            <option value="">--请选择--</option>
                                            <?php foreach($states as $key => $val) { ?>
                                            <option value="<?php echo $key; ?>" <?php echo (string)$key == $data['state'] ? 'selected' : ''; ?>><?php echo $val; ?>
                                            </option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['state'])) { ?><div class="text-danger"><?php echo $error['state']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">收件人：</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="consignee" value="<?php echo $data['consignee']; ?>" class="form-control">
                                        <?php if (isset($error['consignee'])) { ?><div class="text-danger"><?php echo $error['consignee']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">联系电话：</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="phone" value="<?php echo $data['phone']; ?>" class="form-control">
                                        <?php if (isset($error['phone'])) { ?><div class="text-danger"><?php echo $error['phone']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">收货地址：</label>
                                    <div class="col-sm-5">
                                        <textarea name="address" class="form-control" rows="5"><?php echo $data['address']; ?></textarea>
                                        <?php if (isset($error['address'])) { ?><div class="text-danger"><?php echo $error['address']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">物流公司：</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="shipping_company" value="<?php echo $data['shipping_company']; ?>" class="form-control">
                                        <?php if (isset($error['shipping_company'])) { ?><div class="text-danger"><?php echo $error['shipping_company']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">物流单号：</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="shipping_code" value="<?php echo $data['shipping_code']; ?>" class="form-control">
                                        <?php if (isset($error['shipping_code'])) { ?><div class="text-danger"><?php echo $error['shipping_code']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">发货时间：</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="shipping_time" value="<?php echo date('Y-m-d H:i:s',$data['shipping_time']); ?>" class="form-control" />
                                        <?php if (isset($error['shipping_time'])) { ?><div class="text-danger"><?php echo $error['shipping_time']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">备注：</label>
                                    <div class="col-sm-5">
                                        <textarea name="remark" class="form-control" rows="5"><?php echo $data['remark']; ?></textarea>
                                        <?php if (isset($error['remark'])) { ?><div class="text-danger"><?php echo $error['remark']; ?></div><?php } ?>
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
<link rel="stylesheet" href="<?php echo HTTP_IMAGE . 'AdminLTE-2.3.7/';?>plugins/bootstrap-switch/bootstrap-switch.min.css" />
<script type="text/javascript" src="<?php echo HTTP_IMAGE . 'AdminLTE-2.3.7/'; ?>plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
<script type="text/javascript" src="<?php echo HTTP_IMAGE . 'AdminLTE-2.3.7/'; ?>plugins/bootstrap-switch/bootstrap-switch.min.js"></script>
<script type="text/javascript">
    $(document).ready(function() {
        // 开关控件
        $('input.bootstrap-switch').bootstrapSwitch();
        // 时间控件
        $('.date-range').daterangepicker({
            locale:{
                format: 'YYYY-MM-DD',
                isAutoVal:false,
            }
        });
        $('.date-time').datetimepicker({
            locale:{
                format: 'yyyy-mm-dd',
                isAutoVal:false,
                //autoclose: true,
            }
        });
    });

</script>
<?php echo $footer;?>