<?php if($data){ ?>
<?php foreach($data as $v){ ?>
<tr>
    <td id="order-bicycle_sn" style="cursor:pointer;"><?php echo $v['bicycle_sn'] ?></td>
    <td><?php echo $v['region_name'] ?></td>
    <td><?php echo $v['city_name'] ?></td>
    <td>￥<?php echo $v['pay_amount'] ?></td>
    <td title="下单时间：<?php echo $v['add_time'] ?>&#13;开始时间：<?php echo $v['start_time'] ?>&#13;结束时间：<?php echo $v['end_time'] ?>"><?php echo $v['settlement_time'] ?></td>
    <td style="text-align:center;"><?php echo $v['coupon'] ?></td>
    <td><?php echo $v['is_limit_free'] ?></td>
    <td><?php echo $v['is_month_card'] ?></td>
    <td><?php echo $v['order_state'] ?></td>
    <!--<td><button class="btn btn-xs btn-default">轨迹</button></td>-->
</tr>
<?php } ?>
<?php if(count($data) == 10){ ?>
<tr class="has-more" data-next="2"><td colspan="10" style="background: #ffffff;border: none;text-align: center;"><div><button class="btn btn-xs btn-default btn-no-border">加载更多</button></div></td></tr>
<?php } ?>
<?php }else{ ?>
<tr><td colspan="8" class="text-center">暂无记录</td></tr>
<?php } ?>
