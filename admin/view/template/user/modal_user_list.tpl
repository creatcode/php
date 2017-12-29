<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>dist/css/font-awesome.min.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>dist/css/ionicons.min.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>dist/css/AdminLTE.min.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>dist/css/skins/_all-skins.min.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>plugins/iCheck/flat/blue.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>plugins/morris/morris.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>plugins/jvectormap/jquery-jvectormap-1.2.2.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>plugins/datepicker/datepicker3.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>plugins/daterangepicker/daterangepicker.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/"?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">
    <link rel="stylesheet" href="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.css" />
    <link rel="stylesheet" href="<?php echo $static ?>js/bootstrap-datetimepicker.min.css">
    <link rel="stylesheet" href="<?php echo $static ?>css/toastr.css">
    <link rel="stylesheet" href="<?php echo $static ?>css/animate.css">
    <link rel="stylesheet" href="<?php echo $static ?>base.css">
    <link rel="stylesheet" href="<?php echo $static ?>loading.css">

    <script src="<?php echo $static . "AdminLTE-2.3.7/"?>plugins/jQuery/jquery-2.2.3.min.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/"?>plugins/jQueryUI/jquery-ui.min.js"></script>

    <script>
        $.widget.bridge('uibutton', $.ui.button);
    </script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>bootstrap/js/bootstrap.min.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/raphael/raphael-min.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/morris/morris.min.js"></script>

    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/sparkline/jquery.sparkline.min.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>

    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/knob/jquery.knob.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/moment.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/daterangepicker/daterangepicker.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/slimScroll/jquery.slimscroll.min.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/fastclick/fastclick.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>dist/js/app.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.js" type="text/javascript"></script>

    <script src="<?php echo $static;?>js/bootstrap-datetimepicker.min.js"></script>
    <script src="<?php echo $static;?>js/toastr.js"></script>
    <script src="<?php echo $static;?>js/common.js"></script>
</head>
<body>
<section class="content">
    <h2 class="page-header">
        选择用户
    </h2>
    <p class="lead"></p>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <div class="dataTables_length fa-border">
                        <input type="text" name="mobile" id="mobile" class="input-xxlarge" placeholder="请输入手机号码查询" />
                        <div class="pull-right">
                            <button type="button" class="btn btn-primary btn-sm">搜索</button>
                        </div>
                    </div>
                </div>
                <div class="box-body">

                    <table class="">
                        <tbody>
                        <?php foreach($data_rows as $row) { ?>
                        <tr>
                            <td><input type="checkbox" name="mobile" value="<?php echo $row['user_id'];?>"></td>
                            <td><?php echo $row['mobile'];?></td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>
</body>
</html>