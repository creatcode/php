<?php if($data){ ?>
<?php foreach($data as $v){ ?>
<tr><td><?php echo $v['add_time'] ?></td><td><?php echo $v['points'] ?></td><td><?php echo $v['point_desc'] ?></td><td><?php echo $v['admin_name']?$v['admin_name']:'-'; ?></td><td><?php echo $v['remarks']?$v['remarks']:'-'; ?></td></tr>
<?php } ?>
<?php if(count($data) == 10){ ?>
<tr class="has-more" data-next="2"><td colspan="10" style="background: #ffffff;border: none;text-align: center;"><div><button class="btn btn-xs btn-default btn-no-border">加载更多</button></div></td></tr>
<?php } ?>
<?php }else{ ?>
<tr><td colspan="5" class="text-center">暂无记录</td></tr>
<?php } ?>