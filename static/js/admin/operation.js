/**
 * Created by Administrator on 2017/1/20.
 */
jQuery(function ($) {
    /**
     * 全局变量：地图
     * @type {null/Object}
     */
    var map = null;

    /**
     * 全局变量：地图是否已经被初始化了
     * @type {boolean}
     */
    var marker_init = false;


    /**
     * 全局变量：记录地图上的所有marker点
     * @type {Array}
     * @private
     */
    var _markers = [];

    /**
     * 全局变量：当前打开的infoWindow是属于哪个运维人员的（记录运维人员的id，如果没有打开infoWindow则为false）
     * @type {boolean}
     */
    var infoWindowOpened = false;

    /**
     * 全局变量：infoWindow
     * @type {null/Object}
     */
    var infoWindow = null;

    /**
     * 全局变量：infoWindow的单车信息JQuery对象
     * @type {null}
     */
    var $bikeInfo = null;


    /**
     * 地理编码与逆地理编码服务
     * @type {null}
     */
    var geocoder = null;

    /**
     * 全局变量
     * @type {null}
     */
    var pathSimplifierIns = null;


    /////////////////////////////////////////---地图---////////////////////////////////////////

    /**
     * 初始化地图
     */
    window.initMap = function () {
        if (typeof AMap !== 'undefined') {
            var defaultZoom = 14;
            var dafaultCenter = JSON.parse(getCookie('mapLngat_operation'));
            map = map || new AMap.Map("map", {
                    resizeEnable: true,
                    center: dafaultCenter,
                    zoom: defaultZoom
                });

            // 上次访问记录
            if (defaultZoom && dafaultCenter) {
                loadMarker();
            }


            AMapUI.load(['ui/misc/PathSimplifier'], function(PathSimplifier) {

                if (!PathSimplifier.supportCanvas) {
                    alert('当前环境不支持 Canvas！');
                    return;
                }

                //启动页面
                initPage(PathSimplifier);
            });

            // 创建InfoWindow，加载运维人员Marker点
            createInfoWindow();
            addTool();
        }
        geocoder = new AMap.Geocoder({});

        loadMarker();
    };


    //描绘轨迹的类的初始化
    function initPage(PathSimplifier) {
        //创建组件实例
        pathSimplifierIns = new PathSimplifier({
            zIndex: 100,
            //autoSetFitView:false,
            map: map, //所属的地图实例

            getPath: function(pathData, pathIndex) {

                var points = pathData.points,
                    lnglatList = [];

                for (var i = 0, len = points.length; i < len; i++) {
                    lnglatList.push(points[i].lnglat);
                }

                return lnglatList;
            },
            getHoverTitle: function(pathData, pathIndex, pointIndex) {

                if (pointIndex >= 0) {
                    //point
                    return '定位时间  ' + pathData.points[pointIndex].add_time;
                }

                return pathData.name;
            },
            renderOptions: {

                renderAllPointsIfNumberBelow: 100 //绘制路线节点，如不需要可设置为-1
            }
        });
    }

    /**
     * 加载运维Marker（Ajax请求后台 operation/operationLocation/apiGetMarker）
     */
    function loadMarker() {
        var bounds = map.getBounds(),
            sw = bounds.getSouthWest(),
            ne = bounds.getNorthEast(),
            swGps = gcj02towgs84(Number(sw.getLng()), Number(sw.getLat())),
            neGps = gcj02towgs84(Number(ne.getLng()), Number(ne.getLat())),
            data = {min_lng: swGps[0], min_lat: swGps[1], max_lng: neGps[0], max_lat: neGps[1]};

        var centralPoint = map.getCenter();
        setCookie('mapLngat_operation', JSON.stringify([centralPoint.lng, centralPoint.lat]));
        setCookie('mapZoom_operation', map.getZoom());
        if (!marker_init) {
            data.marker_init = 1;
            $('.btn-refresh-marker > i').addClass('fa-spin');
            $('.btn-refresh-marker').prop('disabled', true);
        }

        $.ajax('index.php?route=operation/operationLocation/apiGetMarker', {
            data: data,
            dataType: 'json',
            method: 'post',
            global: false,
            success: markerLoaded
        });
    }

    /**
     * 加载运维Marker点（Ajax请求成功的操作）
     * @param data
     */
    function markerLoaded(data) {
        $.each(data.data, function (i, operator) {
            var marker,
                pos = [Number(operator.lng), Number(operator.lat)]; //坐标转换

            // 图标
            operator.icon = '<div style="width:90px;background-color: #00a057;color: white;border-radius: 3px;text-align: center">'+operator.nickname+'</div>';

            if (!_markers[operator.admin_id]) { //新的Marker点，添加到地图
                marker = new AMap.Marker({
                    content: operator.icon,
                    position: pos,
                    offset: new AMap.Pixel(-18, -31),//X轴Y轴
                    map: map,
                    visible: true
                });
                marker.on('click', onMarkerClick);
                _markers[operator.admin_id] = marker;
            }
            else {
                //已有的Marker点，更新图标和位置
                marker = _markers[operator.admin_id];
                var oldPos = marker.getPosition();
                if ((Math.abs(oldPos.getLng() - pos[0]) > 0.000001) || (Math.abs(oldPos.getLat() - pos[1]) > 0.000001)) {
                    marker.setPosition(pos);
                }
            }
            marker.operator = operator; //把运维数据更新给Marker点（记录下来）
            if (infoWindowOpened === operator.admin_id) { //如果infoWindow已经打开，立刻更新上面的内容
                updateInfoWindow(operator);
            }
        });

        marker_init = true;
        $('.btn-refresh-marker > i').removeClass('fa-spin');
        $('.btn-refresh-marker').prop('disabled', false);

        //map.setZoomAndCenter(0);
        // map.setFitView(); //如果是第一次加载到marker点，setFitView到显示所有点。//TODO 记录最后的中心和zoom
        //openMarker();
    }

    /**
     * 加载地图上的控件
     */
    function addTool() {
        map.plugin(["AMap.ToolBar"], function () {
            map.addControl(new AMap.ToolBar());
        });
    }

    /**
     * 创建InfoWindow
     */
    function createInfoWindow() {
        // infoWindow的内容模板
        $bikeInfo = $(
            '<div>' + // 加一层div方便下面获取dom（$bikeInfo[0]）
            '<div class="bike-info">' +
            '<div class="bike-info-title">小明</div>' +
            '<ul class="bike-info-tabs">' +
            '<li class="active">概况</li>' +
            '<li>开锁记录</li>' +
            '<li>运维记录</li>' +
            '</ul>' +
            '<div class="bike-info-body">' +
            '<div class="active">' + // 概况 start
            '<div class="operator-nickname font14"><i class="fa  fa-user fa-fw"></i> 运维人员名称：<span>小明</span></div>' +
            '<hr>' +
            '<div class="operator-mobile font14"><i class="fa fa-mobile-phone fa-fw"></i> 联系电话：<span>13788888888</span></div>' +
            '<hr>' +
            '<div class="operator-responsible-area font14"><i class="fa fa-tag fa-fw"></i> 负责片区：<span>东城</span></div>' +
            '<hr>' +
            '<button class="btn btn-default show-operator-path">显示轨迹</button>' +
            '</div>' + // 概况 end
            '<div><ul class="bike-info-list"></ul></div>' + // 故障
            '<div><ul class="bike-info-list"></ul></div>' + // 违停
            '<div class="loading-mask"><div><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <span class="sr-only">Loading...</span></div></div>' +
            '</div>' +
            '</div>' +
            '<div class="bike-info-sharp"></div>' +
            '<a class="bike-info-close" href="javascript: void(0)"></a>' +
            '<div class="bike-info-refresh"><i class="fa fa-refresh"></i></div>' +
            '</div>'
        );

        infoWindow = new AMap.InfoWindow({
            isCustom: true,
            content: $bikeInfo[0],
            offset: new AMap.Pixel(-1, -32)
        });

        ///////////////// infoWindow上的事件处理
        $bikeInfo.on('click', '.bike-info-close', function () { // 右上角的关闭按钮
            closeBikeInfo();
        }).on('click', '.bike-info-refresh', function () { // 右上角的刷新按钮
            $(this).find('i').addClass('fa-spin');
            marker_init = false;
            loadMarker();
        }).on('click', '.bike-info-tabs > li', function () { //切换标签
            var index = $(this).index(),
                $tabDiv = $(this).parent().next().children().eq(index);
            setActive($(this));
            setActive($tabDiv);
            if (index > 0 && index < 5) {
                map.setStatus({scrollWheel: false});
            }
            else {
                map.setStatus({scrollWheel: true});
            }
            if ($tabDiv.data('data-loaded')) return;
            refreshTab();
        }).on('click', '.bike-info-list > li.has-more > button', function () { // 故障、违停、停车、反馈、使用等内部的“加载更多”按钮
            var index = $(this).parent().parent().parent().index(),
                page = $(this).parent().data('next');
            var operator_name = $bikeInfo.find('.bike-info-title').html();
            loadTabData(index, page, operator_name);
        }).on('click', '.bike-instruction > button:eq(4)', function () {
            var bike_sn = $bikeInfo.find('.bike-sn').html();
            var hide = $bikeInfo.find('.bike-instruction > button:eq(4)').data('hide');
            closeBikeInfo();
            hideBike(bike_sn, hide);
        }).on('click', '.show-operator-path', function () {
            var operator_name = $bikeInfo.find('.bike-info-title').html();
            showOperatorPath(operator_name);
        });
        $bikeInfo.magnificPopup({
            delegate: '.bike-info-list-img > img',
            type: 'image',
            mainClass: 'mfp-with-zoom',
            zoom: {
                enabled: true,
                duration: 300,
                easing: 'ease-in-out'
            }
        });
    }


    /**
     * 点击Marker点（先更新InfoWindow的内容，然后显示InfoWindow）
     * @param e
     */
    function onMarkerClick(e) {
        var marker = e.target,
            operator = marker.operator;
        updateInfoWindow(operator);

        infoWindow.open(map, marker.getPosition());
        infoWindowOpened = operator.admin_id;
    }

    /**
     * 根据单车的信息更新InfoWindow的内容
     * @param operator
     */
    function updateInfoWindow(operator) {
        $bikeInfo.find('.bike-info-title').text(operator.admin_name);
        $bikeInfo.find('.operator-mobile span').text(operator.mobile);
        $bikeInfo.find('.operator-nickname span').text(operator.nickname);
        $bikeInfo.find('.operator-responsible-area span').text(operator.region_name);
        refreshTab();
        refreshAllTab();
    }

    /**
     * 刷新所有标签
     */
    function refreshAllTab() {
        var $allTabDiv = $bikeInfo.find('.bike-info-body > div');
        $allTabDiv.data('data-loaded', false);
    }

    /**
     * 刷新当前标签
     */
    function refreshTab() {
        var $tabDiv = $bikeInfo.find('.bike-info-body > div.active'),
            index = $tabDiv.index();

        var operator_name = $bikeInfo.find('.bike-info-title').html();
        if (index < 0 || index > 5) return; //只有7个标签（0-5）

        if (index > 0 && index < 5) $tabDiv.find('ul').empty(); //0和5是不清空内容的，只更新内容

        if (index > 0) {
            $tabDiv.find('ul').empty();
            loadTabData(index, 1, operator_name);
        }

    }

    /**
     * 加载成功当前标签内容（除了概况和指令之外）
     * @param index
     * @param page
     * @param operator_name
     */
    function loadTabData(index, page, operator_name) {
        $bikeInfo.find('.bike-info-body').addClass('loading');
        $bikeInfo.find('.bike-info-body > div.active > ul.bike-info-list > li.has-more').remove();
        switch (index) {
            case 1: // 故障
                openLockRecords(page, operator_name);
                break;
            case 2: // 违停
                handleFaultList(page, operator_name);
                break;
        }
    }


    /**
     * 开锁记录
     * @param page
     * @param operator_name
     */
    function openLockRecords(page, operator_name) {
        var $tabDiv = $bikeInfo.find('.bike-info-body > div:eq(1)');
        $.ajax('index.php?route=operation/operationLocation/openLockRecords', {
            dataType: 'html',
            data: {page: page, operator_name: operator_name},
            method: 'POST',
            global: false,
            success: function (html) {
                $tabDiv.find('ul').append(html);
                tabDataLoaded($tabDiv);
            }
        });
    }

    /**
     * 处理列表
     * @param page
     * @param operator_name
     */
    function handleFaultList(page, operator_name) {
        var $tabDiv = $bikeInfo.find('.bike-info-body > div:eq(2)');
        $.ajax('index.php?route=operation/operationLocation/handleFaultList', {
            dataType: 'html',
            data: {page: page, operator_name: operator_name},
            method: 'POST',
            global: false,
            success: function (html) {
                $tabDiv.find('ul').append(html);
                tabDataLoaded($tabDiv);
            }
        });
    }

    function showOperatorPath(operator_name){

        var path = null;

        $.ajax('index.php?route=operation/operationLocation/getOperatorsPosition', {
            dataType: 'json',
            data: {operator_name:operator_name},
            method: 'POST',
            global: false,
            success: function (position_data) {
                console.log(position_data.data);
                if(position_data.data.points.length <= 1){
                    alert('点数不够');
                    return;
                }
                path = position_data.data;

                //这里构建两条简单的轨迹，仅作示例
                pathSimplifierIns.setData([path]);

                pathSimplifierIns.setSelectedPathIndex(0);

                //创建一个巡航器
                var navg0 = pathSimplifierIns.createPathNavigator(0, //关联第1条轨迹
                    {
                        loop: true, //循环播放
                        speed: 1000
                    });

                navg0.start();
            }
        });

    }

    /**
     * 标签内容加载完毕后的处理
     * @param $tabDiv
     */
    function tabDataLoaded($tabDiv) {
        $bikeInfo.find('.bike-info-body').removeClass('loading');
        $bikeInfo.find('.bike-info-refresh > i').removeClass('fa-spin');
        $tabDiv.data('data-loaded', true);
    }

    //关闭单车信息
    function closeBikeInfo() {
        setActive($bikeInfo.find('.bike-info-tabs > li:first-child'));  //重置第一个标签为active
        setActive($bikeInfo.find('.bike-info-body > div:first-child'));
        map.setStatus({scrollWheel: true});
        map.clearInfoWindow();
        infoWindowOpened = false;
    }


    $('.show-bike-type-select li').on('click', function () {
        var cooperator_id = $(this).data('cooperator-id');
        var cooperator_name = $(this).data('cooperator-name');
        $('.show-bike-type-select button').html(cooperator_name + ' <span class="caret pull-right"></span>');
        var city = cooperator_name;
        //地理编码,返回地理编码结果 实现选择城市跳转
        geocoder.getLocation(city, function (status, result) {
            if (status === 'complete' && result.info === 'OK') {
                map.setZoomAndCenter(12, result.geocodes[0].location);
            } else {
                if (city === '全部') {
                    map.setZoom(5);
                } else {
                    alert("找不到相关地址");
                }
            }
        });
        //获取某个合伙人下面的全部运维人员
        $.ajax('index.php?route=operation/operationLocation/getOperators', {
            dataType: 'json',
            data: {cooperator_id: cooperator_id},
            method: 'POST',
            global: false,
            success: function (result) {
                var html = '';
                $.each(result.data, function (index, item) {
                    html += '<li data-admin-id=' + item['admin_id'] + '>' + item['nickname'] + '</li>';
                });
                if (html === '') html = '<div style="padding: 10px;">没有相关人员</div>';
                $('.bike-list').html(html);
            }
        });
    });

    //地图右侧搜索列表点击
    $('.bike-list').on('click', 'li', function () {
        var admin_id = $(this).data('admin-id');
        console.log(admin_id);
        $.each(_markers, function (index, marker) {
            if (typeof marker === 'undefined') return;
            if (marker.operator.admin_id == admin_id) {
                updateInfoWindow(marker.operator);
                infoWindowOpened = marker.operator.admin_id;
                infoWindow.open(map, marker.getPosition());
                $('#dashboard-content').removeClass('open-search-panel');
            }
        })
    });

    //选择右边公司后 地图定位到该公司所在地区
    //如果改要跳地方把这个地方的注释去掉
    $('#bikes').find('ul.dropdown-menu li').click(function () {
        // 合伙人id
        // var coop_id = ($(this).data('cooperator-id'));//console.log(coop_id);
        // 合伙人对应城市id
        // 地图事件处理
        // map.on('moveend', function () {
        //     loadMarker();
        // });
        // map.on('zoomend', loadMarker);
        //
        // var city = $(this).val();
        // $("[name = 'region_name']").val(city);
        // // map.setCity(city);
        // //地理编码,返回地理编码结果
        // geocoder.getLocation(city, function (status, result) {
        //     if (status === 'complete' && result.info === 'OK') {
        //         map.setZoomAndCenter(12, result.geocodes[0].location);
        //     } else {
        //         if (city == '') {
        //             map.setZoom(5);
        //         } else {
        //             alert("找不到相关地址");
        //         }
        //     }
        // });
    });

    /**
     * 工具栏上的刷新按钮：重新刷新Marker点
     */
    $('.btn-refresh-marker').on('click', loadMarker);

    /**
     * 全屏地图
     */
    $('.btn-map-maximize').on('click', function () {
        if (__map_maximized) {
            $('body').removeClass('map-maximized');
            $(this).removeClass('active');
        }
        else {
            $('body').addClass('map-maximized');
            $(this).addClass('active');
        }
        __map_maximized = !__map_maximized;
        $(this).blur();
    });
    var __map_maximized = false;

    /////////////////////////////////////////---公共函数---////////////////////////////////////////
    /**
     * 设置某个元素是active，其siblings都去掉active
     * @param $dom
     */
    function setActive($dom) {
        $dom.addClass('active').siblings().removeClass('active');
    }

});
