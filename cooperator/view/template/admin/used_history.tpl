<?php if($records){ ?>
<?php foreach($records as $record) { ?>
<li>
    <div class="bike-info-list-img"><img src="<?php echo $record['avatar'] ? $record['avatar'] : $static.'images/nopic.jpg'; ?>" data-mfp-src="<?php echo $record['avatar'] ? $record['avatar'] : $static.'images/nopic.jpg'; ?>"></div>
    <div class="bike-info-list-detail">
        <div><i class="fa fa-user fa-fw"></i> <?php echo $record['user_name']; ?></div>
        <div><i class="fa fa-arrow-down fa-fw"></i> <?php echo $record['add_time']; ?></div>
        <div><i class="fa fa-hourglass-start fa-fw"></i> <?php echo $record['start_time']; ?></div>
        <div><i class="fa fa-hourglass-end fa-fw"></i> <?php echo $record['end_time']; ?></div>
        <div class="<?php echo $record['order_state'] == -1 || $record['order_state'] == 0? 'text-danger' : 'text-success'; ?>"><i class="fa fa-cogs fa-fw"></i> <?php echo $record['order_state_describe']; ?></div>
    </div>
</li>
<!--<li>
    <div class="bike-info-list-img"><img src="http://120.76.98.150/bike/static/fault/201703081612118531.jpg"></div>
    <div class="bike-info-list-detail">
        <div><i class="fa fa-user fa-fw"></i> 18565706886 <i class="fa fa-info-circle"></i></div>
        <div><i class="fa fa-clock-o fa-fw"></i> 2017年3月10日 14:44:00</div>
        <div><i class="fa fa-exclamation-triangle fa-fw"></i> 链条断了，链条断了，链条断了，链条断了，链条断了，链条断了</div>
        <div class="text-success"><i class="fa fa-cogs fa-fw"></i> 已处理 <i class="fa fa-info-circle"></i></div>
        <div><i class="fa fa-clock-o fa-fw"></i> 2017年3月10日 16:25:33</div>
    </div>
</li>-->
<?php } ?>
<?php if(!empty($records) && (count($records) >= $config_limit_admin)){ ?>
<li class="has-more" data-next="<?php echo $page; ?>"><button class="btn btn-xs btn-default btn-no-border">加载更多</button></li>
<?php } ?>
<?php }else{ ?>
<li colspan="5" class="text-center">暂无记录</li>
<?php } ?>