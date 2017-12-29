<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>支付宝设置</span>
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
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-alipay-seller-id">支付宝账号</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="config_alipay_seller_id" value="<?php echo $data['config_alipay_seller_id']; ?>" placeholder="支付宝账号" id="input-alipay-seller-id" class="form-control" />
                                        <?php if (isset($error['config_alipay_seller_id'])) { ?><div class="text-danger"><?php echo $error['config_alipay_seller_id']; ?></div><?php } ?>
                                        <small class="help-block">支付宝账号，以2088开头由16位纯数字组成的字符串，一般情况下收款账号就是签约账号</small>
                                    </div>
                                </div>
                                <!-- 交易安全校验码（key） -->
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-alipay-key">交易安全校验码（key）</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="config_alipay_key" value="<?php echo $data['config_alipay_key']; ?>" placeholder="交易安全校验码（key）" id="input-alipay-key" class="form-control" />
                                        <?php if (isset($error['config_alipay_key'])) { ?><div class="text-danger"><?php echo $error['config_alipay_key']; ?></div><?php } ?>
                                        <small class="help-block"></small>
                                    </div>
                                </div>
                                <!-- 合作者身份（partner ID） -->
                                <div class="form-group required">
                                    <label class="col-sm-2 control-label" for="input-alipay-partner">合作者身份（partner ID）</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="config_alipay_partner" value="<?php echo $data['config_alipay_partner']; ?>" placeholder="合作者身份（partner ID）" id="input-alipay-partner" class="form-control" />
                                        <?php if (isset($error['config_alipay_partner'])) { ?><div class="text-danger"><?php echo $error['config_alipay_partner']; ?></div><?php } ?>
                                        <small class="help-block">合作身份者ID，签约账号，以2088开头由16位纯数字组成的字符串，查看地址：https://b.alipay.com/order/pidAndKey.htm</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-7">
                                    <button type="submit" class="btn btn-large btn-success pull-right">提交</button>
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