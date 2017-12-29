<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>区域管理</span>
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
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="row">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">区域名称</label>
                                    <div class="col-sm-8">
                                        <div class="input-group">
                                            <input type="text" name="region_name" value="<?php echo $data['region_name']; ?>" class="form-control" />
                                            <span class="input-group-btn"><button type="button" class="btn btn-primary button-location">快速定位</button></span>
                                        </div>
                                        <?php if (isset($error['region_name'])) { ?><div class="text-danger"><?php echo $error['region_name']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">城市区号</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="region_city_code" value="<?php echo $data['region_city_code']; ?>" class="form-control">
                                        <?php if (isset($error['region_city_code'])) { ?><div class="text-danger"><?php echo $error['region_city_code']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">排序</label>
                                    <div class="col-sm-8">
                                        <input type="text" name="region_sort" value="<?php echo $data['region_sort']; ?>" class="form-control">
                                        <?php if (isset($error['region_sort'])) { ?><div class="text-danger"><?php echo $error['region_sort']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">收费标准</label>
                                    <div class="col-sm-8">
                                        <div class="input-group col-sm-6">
                                            <span class="input-group-addon">每</span>
                                            <input type="number" name="region_charge_time" value="<?php echo $data['region_charge_time']; ?>" class="form-control text-center">
                                            <span class="input-group-addon" style="border-left: 0;border-right: 0;">分钟</span>
                                            <input type="number" name="region_charge_fee" value="<?php echo $data['region_charge_fee']; ?>" class="form-control text-center">
                                            <span class="input-group-addon">元</span>
                                        </div>
                                        <?php if (isset($error['region_charge_time'])) { ?><div class="text-danger"><?php echo $error['region_charge_time']; ?></div><?php } ?>
                                        <?php if (isset($error['region_charge_fee'])) { ?><div class="text-danger"><?php echo $error['region_charge_fee']; ?></div><?php } ?>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">区域范围</label>
                                    <div class="col-sm-8">
                                        <div class="col-sm-12 row form-group">
                                            <button type="button" class="btn btn-large btn-primary margin-r-5 button-start-editor">开始编辑</button>
                                            <button type="button" class="btn btn-large btn-warning margin-r-5 button-end-editor">结束编辑</button>
                                            <button type="button" class="btn btn-large btn-danger  button-clear-editor">清除</button>
                                        </div>
                                        <div  class="col-sm-12 img-thumbnail" style="height: 500px;">
                                            <div id="container"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="col-sm-10">
                                    <div class="pull-right">
                                        <textarea name="region_bounds" class="hidden"><?php echo $data['region_bounds']; ?></textarea>
                                        <textarea name="region_bounds_southwest_lng" class="hidden"><?php echo $data['region_bounds_southwest_lng']; ?></textarea>
                                        <textarea name="region_bounds_southwest_lat" class="hidden"><?php echo $data['region_bounds_southwest_lat']; ?></textarea>
                                        <textarea name="region_bounds_northeast_lng" class="hidden"><?php echo $data['region_bounds_northeast_lng']; ?></textarea>
                                        <textarea name="region_bounds_northeast_lat" class="hidden"><?php echo $data['region_bounds_northeast_lat']; ?></textarea>
                                        <button type="submit" class="btn btn-sm btn-success margin-r-5">提交</button>
                                        <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default">返回</a>
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
<link rel="stylesheet" href="//cache.amap.com/lbs/static/main1119.css"/>
<script type="text/javascript"  src="//webapi.amap.com/maps?v=1.3&key=38c88d25e4aa2652bc7806db2d1f6a0d&plugin=AMap.PolyEditor,AMap.Geocoder,AMap.MouseTool&callback=initMap"></script>
<script type="text/javascript">
    var initMap = function(){
        if(typeof AMap != 'undefined') {
            var path = JSON.parse($('[name="region_bounds"]').val() || "[]");
            var editor = new Object;
            var map = new AMap.Map("container", {
                resizeEnable: true,
                zoom: 13
            });

            // 设置多边形覆盖面参数
            editor._polygon = new AMap.Polygon({
                map: map,
                strokeColor: "#0000ff",
                strokeOpacity: 1,
                strokeWeight: 3,
                fillColor: "#f5deb3",
                fillOpacity: 0.35
            });
            if (typeof path == "object") {
                editor._polygon.setPath(path);
                map.setFitView();
            }
            editor._polygonEditor = new AMap.PolyEditor(map, editor._polygon);

            // 快速定位按钮
            $(".button-location").click(function () {
                var address = $('[name="region_name"]').val();
                var geocoder = new AMap.Geocoder({
                    radius: 1000, //范围，默认：500
                });
                //地理编码,返回地理编码结果
                geocoder.getLocation(address, function (status, result) {
                    if (status === 'complete' && result.info === 'OK') {
                        map.setCenter(result.geocodes[0].location);
                    } else {
                        alert("找不到相关地址");
                    }
                });

            });

            // 开始编辑按钮
            $('.button-start-editor').click(function () {
                // 判断是否已划范围
                if (path.length > 0) {
                    // 修改已划范围
                    editor._polygonEditor.open();
                } else {
                    // 自定义范围
                    mouseTool.polygon();
                }
            });

            // 结束编辑按钮
            $('.button-end-editor').click(function () {
                var buffer = new Array;
                path = editor._polygon.getPath();

                $.each(path, function (key, value) {
                    buffer.push([value.lng, value.lat]);
                });
                path = buffer;

                bounds = editor._polygon.getBounds();
                southwest = bounds.getSouthWest();
                northeast = bounds.getNorthEast();

                $('[name="region_bounds"]').val(JSON.stringify(path));
                $('[name="region_bounds_southwest_lng"]').val(southwest.getLng());
                $('[name="region_bounds_southwest_lat"]').val(southwest.getLat());
                $('[name="region_bounds_northeast_lng"]').val(northeast.getLng());
                $('[name="region_bounds_northeast_lat"]').val(northeast.getLat());
                editor._polygonEditor.close();
            });

            // 清除按钮
            $('.button-clear-editor').click(function () {
                path = new Array;
                $('[name="region_bounds"]').val(JSON.stringify(path));
                editor._polygon.setPath(path);
                editor._polygonEditor.close();
            });

            // 在地图中添加MouseTool插件
            var mouseTool = new AMap.MouseTool(map);
            AMap.event.addListener(mouseTool, 'draw', function (e) { //添加事件
                //获取路径
                var buffer = new Array;
                $.each(e.obj.getPath(), function (key, value) {
                    buffer.push([value.lng, value.lat]);
                });
                path = buffer;
                // 更换成编辑多边形
                editor._polygon.setPath(path);
                editor._polygonEditor.open();
                map.setFitView();

                // 关闭绘制工具
                mouseTool.close(true);
            });
        }
    };
</script>
<?php echo $footer;?>