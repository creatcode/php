<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>服务器运行情况</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
    <?php echo $statistics_in_page_header;?>
</section>
<style>
    #server_list{
        list-style: none;
        line-height: 2;
        font-size: 18px;
        padding: 80px;
    }
    #server_list .lrc{
        text-align: right;
    }
    #server_list .line-margin {
        padding-bottom: 15px;
    }
</style>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <!-- tab 标签 -->
                <ul class="nav nav-tabs">
                    <li class="active"><a href="javascript:;" data-toggle="tab">服务器参数</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="server_list">
                        <table>
                            <tr>
                                <td class="lrc line-margin"> CPU使用率</td>
                                <td class="line-margin"> ：<?php echo $data['cpu_usage']; ?>%</td>
                            </tr>

                            <tr>
                                <td class="lrc"> 总内存</td>
                                <td> ：<?php echo $data['mem_total']; ?>K</td>
                            </tr>

                            <tr>
                                <td class="lrc"> 已用内存</td>
                                <td> ：<?php echo $data['true_mem_used']; ?>K</td>
                            </tr>

                            <tr>
                                <td class="lrc line-margin"> 内存使用率</td>
                                <td class="line-margin"> ：<?php echo $data['mem_usage']; ?>%</td>
                            </tr>


                            <?php foreach($data['hd_arr'] as $k => $v ){ ?>

                            <tr>
                                <td class="lrc"> 硬盘<?php echo $k+1; ?>可用</td>
                                <td> ：<?php echo $v['free']; ?></td>
                            </tr>

                            <tr>
                                <td class="lrc line-margin"> 硬盘<?php echo $k+1; ?>使用率</td>
                                <td class="line-margin"> ：<?php echo $v['usepae']; ?></td>
                            </tr>

                            <?php }?>


                            <tr>
                                <td class="lrc line-margin"> 正在运行的进程数</td>
                                <td class="line-margin"> ：<?php echo $data['tast_running']; ?></td>
                            </tr>

                            <tr>
                                <td class="lrc"> 测试时间</td>
                                <td> ：<?php echo $data['detection_time']; ?></td>

                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->

<?php echo $footer;?>
