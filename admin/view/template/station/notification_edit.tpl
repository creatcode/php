<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo $title;?></span>
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
        
                        <form class="form-horizontal" method="post" action="">
							<div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t12'];?></label>
                                    <div class="col-sm-5">
                                        <select name="cooperator_id" class="form-control">
                                            <option value="0">调度</option>
                                           <option value="0">维修</option>
                                           
                                        </select>
                                       
                                    </div>
                                </div>
							<div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t13'];?></label>
                                    <div class="col-sm-5">
                                        <select name="cooperator_id" class="form-control">
                                            <option value="0">运维员a</option>
                                           <option value="0">运维员a</option>
                                           <option value="0">运维员a</option>
                                           <option value="0">运维员a</option>
										   <option value="0">运维员a</option>
                                           <option value="0">运维员a</option>
                                        </select>
                                       
                                    </div>
                                </div>
							<div class="form-group">
                                    <label class="col-sm-2 control-label"><?php echo @$lang['t14'];?></label>
                                    <div class="col-sm-5">
                                        <textarea  class="form-control date" rows="5"></textarea>
                                       
                                    </div>
                                </div>
                            <div class="form-group">
                                <div class="col-sm-8">
                                    <div style="text-align:center">
                                      
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5"  style="margin-right: 40px;padding:0 30px;height:40px;font-size:14px;"><?php echo @$lang['t15'];?></button>
                                        <a href="" class="btn btn-sm btn-default" style="padding:0 30px;height:40px;font-size:14px;line-height:40px"><?php echo @$lang['t16'];?></a>
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

<?php echo $footer;?>