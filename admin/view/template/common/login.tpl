<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-COMPATIBLE" content="IE=edge">
  <title><?php echo $title; ?></title>
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
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
  <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>
  <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/slimScroll/jquery.slimscroll.min.js"></script>
  <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/fastclick/fastclick.js"></script>
  <script src="<?php echo $static . "AdminLTE-2.3.7/";?>dist/js/app.min.js"></script>
  <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.js" type="text/javascript"></script>

  <script src="<?php echo $static;?>js/bootstrap-datetimepicker.min.js"></script>
  <script src="<?php echo $static;?>js/toastr.js"></script>
</head>
<body class="hold-transition login-page" style="background: url(<?php echo $static;?>images/login_bg.png) fixed top center;">
<div style="max-width: 426px;margin: 7% auto;">
  <div class="login-logo box-header ">
    <div class="image">
      <img src="<?php echo $static;?>images/login_logo.png" class="img" alt="User Image">
    </div>
  </div>
  <div class="box-header panel panel-default" style="padding: 35px 25px 15px 25px;margin-bottom: 35px;">
    <div class="box-header text-center">
      <h1 class="panel-title" style="font-size: 24px;font-weight: 400;"><!--<i class="fa fa-lock">--></i>总后台管理系统</h1>
    </div>
    <div class="panel-body">
      <?php if (isset($success)) { ?>
      <div class="alert alert-success"><i class="fa fa-check-circle"></i> <?php echo $success; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
      <?php } ?>
      <?php if (isset($error['warning'])) { ?>
      <div class="alert alert-danger" style="padding: 10px;"><i class="fa fa-exclamation-circle"></i> <?php echo $error['warning']; ?>
        <button type="button" class="close" data-dismiss="alert">&times;</button>
      </div>
      <?php } ?>
      <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data">
        <div class="form-group">
          <!--<label for="input-username">用户名</label>-->
          <div class="input-group"><span class="input-group-addon" style="border: #FFFFFF;background: #F5F5F5;height: 48px;"><i class="fa fa-user"></i></span>
            <input type="text" name="username" value="" placeholder="用户名" id="input-username" class="form-control" style="border: #FFFFFF;background: #F5F5F5;height: 48px;" />
          </div>
        </div>
        <div class="form-group">
          <!--<label for="input-password">密码</label>-->
          <div class="input-group"><span class="input-group-addon" style="border: #FFFFFF;background: #F5F5F5;height: 48px;"><i class="fa fa-lock"></i></span>
            <input type="password" name="password" value="" placeholder="密码" id="input-password" class="form-control" style="border: #FFFFFF;background: #F5F5F5;height: 48px;" />
          </div>

        </div>
        <div class="form-group">
          <button type="submit" class="btn btn-block btn-success" style="background: #eb474c;border-color: #eb474c;height: 48px;"><!--<i class="fa fa-key"></i>-->登录</button>
        </div>
      </form>
      <!--<div id="footer" class="text-right" style="margin-top:18px;"><a href="<?php echo $forgotten_url; ?>" style="color: #eb474c;">忘记密码？</a></div>-->
    </div>
  </div>
  <footer id="footer" class="text-center" style="color: #999999;"><!--<a href="http://www.estronger.cn">小强单车</a>--> 小强单车&copy; 2016-2017 版权所有。<br /></footer>
</div>
</body>
</html>