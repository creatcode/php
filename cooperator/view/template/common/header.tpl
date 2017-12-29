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
    <link rel="stylesheet" href="<?php echo $static ?>css/animate.css">
    <link rel="stylesheet" href="<?php echo $static ?>base.css?v=2">
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
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>dist/js/app.js"></script>
    <script src="<?php echo $static . "AdminLTE-2.3.7/";?>plugins/bootstrap-switch/bootstrap-switch.min.js" type="text/javascript"></script>

    <script src="<?php echo $static;?>js/bootstrap-datetimepicker.min.js"></script>
    <script src="<?php echo $static;?>js/toastr.js"></script>
    <script src="<?php echo $static;?>js/common.js"></script>
    <script>
        //收藏
        collectList();
        function collect(menu_id,tag){
            $.get("<?php echo $http_server ?>/index.php?route=user/collect/collect",{menu_id:menu_id},function(result){
                if(result.data.status == 1){
                    $(tag).children("i").attr("class","fa fa-star no-margin text-yellow");
                }else if(result.data.status == 0){
                    $(tag).children("i").attr("class","fa fa-star-o text-gray");
                }
                collectList();
            });
        }
        function collectCancel(menu_id,tag){
            $.get("<?php echo $http_server ?>/index.php?route=user/collect/collect",{menu_id:menu_id},function(result){
//                    console.log($(tag).attr('menu_action_route'));
//                    console.log(top.location.href);
                if($(tag).attr('menu_action_route') == stringToArr(top.location.href)){
                    $(".content-header").children("h1").children("a").children("i").attr("class","fa fa-star-o text-gray");
                }
                collectList();
            });
        }
        function collectList(){
            $.get("<?php echo $http_server ?>/index.php?route=user/collect",function(result){
                var html = "";
                jQuery.each(result.data, function(i, val) {
                    html += '<li class="header"><a class="collect-menu-a" href="javascript:;" menu_action="' + val.menu_action + '"><i class="fa ' + val.menu_icon + ' text-aqua"></i><span> ' + val.menu_name + ' </span><span class="pull-right-container"><i menu_action_route = "'+ val.menu_action_route +'" onclick="collectCancel('+ val.menu_id +',this)" class="collectCancel fa fa-close pull-right"></i></span></a></li>';
                });
                $('#collect-menu').html(html);
            });
        }
        function stringToArr(string){
            var array = string.split("=");
            var arr = array[1].split("/");
            if(arr.length > 1){
                return arr[0] + '/' + arr[1];
            }
            return string;
        }
        $(document).on('click', ".collect-menu-a", function(){
            window.location.href = $(this).attr('menu_action');
        });
        $(document).on('click','.collectCancel',function(){
            return false;
        });

        //菜单搜索
        function searchMenu(input){
            var val = input.value;
//                var arr = [];
            $(".search-menu li").hide();
            $.each($(".search-menu li"),function(){
                if($(this).children("a").children("span").text().indexOf(val) != -1){
                    $(this).show();
//                        arr.push($(this).children("a").children("span").text());
                    if($(this).parents("li")){
                        $(this).parents("li").show();
                        $(this).parents("li").attr("class","active");
                    }
                }
                if(val == ""){
                    $(this).parents("li").attr("class","");
                }
            });
//                console.log(arr);
//                if(arr.length == 1){
//                    $(input).val(arr[0]);
//                }
        }
    </script>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
    <header class="main-header">
        <!-- Logo -->
        <a href="index.php?route=admin/index" class="logo" style="border-right: 1px solid #ccc;background-color: #ffffff;">
            <!-- mini logo for sidebar mini 50x50 pixels -->
            <span class="logo-mini"><img src="<?php echo $static;?>images/50x50.png"/></span>
            <!-- logo for regular state and mobile devices -->
            <span class="logo-lg"><img src="<?php echo $static;?>images/200x50.png"/></span>
        </a>
        <!-- Header Navbar: style can be found in header.less -->
        <nav class="navbar navbar-static-top">
            <!-- Sidebar toggle button-->
            <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                <span class="sr-only">Toggle navigation</span>
            </a>

            <div class="navbar-custom-menu">
                <ul class="nav navbar-nav">
                    <!-- 首页 -->
                    <li class="dropdown">
                        <a href="index.php?route=admin/index" style="padding: 12px;">
                            <i class="fa fa-home no-margin" style="font-size: 24px;"></i>
                        </a>
                    </li>
                    <!-- 收藏夹 -->
                    <li class="dropdown notifications-menu">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" style="padding: 12px;">
                            <i class="fa fa-star no-margin" style="font-size: 24px;"></i>
                        </a>
                        <ul class="dropdown-menu" id="collect-menu">

                        </ul>
                    </li>
                    <!-- 消息中心 -->
                    <li class="dropdown">
                        <a href="javascript:;" id="button-message" class="dropdown-toggle" data-toggle="dropdown" style="padding: 12px;">
                            <i class="fa fa-envelope-o no-margin" style="font-size: 24px; position: relative">
                                <span class="label label-danger" style="font-size:9px; position: absolute; padding: 3px 5px; min-width: 20px; border-radius: 10px; max-height: 23px; top: -10px; right: -10px;"></span>
                            </i>
                        </a>
                        <div class="dropdown-menu" id="message-dropdown">
                            <div class="btn-group btn-group-justified" data-toggle="buttons">
                                <label class="btn btn-default violation active" data-action="<?php echo $violation_action; ?>">
                                    违停 <span></span>
                                </label>
                                <label class="btn btn-default fault" data-action="<?php echo $fault_action; ?>">
                                    故障 <span></span>
                                </label>
                                <label class="btn btn-default other" data-action="<?php echo $other_action; ?>">
                                    其他 <span></span>
                                </label>
                            </div>
                            <div class="message-list">
                                <div class="col-sm-12">
                                </div>
                                <p class="loader-wrapper"><span class="loader">正在加载...</span></p>
                            </div>
                            <span class="update-info">
                                最后刷新：<span></span>
                                <button type="button" class="btn btn-xs btn-default pull-right">
                                    <i class="fa fa-refresh"></i>
                                </button>
                            </span>


                        </div>
                    </li>
                    <!-- 个人中心 -->
                    <li class="dropdown user user-menu">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
                            <img src="<?php echo $static;?>/AdminLTE-2.3.7/dist/img/user2-160x160.jpg" class="user-image" alt="User Image">
                            <span class="hidden-xs"><?php echo $nickname; ?></span>
                        </a>
                        <ul class="dropdown-menu">
                            <!-- User image -->
                            <li class="user-header">
                                <img src="<?php echo $static; ?>/AdminLTE-2.3.7/dist/img/user2-160x160.jpg" class="img-circle" alt="User Image">
                                <p>
                                    <?php echo $nickname; ?>
                                    <!--区域 - 都江堰市-->
                                    <small>最后登录时间：<?php echo $login_time; ?></small>
                                </p>
                            </li>
                            <!-- Menu Body
                            <li class="user-body">
                                <button type="button" class="btn btn-block btn-warning" onclick="location.href='<?php echo $logout_url; ?>'">退出</button>
                            </li>-->
                            <li class="user-footer">
                                <div class="pull-left">
                                    <button class="btn btn-default btn-flat" onclick="location.href='<?php echo $information; ?>'">个人中心</button>
                                </div>
                                <div class="pull-right">
                                    <button type="button" class="btn btn-block btn-warning" onclick="location.href='<?php echo $logout_url; ?>'">退出登陆</button>
                                </div>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>
    </header>
    <!-- Left side column. contains the logo and sidebar -->
    <aside class="main-sidebar">
        <!-- sidebar: style can be found in sidebar.less -->
        <section class="sidebar">
            <!-- Sidebar user panel -->
            <div class="header-panel panel-body">
                <a href="<?php echo $home_action; ?>">
                    <i class="fa fa-dashboard" style="color: white;font-size: 40px;padding: 0 5px;/* padding-left: 5px; */position: relative;top: 2px;"></i>&nbsp;<span style="color: white;font-size: 20px;letter-spacing: 5px;">仪表盘</span>
                </a>
            </div>
            <!-- search form -->
            <form action="#" method="get" class="sidebar-form">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="Search..." onkeyup="searchMenu(this)">
                    <span class="input-group-btn">
                <button type="submit" name="search" id="search-btn" class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
                </div>
            </form>
            <!-- /.search form -->
            <!-- sidebar menu: : style can be found in sidebar.less -->
            <?php echo $menu; ?>
        </section>
        <!-- /.sidebar -->
    </aside>
    <div class="ajax-content">
        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper" id="content-wrapper">
