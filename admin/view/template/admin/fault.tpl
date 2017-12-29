<?php if($faults){ ?>
<?php foreach($faults as $fault) { ?>
<li>
    <div class="bike-info-list-img"><img src="<?php echo $fault['fault_image'] ? $fault['fault_image'] : $static.'images/nopic.jpg'; ?>" data-mfp-src="<?php echo $fault['fault_image'] ? $fault['fault_image'] : $static.'images/nopic.jpg'; ?>"></div>
    <div class="bike-info-list-detail">
        <div><i class="fa fa-user fa-fw"></i> <?php echo $fault['user_name']; ?></div>
        <div><i class="fa fa-clock-o fa-fw"></i> <?php echo $fault['add_time']; ?></div>
        <div><i class="fa fa-exclamation-triangle fa-fw"></i> <?php echo $fault['fault_type']; ?></div>
        <?php if($fault['processed'] == '已处理'){ ?>
        <div class="text-success"><i class="fa fa-cogs fa-fw"></i><?php $fault['fault_type'] ?> <?php echo $fault['processed']; ?> <i class="fa fa-info-circle"> </i></div>
        <?php }else{ ?>
        <?php if($fault['fault_type'] != '结束不了订单'){ ?>
        <div class="text-danger"><i class="fa fa-cogs fa-fw"></i> <?php echo $fault['processed']; ?> <i data-fault-id="<?php echo $fault['fault_id']; ?>"  class="fa fa-pencil-square bike-lock-info fault-handling-modal" ></i></div>
        <?php }else{ ?>
        <div class="text-danger"><i class="fa fa-cogs fa-fw"></i> <?php echo $fault['processed']; ?> <i id="fault_type_12" data-fault-id="<?php echo $fault['fault_id']; ?>"  class="fa fa-pencil-square" style="cursor:pointer;"></i></div>
        <?php } ?>
        <?php } ?>
    </div>
</li>
<!--<li>
    <div class="bike-info-list-img"><img src="http://120.76.98.150/bike/static/fault/201703081612118531.jpg" data-mfp-src="http://120.76.98.150/bike/static/fault/201703081612118531.jpg"></div>
    <div class="bike-info-list-detail">
        <div><i class="fa fa-user fa-fw"></i> 18565706886 <i class="fa fa-info-circle"></i></div>
        <div><i class="fa fa-clock-o fa-fw"></i> 2017年3月10日 14:44:00</div>
        <div><i class="fa fa-exclamation-triangle fa-fw"></i> 链条断了，链条断了，链条断了，链条断了，链条断了，链条断了</div>
        <div class="text-success"><i class="fa fa-cogs fa-fw"></i> 已处理 <i class="fa fa-info-circle"></i></div>
        <div><i class="fa fa-clock-o fa-fw"></i> 2017年3月10日 16:25:33</div>
    </div>
</li>-->
<?php } ?>
<?php if(!empty($faults) && count($faults) >= $config_limit_admin){ ?>
<li class="has-more" data-next="2"><button class="btn btn-xs btn-default btn-no-border">加载更多</button></li>
<?php } ?>
<?php }else{ ?>
<li colspan="5" class="text-center">暂无记录</li>
<?php } ?>