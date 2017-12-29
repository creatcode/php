<?php if($parkings){ ?>
<?php foreach($parkings as $parking) { ?>
<li>
    <div class="bike-info-list-img"><img src="<?php echo $parking['file_image'] ? $parking['file_image'] : $static.'images/nopic.jpg'; ?>" data-mfp-src="<?php echo $parking['file_image'] ? $parking['file_image'] : $static.'images/nopic.jpg'; ?>"></div>
    <div class="bike-info-list-detail">
        <div><i class="fa fa-user fa-fw"></i> <?php echo $parking['user_name']; ?></div>
        <div><i class="fa fa-clock-o fa-fw"></i> <?php echo $parking['add_time']; ?></div>
        <div><i class="fa fa-exclamation-triangle fa-fw"></i> <?php echo $parking['type']; ?></div>
        <div><i class="fa fa-commenting-o fa-fw"></i> <?php echo $parking['content']; ?></div>
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
<?php if(!empty($parkings) && count($parkings) >= $config_limit_admin){ ?>
<li class="has-more" data-next="<?php echo $page; ?>"><button class="btn btn-xs btn-default btn-no-border">加载更多</button></li>
<?php } ?>
<?php }else{ ?>
<li colspan="5" class="text-center">暂无记录</li>
<?php } ?>