<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>新增运维人员</span>
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
                    <li ><a href="<?php echo $index_url?>" data-toggle="tab">运维人员列表</a></li>
                   <li class="active"><a href="<?php echo $position_url;?>" data-toggle="tab">运维人员定位</a></li>
				   <li ><a href="<?php echo $record_url;?>" data-toggle="tab">运维记录明细</a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <?php if (isset($error['warning'])) { ?>
                        <div class="alert alert-danger" style="opacity: 0.8;"><i class="fa fa-exclamation-circle"></i>&nbsp;<span><?php echo $error['warning']; ?></span>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <form class="form-horizontal" method="post" action="<?php echo $action; ?>">
                            <div class="form-group">
                                <label class="col-sm-2 control-label">运维名称</label>
                                <div class="col-sm-8">
                                    <input type="text" name="region_city_ranking" value="<?php echo $data['region_city_ranking']; ?>" class="form-control" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label">电话</label>
                                <div class="col-sm-8">
                                    <input type="text" name="region_city_code" value="<?php echo $data['region_city_code']; ?>" class="form-control">
                                    <?php if (isset($error['region_city_code'])) { ?><div class="text-danger"><?php echo $error['region_city_code']; ?></div><?php } ?>
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
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="col-sm-2 control-label">范围显示</label>
                                <div class="col-sm-8">
                                    
                                    <div  class="col-sm-12 img-thumbnail" style="height: 500px;width: 100%">
                                        <div id="container"></div>
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
                                        <a href="#" class="btn btn-sm btn-default">返回</a>
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
            var now_lat = '';
            var now_lng = '';
            var markers = [];
            var positions = [];
            var park_class_num = 1;
            var lii = 0;
            var path = JSON.parse($('[name="region_bounds"]').val() || "[]");
            var editor = new Object;
            var map = new AMap.Map("container", {
                resizeEnable: true,
                zoom: 13
            });

            map.on('complete', function() {
                var now_d = map.getCenter();
                now_lat = now_d.lat;
                now_lng = now_d.lng;
                //console.log(now_lat);
                //console.log(now_lng);
                //console.log(now_d);
            });

            function add_park(e,pclass,num){
                $(e).parent().append('<button type="button" data-id='+num+' class="btn btn-large btn-danger  button-clear-park '+pclass+'" style="margin: 5px;">点'+num+'<input type="hidden" name="park[]" value="'+now_lat+'-'+now_lng+'"></button>');
            }

        <?php if(!empty($data['park_bounds'])){
                foreach($data['park_bounds'] as $v){
                        ?>
                    positions[lii] = [<?php echo $v['lng']; ?>,<?php echo $v['lat']; ?>]
                    lii += 1;
                <?php
                }
            }
                ?>
            //console.log(positions);
            // 显示所有的停放点
            //var positions = [[116.405467, 39.907761], [116.415467, 39.907761], [116.415467, 39.917761], [116.425467, 39.907761], [116.385467, 39.907761]];
            for (var i = 0, marker; i < positions.length; i++) {
                console.log(positions[i]);
                if(positions[i][0] != undefined && positions[i][1] != undefined){
                    marker = new AMap.Marker({
                        map: map,
                        icon: "https://webapi.amap.com/theme/v1.3/markers/n/mark_b.png",
                        position: positions[i],
                        draggable: true,
                        cursor: 'move',
                        content: '<div  style="background-image: url(https://webapi.amap.com/theme/v1.3/markers/n/mark_b.png); background-repeat:no-repeat; width:20px; height: 30px; text-align: center ">'+park_class_num+'</div>',
                        raiseOnDrag: true,
                        title:"parking"+park_class_num
                    });
                    markers.push(marker);
                    // 点移动事件绑定，改变其经纬度；
                    marker.on('dragend', function(e) {
                        //console.log(e);
                        //console.log(e.target.G.title);
                        var cla = e.target.G.title;
                        var vall =  e.lnglat.lat+'-'+e.lnglat.lng;
                        $('.'+cla).find("input").val(vall);
                    });

                    now_lat = positions[i][1];
                    now_lng = positions[i][0];

                    // 添加点按钮
                    add_park(".button-add-park",'parking'+park_class_num,park_class_num);

                    park_class_num += 1;
                }
            }


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



            // 添加点单车停放点标记
            $(".button-add-park").click(function () {
                //console.log(now_lat);
                //console.log(now_lng);
                marker = new AMap.Marker({
                    map: map,
                    icon: "https://webapi.amap.com/theme/v1.3/markers/n/mark_b.png",
                    position: map.getCenter(),
                    draggable: true,
                    cursor: 'move',
                    content: '<div  style="background-image: url(https://webapi.amap.com/theme/v1.3/markers/n/mark_b.png); background-repeat:no-repeat; width:20px; height: 30px; text-align: center ">'+park_class_num+'</div>',
                    raiseOnDrag: true,
                    title:"parking"+park_class_num
                });
                marker.setMap(map);
                markers.push(marker);
                // 点移动事件绑定，改变其经纬度；
                marker.on('dragend', function(e) {
                    //console.log(e);
                    //console.log(e.target.G.title);
                    var cla = e.target.G.title;
                    var vall =  e.lnglat.lat+'-'+e.lnglat.lng;
                    $('.'+cla).find("input").val(vall);
                });
                // 添加点按钮
                add_park(this,'parking'+park_class_num,park_class_num);

                park_class_num += 1;
            });


            //


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

                //加载行政区划插件
                AMap.service('AMap.DistrictSearch', function() {
                    var opts = {
                        subdistrict: 1,   //返回下一级行政区
                        extensions: 'all',  //返回行政区边界坐标组等具体信息
                        level: 'city'  //查询行政级别为 市
                    };
                    //实例化DistrictSearch
                    district = new AMap.DistrictSearch(opts);
                    district.setLevel('district');
                    //行政区查询
                    district.search(address, function(status, result) {
                        if(result.districtList != undefined){
                            if(map.regionPolygons && map.regionPolygons.length) {
                                while (p = map.regionPolygons.pop()) {
                                    p.setMap(null);
                                }
                            }

                            var bounds = result.districtList[0].boundaries;
                            var polygons = [];
                            if (bounds) {
                                for (var i = 0, l = bounds.length; i < l; i++) {
                                    //生成行政区划polygon
                                    var polygon = new AMap.Polygon({
                                        map: map,
                                        strokeWeight: 1,
                                        path: bounds[i],
                                        fillOpacity: 0.7,
                                        fillColor: '#CCF3FF',
                                        strokeColor: '#CC66CC'
                                    });
                                    polygons.push(polygon);
                                    map.regionPolygons = polygons;
                                }
                                map.setFitView();//地图自适应
                            }
                        }
                    });
                });

            });
            //绑定删除事件；
            $(document).on('dblclick',".button-clear-park",function(){
                var id =$(this).attr('data-id') - 1;
                markers[id].setMap(null);
                var cl = "parking"+$(this).attr('data-id');
                $('.'+cl).remove();
                //map.remove(markers);
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