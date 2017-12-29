<?php if($data){ ?>
<?php foreach($data as $item) { ?>
<li>
    <div class="bike-info-list-detail">
        <div><i class="fa fa-bicycle fa-fw"></i>单车SN：<?php echo $item['bicycle_sn']; ?></div>
        <div><i class="fa fa-clock-o fa-fw"></i>修理时间：<?php echo date('Y-m-d H:i:s',$item['add_time']); ?></div>
        <div><i class="fa fa-check-square-o fa-fw"></i>处理类型：<?php echo $item['handle_type']?></div>
    </div>
</li>
<?php } ?>
<?php if(!empty($data) && count($data) >= $config_limit_admin){ ?>
<li class="has-more" data-next="<?php echo $page; ?>"><button class="btn btn-xs btn-default btn-no-border">加载更多</button></li>
<?php } ?>
<?php }else{ ?>
<li colspan="5" class="text-center">暂无记录</li>
<?php } ?>