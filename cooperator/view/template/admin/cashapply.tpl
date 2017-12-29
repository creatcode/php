<?php if($data){ ?>
<?php foreach($data as $v){ ?>
<tr><td><?php echo $v['type'] ?></td><td><?php echo $v['add_time'] ?></td><td>￥<?php echo $v['amount'] ?></td><td><?php echo $v['payment_type'] ?></td><td><?php echo $v['payment_state'] ?></td><td>
    </td></tr>
<?php } ?>
<?php if(count($data) == 10){ ?>
<tr class="has-more" data-next="2"><td colspan="10" style="background: #ffffff;border: none;text-align: center;"><div><button class="btn btn-xs btn-default btn-no-border">加载更多</button></div></td></tr>
<?php } ?>
<?php }else{ ?>
<tr><td colspan="6 class="text-center">暂无记录</td></tr>
<?php } ?>