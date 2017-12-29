<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>充值管理</span>
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
                        <form id="myForm" class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <input type="hidden" name="pdc_id" value="<?php echo $pdc_id; ?>" >
                            <input type="hidden" name="type" id="type">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">用户：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['mobile'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">是否有待计费订单：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $has_waiting_checkout_order;?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">充值订单号：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_sn'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">充值时间：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_payment_time'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">支付方式：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_payment_name'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">支付途径：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_payment_type'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">充值订单状态：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_payment_state'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">押金金额：</label>
                                    <div class="col-sm-5">
                                        <h5><?php echo $recharge_info['pdr_amount'];?></h5>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">退款方式：</label>
                                    <div class="col-sm-5">
                                        <select id="apply_payment_type" name="apply_payment_type" class="form-control" onchange="showUinonpay(this)">
                                            <?php foreach($apply_payment_types as $k=>$v){ ?>
                                                <option value="<?php echo $k; ?>" <?php if($data['apply_payment_type']==$k) echo "selected=\"selected\""?>><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                        <?php if (isset($error['apply_payment_type'])) { ?><div class="text-danger"><?php echo $error['apply_payment_type']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">收款账号：</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="apply_account" value="<?php echo $data['apply_account']; ?>" class="form-control"><?php if (isset($error['apply_account'])) { ?><div class="text-danger"><?php echo $error['apply_account']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div id="unionpay-form" style="display: <?php echo $data['apply_payment_type']=='3'?'inline':'none'; ?>">
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">收款户名：</label>
                                        <div class="col-sm-5">
                                            <input type="text" name="apply_account_name" value="<?php echo $data['apply_account_name']; ?>" class="form-control"><?php if (isset($error['apply_account_name'])) { ?><div class="text-danger"><?php echo $error['apply_account_name']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">收款银行：</label>
                                        <div class="col-sm-5">
                                            <input type="text" name="apply_bank_name" value="<?php echo $data['apply_bank_name']; ?>" class="form-control"><?php if (isset($error['apply_bank_name'])) { ?><div class="text-danger"><?php echo $error['apply_bank_name']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-2 control-label">收款支行：</label>
                                        <div class="col-sm-5">
                                            <input type="text" name="apply_sub_bank_name" value="<?php echo $data['apply_sub_bank_name']; ?>" class="form-control"><?php if (isset($error['apply_sub_bank_name'])) { ?><div class="text-danger"><?php echo $error['apply_sub_bank_name']; ?></div><?php } ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">退款理由：</label>
                                    <div class="col-sm-5">
                                        <textarea name="cash_reason" class="form-control" rows="5"><?php echo $data['cash_reason']; ?></textarea>
                                        <?php if (isset($error['cash_reason'])) { ?><div class="text-danger"><?php echo $error['cash_reason']; ?></div><?php } ?>
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
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    function showUinonpay(obj){
        var index = obj.selectedIndex;
        var value = obj.options[index].value;
        if(value==3){
            document.getElementById("unionpay-form").style.display="inline";
        }else{
            document.getElementById("unionpay-form").style.display="none";
        }
        console.log(123);
    }
</script>
<?php echo $footer;?>
