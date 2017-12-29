<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>财务结算表</span>
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
                            <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                                <select class="input-sm" name="cooperator_id">
                                    <option value="">合伙人</option>
                                    <?php if (is_array($cooperators) && !empty($cooperators)) { ?>
                                    <?php foreach($cooperators as $cooperator) { ?>
                                    <option value="<?php echo $cooperator['cooperator_id']; ?>" <?php echo $cooperator['cooperator_id']==$data['cooperator_id'] ? 'selected' : ''; ?>><?php echo $cooperator['cooperator_name']; ?></option>
                                    <?php } ?>
                                    <?php } ?>
                                </select>
                                <select class="input-sm" name="city">
                                    <option value>用户类型</option>
                                    <?php foreach($user_types as $k => $v) { ?>
                                    <option value="<?php echo $k; ?>" <?php echo (string)$k == $filter['user_type'] ? 'selected' : ''; ?>><?php echo $v; ?></option>
                                    <?php } ?>
                                </select>
                                <input type="text" name="payoff_time" value="<?php echo $data['payoff_time']; ?>" class="input-sm date-range" style="border: 1px solid #a9a9a9;width: 200px" placeholder="结算时间段"/>
                                <div class="pull-right">
                                    <button type='button' class="btn btn-primary btn-sm" onclick='search_lock()'><i class="fa fa-search"></i>&nbsp;搜索</button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">单车数</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="bicycle_total" value="<?php echo $data['bicycle_total']; ?>" class="form-control">
                                            <span class="input-group-addon">辆</span>
                                        </div>
                                        <?php if (isset($error['bicycle_total'])) { ?><div class="text-danger"><?php echo $error['bicycle_total']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">累计骑行次数</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="orders_total" value="<?php echo $data['orders_total']; ?>" class="form-control">
                                            <span class="input-group-addon">次</span>
                                        </div>
                                        <?php if (isset($error['orders_total'])) { ?><div class="text-danger"><?php echo $error['orders_total']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">周平均骑行次数/辆</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="daily_usage" value="<?php echo $data['daily_usage']; ?>" class="form-control">
                                        <?php if (isset($error['daily_usage'])) { ?><div class="text-danger"><?php echo $error['daily_usage']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">骑行金额</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="orders_amount" value="<?php echo $data['orders_amount']; ?>" class="form-control">
                                            <span class="input-group-addon">元</span>
                                        </div>
                                        <?php if (isset($error['orders_amount'])) { ?><div class="text-danger"><?php echo $error['orders_amount']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">合同提成比例</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="commission_ratio" value="<?php echo $data['commission_ratio']; ?>" class="form-control">
                                            <span class="input-group-addon">%</span>
                                        </div>
                                        <?php if (isset($error['commission_ratio'])) { ?><div class="text-danger"><?php echo $error['commission_ratio']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">收入金额</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="payoff_base" value="<?php echo $data['payoff_base']; ?>" class="form-control">
                                            <span class="input-group-addon">元</span>
                                        </div>
                                        <?php if (isset($error['payoff_base'])) { ?><div class="text-danger"><?php echo $error['payoff_base']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">补贴费用</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="subsidy" value="<?php echo $data['subsidy']; ?>" class="form-control">
                                            <span class="input-group-addon">元</span>
                                        </div>
                                        <?php if (isset($error['subsidy'])) { ?><div class="text-danger"><?php echo $error['subsidy']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">总部成本收回</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="cost_recovery" value="<?php echo $data['cost_recovery']; ?>" class="form-control">
                                            <span class="input-group-addon">元</span>
                                        </div>
                                        <?php if (isset($error['cost_recovery'])) { ?><div class="text-danger"><?php echo $error['cost_recovery']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">支付合计</label>
                                    <div class="col-sm-5">
                                        <div class="input-group col-sm-12">
                                            <input type="text" name="payoff_amount" value="<?php echo $data['payoff_amount']; ?>" class="form-control">
                                            <span class="input-group-addon">元</span>
                                        </div>
                                        <?php if (isset($error['payoff_amount'])) { ?><div class="text-danger"><?php echo $error['payoff_amount']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">收款账户</label>
                                    <div class="col-sm-5">
                                        <input type="text" name="account_payee" value="<?php echo $data['account_payee']; ?>" class="form-control">
                                        <?php if (isset($error['account_payee'])) { ?><div class="text-danger"><?php echo $error['account_payee']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">备注</label>
                                    <div class="col-sm-5">
                                        <textarea name="payoff_remarks" class="form-control"><?php echo $data['payoff_remarks']; ?></textarea>
                                        <?php if (isset($error['payoff_remarks'])) { ?><div class="text-danger"><?php echo $error['payoff_remarks']; ?></div><?php } ?>
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
<!-- /.content -->

<script type="text/javascript">
    $('.date-range').daterangepicker({
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
        }
    });

    // 周平均骑行次数/辆 = 累计骑行次数 ÷ 单车数 ÷ 天数
    $('[name="bicycle_total"], [name="orders_total"]').change(function() {
        var bicycle_total = $('[name="bicycle_total"]').val() || 0,
            orders_total = $('[name="orders_total"]').val() || 0,
            payoff_time = $('[name="payoff_time"]').val(),
            daily_usage = days = 0;
        if (payoff_time.indexOf(" 至 ") > -1) {
            payoff_time = payoff_time.split(" 至 ");
            if (payoff_time.length == 2) {
                days = dateDiff(payoff_time[0], payoff_time[1]);
            }
        }
        days += 1;
        daily_usage = (bicycle_total == 0) ? 0 : accDiv(accDiv(orders_total, bicycle_total), days).toFixed(2);
        $('[name="daily_usage"]').val(daily_usage);
    });

    // 收入金额 = 骑行金额 × 合同提成比例
    $('[name="orders_amount"], [name="commission_ratio"]').change(function() {
        var orders_amount = $('[name="orders_amount"]').val() || 0,
            commission_ratio = accDiv(($('[name="commission_ratio"]').val() || 0), 100),
            payoff_base = accMul(orders_amount, commission_ratio);
        $('[name="payoff_base"]').val(payoff_base);
        $('[name="payoff_base"]').trigger('change');
    });

    // 总部成本收回 = 骑行金额 - 收入金额
    $('[name="orders_amount"], [name="payoff_base"]').change(function() {
        var orders_amount = $('[name="orders_amount"]').val() || 0,
            payoff_base = $('[name="payoff_base"]').val() || 0,
            cost_recovery = orders_amount - payoff_base;
        $('[name="cost_recovery"]').val(cost_recovery);
    });

    // 支付合计 = 收入金额 + 补贴费用
    $('[name="payoff_base"], [name="subsidy"]').change(function() {
        var payoff_base = $('[name="payoff_base"]').val() || 0,
            subsidy = $('[name="subsidy"]').val() || 0,
            payoff_amount = accAdd(payoff_base, subsidy);
        $('[name="payoff_amount"]').val(payoff_amount);
    });

    // 搜索合伙人结算信息
    function search_lock() {
        var cooperator_id = $('[name="cooperator_id"]').val(),
            payoff_time = $('[name="payoff_time"]').val();
        if (!cooperator_id) {
            alert('请选择合伙人');
            return false;
        }
        if (!payoff_time) {
            alert('请输入结算时间段');
            return false;
        }
        $.ajax(location.href, {
            dataType: 'json',
            data: {cooperator_id: cooperator_id, payoff_time: payoff_time},
            method: 'POST',
            beforeSend: function(request) {
                request.setRequestHeader("channel", "api");
            },
            success: function (result) {
                if(!result.errorCode) {
                    $('[name="bicycle_total"]').val(result.data.bicycle_total).trigger('change');
                    $('[name="orders_amount"]').val(result.data.orders_amount).trigger('change');
                    $('[name="orders_total"]').val(result.data.orders_total).trigger('change');
                }
            }
        });
    }

    // *************************************************** 辅助函数 ***************************************************
    //除法函数，用来得到精确的除法结果
    //说明：javascript的除法结果会有误差，在两个浮点数相除的时候会比较明显。这个函数返回较为精确的除法结果。
    //调用：accDiv(arg1,arg2)
    //返回值：arg1除以arg2的精确结果
    function accDiv(arg1,arg2){
        var t1=0,t2=0,r1,r2;
        try{t1=arg1.toString().split(".")[1].length}catch(e){}
        try{t2=arg2.toString().split(".")[1].length}catch(e){}
        with(Math){
            r1=Number(arg1.toString().replace(".",""))
            r2=Number(arg2.toString().replace(".",""))
            return (r1/r2)*pow(10,t2-t1);
        }
    }

    //乘法函数，用来得到精确的乘法结果
    //说明：javascript的乘法结果会有误差，在两个浮点数相乘的时候会比较明显。这个函数返回较为精确的乘法结果。
    //调用：accMul(arg1,arg2)
    //返回值：arg1乘以 arg2的精确结果
    function accMul(arg1, arg2) {
        var m=0,s1=arg1.toString(),s2=arg2.toString();
        try{m+=s1.split(".")[1].length}catch(e){}
        try{m+=s2.split(".")[1].length}catch(e){}
        return Number(s1.replace(".",""))*Number(s2.replace(".",""))/Math.pow(10,m)
    }

    //加法函数，用来得到精确的加法结果
    //说明：javascript的加法结果会有误差，在两个浮点数相加的时候会比较明显。这个函数返回较为精确的加法结果。
    //调用：accAdd(arg1,arg2)
    //返回值：arg1加上arg2的精确结果
    function accAdd(arg1, arg2) {
        var r1,r2,m;
        try{r1=arg1.toString().split(".")[1].length}catch(e){r1=0}
        try{r2=arg2.toString().split(".")[1].length}catch(e){r2=0}
        m=Math.pow(10,Math.max(r1,r2));
        return (arg1*m+arg2*m)/m
    }

    //计算天数差的函数，用来得到两个日期相差的天数
    //说明：sDate1和sDate2是2006-12-18格式
    //调用：dateDiff(sDate1,sDate2)
    //返回值：dateDiff('2017-6-1', '2017-6-30') = 29
    function  dateDiff(sDate1,  sDate2) {
        var  aDate,  oDate1,  oDate2,  iDays
        aDate  =  sDate1.split("-")
        oDate1  =  new  Date(aDate[1]  +  '-'  +  aDate[2]  +  '-'  +  aDate[0])    //转换为12-18-2006格式
        aDate  =  sDate2.split("-")
        oDate2  =  new  Date(aDate[1]  +  '-'  +  aDate[2]  +  '-'  +  aDate[0])
        iDays  =  parseInt(Math.abs(oDate1  -  oDate2)  /  1000  /  60  /  60  /24)    //把相差的毫秒数转换为天数
        return  iDays
    }
</script>
<?php echo $footer;?>