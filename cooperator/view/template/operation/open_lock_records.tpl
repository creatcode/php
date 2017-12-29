<?php if($data){ ?>
<?php foreach($data as $item) { ?>
<li>
    <div class="bike-info-list-detail">
        <div><i class="fa fa-bicycle fa-fw"></i>单车编号：<?php echo $item['bicycle_sn']; ?></div>
        <div><i class="fa fa-clock-o fa-fw"></i>开锁时间：<?php echo date('Y-m-d H:i:s',$item['add_time']); ?></div>
    </div>
</li>
<?php } ?>
<?php if(!empty($data) && count($data) >= $config_limit_admin){ ?>
<li class="has-more" data-next="<?php echo $page; ?>"><button class="btn btn-xs btn-default btn-no-border">加载更多</button></li>
<?php } ?>
<?php }else{ ?>
<li colspan="5" class="text-center">暂无记录</li>
<?php } ?>