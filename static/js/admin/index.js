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
     * 全局变量：当前打开的infoWindow是属于哪个单车的（记录单车的id，如果没有打开infoWindow则为false）
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
     * 全局变量：当前是否显示单车编号，默认false
     * @type {boolean}
     */
    var showBikeNumber = false;

    /**
     * 全局变量：当前显示单车的状态：''代表所有单车，其他值还有'low_battery'、'illegal_parking'、'fault'、'offline'
     * @type {string}
     */
    var showBikeStatusType = '';

    /**
     * 地理编码与逆地理编码服务
     * @type {null}
     */
    var geocoder = null;

    /**
     * 热力图
     * @type {null}
     */
    var heatmap = null;

    /**
     * 用户信息
     */
    var userInfoWindowModel = null;

    /////////////////////////////////////////---地图---////////////////////////////////////////

    /**
     * 初始化地图
     */
    window.initMap = function() {
        if(typeof AMap != 'undefined') {
            var defaultZoom = getCookie('mapZoom') || 14;
            var dafaultCenter = JSON.parse(getCookie('mapLngat'));
            map = map || new AMap.Map("map", {
                    resizeEnable: true,
                    center: dafaultCenter,
                    zoom: defaultZoom
                });


            // 上次访问记录
            if (defaultZoom && dafaultCenter) {
                // loadMarker();
            }

            // 创建InfoWindow，加载单车Marker点
            createInfoWindow();
            addTool();

            // 初始化测距工具
            initRuler();

            onGetUrl();
        }
        geocoder = new AMap.Geocoder({});

        //初始化heatmap对象
        heatmap = new AMap.Heatmap(map, {
            radius: 25, //给定半径
            opacity: [0, 0.8],
        });
    };

    /**
     * 加载热力图
     */
    function loadHeatMap(type) {
        var data = [];
        switch (type) {
            case 'start_position' :
                data = heatmapData.start
                break;
            case 'end_position' :
                data = heatmapData.end
                break;
            default:
                data = heatmapData.all
                break;
        }
        //设置数据集数据
        heatmap.setDataSet({
            data: data,
            max: 100
        });
    }

    /**
     * 加载单车Marker（Ajax请求后台admin/index/apiGetMarker）
     */
    function loadMarker() {
        if ($('input#show-heatmap').is(":checked")) {
            return false;
        }
        var bounds = map.getBounds(),
            sw = bounds.getSouthWest(),
            ne = bounds.getNorthEast(),
            swGps = gcj02towgs84(Number(sw.getLng()), Number(sw.getLat())),
            neGps = gcj02towgs84(Number(ne.getLng()), Number(ne.getLat())),
            data = {min_lng: swGps[0], min_lat: swGps[1], max_lng: neGps[0], max_lat: neGps[1]};
        console.log(map.getCenter());
        console.log(map.getZoom());
        var centralPoint = map.getCenter();
        setCookie('mapLngat', JSON.stringify([centralPoint.lng, centralPoint.lat]));
        setCookie('mapZoom', map.getZoom());
        if(!marker_init) data.marker_init = 1;
        $('.btn-refresh-marker > i').addClass('fa-spin');
        $('.btn-refresh-marker').prop('disabled', true);
        $.ajax('index.php?route=admin/index/apiGetMarker', {
            data: data,
            dataType: 'json',
            method: 'post',
            global: false,
            success: mrkerLoaded
        });
    }

    /**
     * 加载单车Marker点（Ajax请求成功的操作）
     * @param data
     */
    function mrkerLoaded(data) {
        $.each(data.data,function(i, bike) {
            var marker,
                pos = wgs84togcj02(Number(bike.lng), Number(bike.lat)); //坐标转换

            // 根据使用状况和故障状况决定图标
            bike.icon = '<div class="bike-marker ' + getMarkerClass(bike) + (showBikeNumber ? ' show-bike-number' : '') + '"><div class="bike-marker-bike-number">' + bike.bicycle_sn + '</div></div>';

            if(!_markers[bike.bicycle_id]){ //新的Marker点，添加到地图
                marker = new AMap.Marker({
                    content: bike.icon,
                    position: pos,
                    offset: new AMap.Pixel(-18,-31),//X轴Y轴
                    map: map,
                    visible: isMarkerVisible(bike)
                });
                marker.on('click', onMarkerClick);
                _markers[bike.bicycle_id] = marker;
            }
            else { //已有的Marker点，更新图标和位置
                marker = _markers[bike.bicycle_id];
                isMarkerVisible(bike) ? marker.show() : marker.hide();
                if(marker.bike.showingNumber!=showBikeNumber) {
                    var iconDiv = $(marker.icon).find('>div');
                    showBikeNumber ? iconDiv.addClass('show-bike-number') : iconDiv.removeClass('show-bike-number');
                }
                var oldPos = marker.getPosition();
                if((Math.abs(oldPos.getLng() - pos[0]) > 0.000001) || (Math.abs(oldPos.getLat() - pos[1]) > 0.000001) ) {
                    marker.setPosition(pos);
                }
            }
            marker.bike = bike; //把单车数据更新给Marker点（记录下来）
            bike.showingNumber = showBikeNumber;

            if(infoWindowOpened == bike.bicycle_id) { //如果infoWindow已经打开，立刻更新上面的内容
                updateInfoWindow(bike);
            }
        });

        if(!marker_init) {
            marker_init = true;
            //map.setZoomAndCenter(0);
            //map.setFitView(); //如果是第一次加载到marker点，setFitView到显示所有点。//TODO 记录最后的中心和zoom
        }

        $('.btn-refresh-marker').prop('disabled', false);
        $('.btn-refresh-marker > i').removeClass('fa-spin');

        openMarker();
    }

    /**
     * 根据单车状态获取单车的图标css class
     * @param bike
     * @returns {string}
     */
    function getMarkerClass(bike) {
        if (bike.cant_finish == "1") {
            return 'cant_finish';
        }
        else if(bike.offline=="1" && bike.lock_type != 2 ) {
            return 'offline';
        }
        else if(bike.faultTime && bike.illegalParkingTime) {
            return (bike.faultTime < bike.illegalParkingTime) ? 'fault' : 'illegal-parking'
        }
        else if(bike.faultTime) {
            return 'fault';
        }
        else if(bike.illegalParkingTime) {
            return 'illegal-parking';
        }
        else if(Math.abs(parseInt(bike.battery))<=25 && bike.lock_type != 2) {
            return 'low-battery';
        }
        else if(bike.is_using=="1") {
            return 'using';
        }
        else if(bike.noUsedDays >= 2 && bike.noUsedDays <= 5) {
            return 'no-used-' + bike.noUsedDays + '-days'
        }
        else if(bike.noUsedDays > 5) {
            return 'no-used-5-plus-days'
        }
        // else if(bike.offline=="1") {
        //     return 'offline';
        // }
        else if( bike.lock_type == 2 || bike.lock_type == 5){
            return 'bike-marker-lan';
        }
        else {
            return '';
        }
    }

    /**
     * 加载地图上的控件
     */
    function addTool() {
        map.plugin(["AMap.ToolBar"], function() {
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
                    '<div class="bike-info-title">自行车编号：<span class="bike-sn">123456</span></div>' +
                    '<ul class="bike-info-tabs">' +
                        '<li class="active">概况</li>' +
                        '<li>故障</li>' +
                        '<li>违停</li>' +
                        '<li>停车</li>' +
                        // '<li>反馈</li>' +
                        '<li>使用</li>' +
                        '<li>指令</li>' +
                    '</ul>' +
                    '<div class="bike-info-body">' +
                        '<div class="active">' + // 概况 start
                            '<div class="icon-info-wrapper">' +
                                '<div class="bike-battery battery-1" title="电池电压：3.83V\n充电电压：0.06V"></div>' +
                                '<div class="bike-power"></div>' +
                            '</div>' +
                            '<div class="icon-info-wrapper">' +
                                '<div class="bike-status bike-status-locked"></div>' +
                                '<div class="bike-status-text">在线</div>' +
                            '</div>' +
                            '<div class="icon-info-wrapper">' +
                                '<div class="bike-alarm"></div>' +
                                '<div class="bike-alarm-text">无警报</div>' +
                            '</div>' +
                            '<div class="icon-info-wrapper">' +
                                '<div class="bike-gprs signal-3" title="24"></div>' +
                                '<div>GPRS</div>' +
                            '</div>' +
                            '<div class="icon-info-wrapper">' +
                                '<div class="bike-gps signal-3" title="6"></div>' +
                                '<div>GPS</div>' +
                            '</div>' +
                            '<div class="horizontal-info">' +
                                '<div>故障：<i class="fa fa-circle"></i></div>' +
                                '<div>违停：<i class="fa fa-circle"></i></div>' +
                                '<div>激活：<i class="fa fa-circle"></i></div>' +
                            '</div>' +
                            '<hr>' +
                            '<div class="bike-location font14" style="overflow: hidden;text-overflow: ellipsis;white-space: nowrap;"><i class="fa fa-map-marker fa-fw"></i> <span>广东省东莞市南城区新基路新基地创意产业园H座</span></div>' +
                            '<div class="bike-last-update font14"><i class="fa fa-clock-o fa-fw"></i> 最后更新：<span class="last-update-time">2017-03-07 22:22:22</span>(<span class="last-update-type">GPS</span>)<!-- <button class="btn btn-xs btn-default"><i class="fa fa-map"></i> 轨迹</button> --></div>' +
                            '<hr>' +
                            '<div class="font14"><span class="bike-cooperator"><i class="fa fa-user fa-fw"></i> </span>' +
                            '<span class="bike-region"><i class="fa fa-map-pin fa-fw"></i> 珠海市<span></div>' +
                            '<hr>' +
                            '<div class="bike-lock-sn font14"><i class="fa fa-lock fa-fw"></i> 锁编号：<span>063012345678</span> <i class="fa fa-info-circle bike-lock-info"></i></div>' +
                            '<div class="bike-lock-type font14"><i class="fa fa-compass fa-fw"></i> <span>锁类型：GRPS</span></div>' + // fa-bluetooth
                            '<hr>' +
                            '<div class="bike-used-times-total font14"><i class="fa fa-area-chart fa-fw"></i> <span>使用次数：共0次，本月0次，今天0次</span></div>' +
                        '</div>' + // 概况 end
                        '<div><ul class="bike-info-list"></ul></div>' + // 故障
                        '<div><ul class="bike-info-list"></ul></div>' + // 违停
                        '<div><ul class="bike-info-list"></ul></div>' + // 停车
                        // '<div><ul class="bike-info-list"></ul></div>' + // 反馈
                        '<div><ul class="bike-info-list"></ul></div>' + // 使用
                        '<div class="bike-instruction">' +  // 指令 start
                            '<button class="btn btn-default" style="display:none;"><i class="fa fa-lock fa-fw"></i> 关锁</button>' +
                            '<button class="btn btn-default"><i class="fa fa-unlock-alt fa-fw"></i> 开锁</button>' +
                            '<button class="btn btn-default"><i class="fa fa-bell fa-fw"></i> 开蜂鸣器</button>'+
                            '<button class="btn btn-default" data-lng="" data-lat=""><i class="fa fa-bell fa-fw"></i> 结束订单</button>' +
                            // '<button class="btn btn-default"><i class="fa fa-bell-slash fa-fw"></i> 关蜂鸣器</button><br/>' +
                            '<div class="input-group">' +
                                '<span class="input-group-addon">关锁时：每隔</span>' +
                                '<input type="number" class="form-control" value="1800">' +
                                '<span class="input-group-addon">秒一次定位</span>' +
                                '<span class="input-group-btn">' +
                                    '<button class="btn btn-default" type="button">设置</button>' +
                                '</span>' +
                            '</div>' +
                            '<div class="input-group">' +
                                '<span class="input-group-addon">开锁时：每隔</span>' +
                                '<input type="number" class="form-control" value="600">' +
                                '<span class="input-group-addon">秒一次定位</span>' +
                                '<span class="input-group-btn">' +
                                    '<button class="btn btn-default" type="button">设置</button>' +
                                '</span>' +
                            '</div>' +
                            '<button class="bike-is-hide btn btn-default" data-hide="1"><i class="fa fa-eye-slash"></i>隐藏</button>'+
                        '</div>' + // 指令 end
                        '<div class="loading-mask"><div><i class="fa fa-spinner fa-pulse fa-3x fa-fw"></i> <span class="sr-only">Loading...</span></div></div>' +
                        '<div class="bike-info-qrcode"><img src="" STYLE=""/></div>' +
                    '</div>' +
                '</div>' +
                '<div class="bike-info-sharp"></div>' +
                '<a class="bike-info-close" href="javascript: void(0)"></a>' +
                '<div class="bike-info-qrcode-trigger"><i class="fa fa-qrcode"></i></div>' +
                '<div class="bike-info-refresh"><i class="fa fa-refresh"></i></div>' +
            '</div>'
        );

        infoWindow = new AMap.InfoWindow({
            isCustom: true,
            content: $bikeInfo[0],
            offset: new AMap.Pixel(-1, -32)
        });

        ///////////////// infoWindow上的事件处理
        $bikeInfo.on('click', '.bike-info-close', function(){ // 右上角的关闭按钮
            closeBikeInfo();
        }).on('click', '.bike-info-refresh', function() { // 右上角的刷新按钮
            $(this).find('i').addClass('fa-spin');
            loadMarker();
        }).on('mouseenter', '.bike-info-qrcode-trigger', function() { // 右上角二维码按钮图标（鼠标进入）
            setActive($bikeInfo.find('.bike-info-body > div:last-child'));
        }).on('mouseleave', '.bike-info-qrcode-trigger', function() { // 右上角二维码按钮图标（鼠标离开
            setActive($bikeInfo.find('.bike-info-body > div:eq(' + $bikeInfo.find('.bike-info-tabs > li.active').index() + ')'));
        }).on('click', '.bike-info-tabs > li', function() { //切换标签
            var index = $(this).index(),
                $tabDiv = $(this).parent().next().children().eq(index);
            setActive($(this));
            setActive($tabDiv);
            if(index>0 && index<5) {
                map.setStatus({scrollWheel:false});
            }
            else {
                map.setStatus({scrollWheel:true});
            }
            if($tabDiv.data('data-loaded')) return;
            refreshTab();
        }).on('click', '.bike-info-list > li.has-more > button', function() { // 故障、违停、停车、反馈、使用等内部的“加载更多”按钮
            var index = $(this).parent().parent().parent().index(),
                page = $(this).parent().data('next');
            var bike_sn = $bikeInfo.find('.bike-sn').html();
            loadTabData(index, page, bike_sn);
        }).on('click', '.bike-instruction > button:eq(0)', function(){ //关锁
            var lock_sn = $bikeInfo.find('.bike-lock-sn > span').html();
            shut(lock_sn);
        }).on('click', '.bike-instruction > button:eq(1)', function(){ //开锁
            var lock_sn = $bikeInfo.find('.bike-lock-sn > span').html();
            openLock(lock_sn);
        }).on('click', '.bike-instruction > button:eq(2)', function(){ //响铃
            var lock_sn = $bikeInfo.find('.bike-lock-sn > span').html();
            beepLock(lock_sn);
        }).on('click', '.bike-instruction > div:eq(0) > span:eq(2) > button', function(){ //设置设备锁关是位置回传间隔
            var lock_sn = $bikeInfo.find('.bike-lock-sn > span').html();
            var time = $bikeInfo.find('.bike-instruction > div:eq(0) > input').val();
            setGapTime2(lock_sn,time);
        }).on('click', '.bike-instruction > div:eq(1) > span:eq(2) > button', function(){ //设置设备锁开是位置回传间隔
            var lock_sn = $bikeInfo.find('.bike-lock-sn > span').html();
            var time = $bikeInfo.find('.bike-instruction > div:eq(1) > input').val();
            setGapTime(lock_sn,time);
        }).on('click', '.bike-instruction > button:eq(3)', function(){ //结束订单
            var lock_sn = $bikeInfo.find('.bike-lock-sn > span').html();
            var lng = $bikeInfo.find('.bike-instruction > button:eq(3)').data('lng');
            var lat = $bikeInfo.find('.bike-instruction > button:eq(3)').data('lat');
            finishOrder(lock_sn, lng, lat);
        }).on('click', '#fault_type_12', function(){ //处理类型为12的故障
            var lock_sn = $bikeInfo.find('.bike-lock-sn > span').html();
            var lng = $bikeInfo.find('.bike-instruction > button:eq(3)').data('lng');
            var lat = $bikeInfo.find('.bike-instruction > button:eq(3)').data('lat');
            finishOrder(lock_sn, lng, lat);
        }).on('click', '#order-history-mobile', function(){ //点击历史订单用户手机号
            userInfoWindow($(this).data('user-id'));
            updateUserInfoWindow($(this).data('user-id'));
            var modal = $('#user-modal');
            modal.find('.modal-title').html($(this).data('mobile') + "(" + $(this).data('nickname') + ")");
        }).on('click','.bike-instruction > button:eq(4)',function(){
            var bike_sn = $bikeInfo.find('.bike-sn').html();
            var hide = $bikeInfo.find('.bike-instruction > button:eq(4)').data('hide');
            closeBikeInfo();
            hideBike(bike_sn,hide);
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
            bike = marker.bike;
        updateInfoWindow(bike);

        infoWindow.open(map, marker.getPosition());
        infoWindowOpened = bike.bicycle_id;
    }

    //刷新地图如果存在就打开该点详情（邮件图标）
    function openMarker(){
        var bicycle_id = localStorage.open_marker_bicycle_id;
        if(bicycle_id){
            $.each(_markers, function (index, marker) {
                if(typeof marker == 'undefined') return;
                if(marker.bike.bicycle_id == bicycle_id) {
                    updateInfoWindow(marker.bike);
                    infoWindow.open(map, marker.getPosition());
                    //map.setZoomAndCenter(11, [marker.bike.lng, marker.bike.lat]);
                }
            });
        }
        localStorage.clear();
        // map.setZoomAndCenter(11);
    }

    //点击地图顶部邮件按钮(如果在首页)
    function onGetUrl() {
        getUrl(function(result){
            if(result['route'] == 'admin/index')
            $(document).on('click','.open-marker',function(){
                var bicycle_id = $(this).data('bicycleId');
                $.each(_markers, function (index, marker) {
                    if(typeof marker == 'undefined') return;
                    if(marker.bike.bicycle_id == bicycle_id) {
                        updateInfoWindow(marker.bike);
                        infoWindow.open(map, marker.getPosition());
    //                    map.setZoomAndCenter(11, [marker.bike.lng, marker.bike.lat]);

                    }
                });
            });
        });
    }


    // 移动端点击搜索单车按钮打开搜索单车列表
    $(document).on('click', '#btn-open-search',function(e){
        $('#dashboard-content').addClass('open-search-panel');
        $(this).blur();
    });

    $(document).on('click', 'header,#bikes-bg', function(e){
        if($("#dashboard-content").hasClass("open-search-panel")) {
            e.preventDefault();
            e.stopPropagation();
            $('#dashboard-content').removeClass('open-search-panel');
        }
    });

    //地图右侧搜索单车按钮
    var last;
    $(document).on('click','#search-bicycle-btn',function(){
        search();
    })
    .on('keyup','#search-bicycle-input',function(event){
        var q = $("#search-bicycle-input").val();
        // if(event.keyCode == "13") {
        //     search();
        // }
        // else if(q.length>=3){
        //     search();
        // }
        // else if(q.length<3) {
        //     $('.bike-list').empty();
        // }
        last = event.timeStamp;
        //利用event的timeStamp来标记时间，这样每次的keyup事件都会修改last的值，注意last必需为全局变量
        setTimeout(function(){    //设时延迟0.5s执行
            if(last-event.timeStamp==0)
            //如果时间差为0（也就是你停止输入0.5s之内都没有其它的keyup事件发生）则做你想要做的事
            {
                //做你要做的事情
                if(event.keyCode == "13") {
                    search();
                }
                else if(q.length>=3){
                    search();
                }
                else if(q.length<3) {
                    $('.bike-list').empty();
                }
            }
        },1000);
    });


    //地图右侧搜索请求
    function search() {
        var bike_sn = $("#search-bicycle-input").val();
        if(bike_sn == '') {
            $('.bike-list').html('');
            return;
        }
        var fault = $('.show-bike-type-select li:eq(0) input[type="checkbox"]').prop('checked') ? 1 : 0;
        var illegal_parking = $('.show-bike-type-select li:eq(1) input[type="checkbox"]').prop('checked') ? 1 : 0;
        var low_battery = $('.show-bike-type-select li:eq(2) input[type="checkbox"]').prop('checked') ? 1 : 0;

        $.ajax('index.php?route=admin/index/search', {
            dataType: 'json',
            data: {
                bicycle_sn: bike_sn,
                fault: fault,
                illegal_parking: illegal_parking,
                low_battery: low_battery
            },
            method: 'POST',
            global: false,
            success: function (result) {
                var html = '';
                $.each(result.data.bikes, function(index, bike){
                    html += '<li data-type="bike" data-bicycle_id="'+ bike.bicycle_id +'"><i class="fa fa-bicycle fa-fw"></i> '+ bike.bicycle_sn +'</li>';
                });
                $.each(result.data.users, function(index, user){
                    html += '<li data-type="user" data-user_id="' + user.user_id + '" data-nickname="' + user.nickname + '"  data-mobile="' + user.mobile + '" data-toggle="modal" data-target="#user-modal"><i class="fa fa-user fa-fw"></i> ' + user.mobile + '</li>';
                    if(user.order_id) {
                        html += '<li data-type="bike" data-bicycle_id="' + user.bicycle_id + '" style="padding-left: 32px;">'+(user.order_state=='1' ? '使用中':'预约中')+'：<i class="fa fa-bicycle fa-fw"></i> ' + user.bicycle_sn + '</li>';
                    }
                });
                if(html==='') html = '<div style="padding: 10px;">没有相关单车或用户</div>';
                $('.bike-list').html(html);
            }
        });
    }

    //地图右侧搜索列表点击
    $('.bike-list').on('click', 'li', function () {
        var user_id = $(this).data('user_id');
        if($(this).data('type')=='bike') {
            var bicycle_id = $(this).data('bicycle_id');
            $.each(_markers, function (index, marker) {
                if(typeof marker == 'undefined') return;
                if(marker.bike.bicycle_id == bicycle_id) {
                    updateInfoWindow(marker.bike);
                    infoWindowOpened = marker.bike.bicycle_id;
                    infoWindow.open(map, marker.getPosition());
                    //map.setZoomAndCenter(11, [marker.bike.lng, marker.bike.lat]);
                    $('#dashboard-content').removeClass('open-search-panel');
                }
            })
        }else {
            userInfoWindow(user_id);
            updateUserInfoWindow(user_id);
            var modal = $('#user-modal');
            modal.find('.modal-title').html($(this).data('mobile') + "(" + $(this).data('nickname') + ")");
        }
    });

    /**
     * 根据单车的信息更新InfoWindow的内容
     * @param bike
     */
    function updateInfoWindow(bike) {
        // 编号
        $bikeInfo.find('.bike-sn').html(bike.bicycle_sn);
        // 否在线
        bike.online=="0" ? $bikeInfo.find('.bike-info').removeClass("online") : $bikeInfo.find('.bike-info').addClass("online");
        // 电量
        var battery = Math.abs(parseInt(bike.battery));
        $bikeInfo.find('.bike-power').html(battery + '%');
        if(parseInt(bike.battery)>=0) {
            $bikeInfo.find('.bike-battery').removeClass("battery-1 battery-2 battery-3").addClass("battery-0");
        }
        else if(battery>=50) {
            $bikeInfo.find('.bike-battery').removeClass("battery-0 battery-1 battery-2").addClass("battery-3");
        }
        else if(battery>25) {
            $bikeInfo.find('.bike-battery').removeClass("battery-0 battery-1 battery-3").addClass("battery-2");
        }
        else {
            $bikeInfo.find('.bike-battery').removeClass("battery-0 battery-2 battery-3").addClass("battery-1");
        }
        $bikeInfo.find('.bike-battery').attr('title', "电池电压：" + bike.battery_voltage + "V\n充电电压：" + bike.charging_voltage + "V");
        // 开锁关锁状态
        if(bike.lock_status=="0") {
            $bikeInfo.find('.bike-status').removeClass("bike-status-unlocked bike-status-error").addClass("bike-status-locked");
            $bikeInfo.find('.bike-status-text').html('关');
        }
        else if(bike.lock_status=="1") {
            $bikeInfo.find('.bike-status').removeClass("bike-status-locked bike-status-error").addClass("bike-status-unlocked");
            $bikeInfo.find('.bike-status-text').html('开');
        }
        else if(bike.lock_status=="2") {
            $bikeInfo.find('.bike-status').removeClass("bike-status-unlocked bike-status-locked").addClass("bike-status-error");
            $bikeInfo.find('.bike-status-text').html('异常');
        }
        // 状态与报警
        // 运动或静止
        if(bike.moving=="1" && bike.lock_status=="1") {
            $bikeInfo.find('.bike-alarm').removeClass("static-status").addClass("moving-status");
            $bikeInfo.find('.bike-alarm-text').addClass("moving-status").html("运动中");
        }
        else {
            $bikeInfo.find('.bike-alarm').removeClass("moving-status").addClass("static-status");
            $bikeInfo.find('.bike-alarm-text').removeClass("moving-status").html("静止");
        }
        // 低电量报警
        if(bike.low_battary_alarm=="1") {
            $bikeInfo.find('.bike-alarm').removeClass("moving-status").addClass("low-battery-alarm");
            $bikeInfo.find('.bike-alarm-text').html("低电量");
        }
        else {
            $bikeInfo.find('.bike-alarm').removeClass("low-battery-alarm");
        }
        // 非法移动报警
        if(bike.illegal_moving_alarm=="1") {
            $bikeInfo.find('.bike-alarm').removeClass("moving-status").addClass("illegal-moving-alarm");
            $bikeInfo.find('.bike-alarm-text').html("非法移动");
        }
        else {
            $bikeInfo.find('.bike-alarm').removeClass("illegal-moving-alarm");
        }
        if(bike.is_hide=="1"){
            $bikeInfo.find('.bike-is-hide').data('hide',0);
            $bikeInfo.find('.bike-is-hide').html('<i class="fa fa-eye"></i>  显示');
        }else{
            $bikeInfo.find('.bike-is-hide').data('hide',1);
            $bikeInfo.find('.bike-is-hide').html('<i class="fa fa-eye-slash"></i>  隐藏');
        }

        // GPRS信号
        var gprs = Number(bike.gprs);
        $bikeInfo.find('.bike-gprs').attr('title', gprs);
        if(gprs<10) {
            $bikeInfo.find('.bike-gprs').removeClass("signal-1 signal-2 signal-3 signal-4 signal-5").addClass("signal-0");
        } else if(gprs<15) {
            $bikeInfo.find('.bike-gprs').removeClass("signal-0 signal-2 signal-3 signal-4 signal-5").addClass("signal-1");
        } else if(gprs<20) {
            $bikeInfo.find('.bike-gprs').removeClass("signal-0 signal-1 signal-3 signal-4 signal-5").addClass("signal-2");
        } else if(gprs<25) {
            $bikeInfo.find('.bike-gprs').removeClass("signal-0 signal-1 signal-2 signal-4 signal-5").addClass("signal-3");
        } else if(gprs<30) {
            $bikeInfo.find('.bike-gprs').removeClass("signal-0 signal-1 signal-2 signal-3 signal-5").addClass("signal-4");
        } else {
            $bikeInfo.find('.bike-gprs').removeClass("signal-0 signal-1 signal-2 signal-3 signal-4").addClass("signal-5");
        }
        // GPS信号
        var gps = Number(bike.gps);
        $bikeInfo.find('.bike-gps').attr('title', gps);
        if(gps<1) {
            $bikeInfo.find('.bike-gps').removeClass("signal-1 signal-2 signal-3 signal-4 signal-5").addClass("signal-0");
        } else if(gps<3) {
            $bikeInfo.find('.bike-gps').removeClass("signal-0 signal-2 signal-3 signal-4 signal-5").addClass("signal-1");
        } else if(gps<5) {
            $bikeInfo.find('.bike-gps').removeClass("signal-0 signal-1 signal-3 signal-4 signal-5").addClass("signal-2");
        } else if(gps<7) {
            $bikeInfo.find('.bike-gps').removeClass("signal-0 signal-1 signal-2 signal-4 signal-5").addClass("signal-3");
        } else if(gps<9) {
            $bikeInfo.find('.bike-gps').removeClass("signal-0 signal-1 signal-2 signal-3 signal-5").addClass("signal-4");
        } else {
            $bikeInfo.find('.bike-gps').removeClass("signal-0 signal-1 signal-2 signal-3 signal-4").addClass("signal-5");
        }

        // 最后更新时间
        $bikeInfo.find('.bike-last-update > span.last-update-time').html(bike.last_update);
        if(bike.gps_positioning=='1') {
            $bikeInfo.find('.bike-last-update > span.last-update-type').html('GPS');
        } else {
            $bikeInfo.find('.bike-last-update > span.last-update-type').html('基站');
        }
        // 锁编号
        $bikeInfo.find('.bike-lock-sn > span').html(bike.lock_sn);
        // 锁类型
        var lock_type = Number(bike.lock_type);
        switch(lock_type) {
            case 1:
                $bikeInfo.find('.bike-lock-type > span').html('GPRS');
                break;
            case 2:
                $bikeInfo.find('.bike-lock-type > span').html('纯蓝牙');
                break;
            case 3:
                $bikeInfo.find('.bike-lock-type > span').html('机械');
                break;
            case 4:
                $bikeInfo.find('.bike-lock-type > span').html('蓝牙+GPRS');
                break;
            case 5:
                $bikeInfo.find('.bike-lock-type > span').html('亦强蓝牙');
                break;
            case 6:
                $bikeInfo.find('.bike-lock-type > span').html('亦强GPRS');
                break;
        }

        // 地区
        $bikeInfo.find('.bike-region').html('<i class="fa fa-map-pin fa-fw"></i> ' + bike.region_name);
        // 合伙人
        $bikeInfo.find('.bike-cooperator').html('<i class="fa fa-user fa-fw"></i> ' + (bike.cooperator_name || '(平台)'));
        //当前地址
        $bikeInfo.find('.bike-location').html('<i class="fa fa-map-marker fa-fw"></i> <i class="fa fa-spinner fa-pulse fa-fw"></i> <span class="sr-only">Loading...</span>');
        var pos = wgs84togcj02(Number(bike.lng), Number(bike.lat)); //坐标转换
        geocoder.getAddress(pos, function(status, result) {
            if (status === 'complete' && result.info === 'OK') {
                $bikeInfo.find('.bike-location').html('<i class="fa fa-map-marker fa-fw"></i> <span>'+ result.regeocode.formattedAddress +'</span> ');
            }
        });
        // 二维码
        $bikeInfo.find('.bike-info-qrcode > img').attr("src", window.imageUrlBase + "images/qrcode/" + bike.bicycle_sn + '.png');

        //锁使用次数统计
        getUsageCount(function(result){
            console.log(result);
            var total = 0, month = 0, day = 0;
            $.each(result.data.totalCount, function(index, totalCount){
                if(totalCount.bicycle_sn == bike.bicycle_sn){
                    total = totalCount.count;
                }
            });
            $.each(result.data.monthCount, function(index, monthCount){
                if(monthCount.bicycle_sn == bike.bicycle_sn){
                    month = monthCount.count;
                }
            });
            $.each(result.data.dayCount, function(index, dayCount){
                if(dayCount.bicycle_sn == bike.bicycle_sn){
                    day = dayCount.count;
                }
            });
            $bikeInfo.find('.bike-used-times-total > span').text('开锁'+ bike.open_nums +'次；使用'+ total +'次，本月'+ month +'次，今天'+ day +'次');
        });

        //结束订单
        $bikeInfo.find('.bike-instruction > button:eq(3)').data('lng',bike.lng).data('lat',bike.lat);

        //是否故障，违停，激活
        if(bike.is_activated == 1){
            $bikeInfo.find('.horizontal-info div i:eq(2)').css('color','#66d463')
        }else{
            $bikeInfo.find('.horizontal-info div i:eq(2)').css('color','red')
        }
        if(bike.fault == 1){
            $bikeInfo.find('.horizontal-info div i:eq(0)').css('color','red')
        }else{
            $bikeInfo.find('.horizontal-info div i:eq(0)').css('color','#66d463')
        }
        if(bike.illegal_parking== 1){
            $bikeInfo.find('.horizontal-info div i:eq(1)').css('color','red')
        }else{
            $bikeInfo.find('.horizontal-info div i:eq(1)').css('color','#66d463')
        }

        refreshTab();
        refreshAllTab();
    }

    //故障处理弹出框
    $(document).on('click', '.fault-handling-modal', function(){
        $('#fault-handling-modal').remove();
        var faultId = $(this).data('faultId');
        var html =$(
            '<div class="modal fade" id="fault-handling-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-fault-id="'+ faultId +'">'+
            '<div class="modal-dialog" role="document">'+
            '<div class="modal-content">'+
            '<div class="modal-header">'+
            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
            '<h4 class="modal-title" id="myModalLabel">处理建议</h4>'+
            '</div>'+
            '<div class="modal-body">'+
            '<textarea class="form-control content" rows="3" style="resize:none" ></textarea>'+
            '</div>'+
            '<div class="modal-footer">'+
            '<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>'+
            '<button type="button" class="btn btn-primary">确定</button>'+
            '</div>'+
            '</div>'+
            '</div>'+
            '</div>'
        );
        $('#admin-main').append(html);
        $('#fault-handling-modal').modal('show');
    });

    //故障处理提交
    $(document).on('click', '#fault-handling-modal button:eq(2)', function(){
        $.ajax('index.php?route=operation/fault/handling', {
            dataType: 'json',
            data: {
                fault_id: $("#fault-handling-modal").data('faultId'),
                content: $("#fault-handling-modal textarea").val()
            },
            method: 'POST',
            success: function (result) {
                alert(result.msg);
                $('#fault-handling-modal').modal('hide');
            }
        });
        refreshTab();
    });

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

        var bike_sn = $bikeInfo.find('.bike-sn').html();
        var lock_sn = $bikeInfo.find('.bike-lock-sn > span').html();

        if(index<0 || index>5) return; //只有7个标签（0-5）

        if(index>0 && index<5) $tabDiv.find('ul').empty(); //0和5是不清空内容的，只更新内容

        switch (index) {
            case 0:
                refreshGeneral();
                break;
            case 1:
            case 2:
            case 3:
            // case 4:
            case 4:
                $tabDiv.find('ul').empty();
                loadTabData(index, 1, bike_sn);
                break;
            case 5:
                lockInfo(lock_sn);
                refreshGeneral();
                break;
        }
    }

    /**
     * 加载成功当前标签内容（除了概况和指令之外）
     * @param index
     * @param page
     * @param bike_sn
     */
    function loadTabData(index, page, bike_sn) {
        $bikeInfo.find('.bike-info-body').addClass('loading');
        $bikeInfo.find('.bike-info-body > div.active > ul.bike-info-list > li.has-more').remove();
        switch (index) {
            case 1: // 故障
                loadFault(page, bike_sn);
                break;
            case 2: // 违停
                loadIlleagleParking(page, bike_sn);
                break;
            case 3: // 停车
                loadNormalParking(page, bike_sn);
                break;
            // case 4: // 反馈
            //     loadFeekback(page, bike_sn);
            //     break;
            case 4: // 使用
                loadUsedHistory(page, bike_sn);
                break;
        }
    }

    /**
     * TODO 刷新当前标签的概况
     */
    function refreshGeneral() { //刷新概况，包括指令标签里面的定位上传间隔
        $bikeInfo.find('.bike-info-body').addClass('loading');

        tabDataLoaded($bikeInfo.find('.bike-info-body > div:eq(0)'));
    }

    /**
     * 加载热力图数据
     * @param callback
     */
    function getHeatMapData(timeRound, callback) {
        timeRound = '&add_time=' + timeRound || '';
        var uri = "index.php?route=admin/index/apiHeatmapData" + timeRound;
        $.getScript(uri, function () {
            if (typeof callback == 'function') {
                callback();
            }
        });
    }

    /**
     * 加载锁使用次数统计
     * @param callback
     */
    function getUsageCount(callback) {
        $.ajax('index.php?route=admin/index/apiGetUsageCount', {
            dataType: 'JSON',
            global: false,
            success: callback
        });
    }

    /**
     * 加载故障列表
     * @param page
     * @param bike_sn
     */
    function loadFault(page, bike_sn) {
        var $tabDiv = $bikeInfo.find('.bike-info-body > div:eq(1)');
        $.ajax('index.php?route=admin/index/apiGetFaults', {
            dataType: 'html',
            data: {page: page, bike_sn: bike_sn},
            method: 'POST',
            global: false,
            success: function (html) {
                $tabDiv.find('ul').append(html);
                tabDataLoaded($tabDiv);
            }
        });
    }

    /**
     * 加载违停列表
     * @param page
     * @param bike_sn
     */
    function loadIlleagleParking(page, bike_sn) {
        var $tabDiv = $bikeInfo.find('.bike-info-body > div:eq(2)');
        $.ajax('index.php?route=admin/index/apiGetIllegalParking', {
            dataType: 'html',
            data: {page: page, bike_sn: bike_sn},
            method: 'POST',
            global: false,
            success: function (html) {
                $tabDiv.find('ul').append(html);
                tabDataLoaded($tabDiv);
            }
        });
    }

    /**
     * 加载停车列表
     * @param page
     * @param bike_sn
     */
    function loadNormalParking(page, bike_sn) {
        var $tabDiv = $bikeInfo.find('.bike-info-body > div:eq(3)');
        $.ajax('index.php?route=admin/index/apiGetNormalParking', {
            dataType: 'html',
            data: {page: page, bike_sn: bike_sn},
            method: 'POST',
            global: false,
            success: function (html) {
                $tabDiv.find('ul').append(html);
                tabDataLoaded($tabDiv);
            }
        });
    }

    /**
     * 加载反馈列表
     * @param page
     * @param bike_sn
     */
    function loadFeekback(page, bike_sn) {
        var $tabDiv = $bikeInfo.find('.bike-info-body > div:eq(4)');
        $.ajax('index.php?route=admin/index/apiGetFeekbacks', {
            dataType: 'html',
            data: {page: page, bike_sn: bike_sn},
            method: 'POST',
            global: false,
            success: function (html) {
                $tabDiv.find('ul').append(html);
                tabDataLoaded($tabDiv);
            }
        });
    }

    /**
     * 加载使用记录列表
     * @param page
     * @param bike_sn
     */
    function loadUsedHistory(page, bike_sn) {
        var $tabDiv = $bikeInfo.find('.bike-info-body > div:eq(4)');
        $.ajax('index.php?route=admin/index/apiGetUsedHistory', {
            dataType: 'html',
            data: {page: page, bike_sn: bike_sn},
            method: 'POST',
            global: false,
            success: function (html) {
                $tabDiv.find('ul').append(html);
                tabDataLoaded($tabDiv);
            }
        });
    }

    /**
     * 关锁
     * @param lock_sn
     */
    function shut(lock_sn) {
        $.ajax('index.php?route=admin/index/shut', {
            dataType: 'json',
            data: {device_id: lock_sn},
            method: 'POST',
            global: false,
            success: function (result) {
                console.log(result);
            }
        });
    }

    /**
     * 开锁
     * @param lock_sn
     */
    function openLock(lock_sn) {
        $.ajax('index.php?route=admin/index/openLock', {
            dataType: 'json',
            data: {device_id: lock_sn},
            method: 'POST',
            global: false,
            success: function (result) {
                console.log(result);
            }
        });
    }

    //隐藏单车
    function hideBike(bike_sn,hide){
        $.ajax('index.php?route=admin/index/hideBike', {
            dataType: 'json',
            data: {bike_sn: bike_sn,hide:hide},
            method: 'POST',
            global: false,
            success: function (result) {
                console.log(result);
                loadMarker();
            }
        });
    }

    //关闭单车信息
    function closeBikeInfo(){
        setActive($bikeInfo.find('.bike-info-tabs > li:first-child'));  //重置第一个标签为active
        setActive($bikeInfo.find('.bike-info-body > div:first-child'));
        map.setStatus({scrollWheel:true});
        map.clearInfoWindow();
        infoWindowOpened = false;
    }

    /**
     * 设置设备锁关是位置回传间隔
     * @param lock_sn
     * @param time
     */
    function setGapTime2(lock_sn, time) {
        $.ajax('index.php?route=admin/index/setGapTime2', {
            dataType: 'json',
            data: {device_id: lock_sn,time: time},
            method: 'POST',
            global: false,
            success: function (result) {
                if(result.errorCode == 0) alert('操作成功');
            }
        });
    }

    /**
     * 设置设备锁开是位置回传间隔
     * @param lock_sn
     * @param time
     */
    function setGapTime(lock_sn, time) {
        $.ajax('index.php?route=admin/index/setGapTime', {
            dataType: 'json',
            data: {device_id: lock_sn, time: time},
            method: 'POST',
            global: false,
            success: function (result) {
                if(result.errorCode == 0) alert('操作成功');
            }
        });
    }

    /**
     * 响铃
     * @param lock_sn
     */
    function beepLock(lock_sn) {
        $.ajax('index.php?route=admin/index/beepLock', {
            dataType: 'json',
            data: {device_id: lock_sn},
            method: 'POST',
            global: false,
            success: function (result) {
                console.log(result);
            }
        });
    }

    /**
     * 结束订单
     * @param lock_sn
     * @param lng
     * @param lat
     */
    function finishOrder(lock_sn,lng,lat) {
        $.ajax('index.php?route=admin/index/finishOrder', {
            dataType: 'json',
            data: {
                device_id: lock_sn,
                lng: lng,
                lat: lat
            },
            method: 'POST',
            global: false,
            success: function (result) {
                if(!result.errorCode){
                    alert('操作成功');
                    loadMarker();
                }
            }
        });
    }

    /**
     * 锁资料
     * @param lock_sn
     */
    function lockInfo(lock_sn) {
        $.ajax('index.php?route=admin/index/lockInfo', {
            dataType: 'json',
            data: {device_id: lock_sn},
            method: 'POST',
            global: false,
            success: function (result) {
                $bikeInfo.find('.bike-instruction > div:eq(0) > input').val(result.data.set_gap_time2);
                $bikeInfo.find('.bike-instruction > div:eq(1) > input').val(result.data.set_gap_time);
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

    // $(document).on('click',".amap-marker",function(){
    //     refreshTab();
    // });

    /////////////////////////////////////////---工具栏---////////////////////////////////////////
    //地图工具栏合伙人区域列表
     cooperator(function (result) {
        //初始化合伙人区域下拉列表
        $.each(result.data.cooperator, function(index, data){
            html = '<option value="'+ data.cooperator_id +'">'+ data.cooperator_name +'</option>';
            $("#map-toolbar .cooperator-selecter").append(html);
        });
        // $.each(result.data.region, function(index, data){
        //     html = '<option value="'+ data.region_name +'">'+ data.region_name +'</option>';
        //     $("#map-toolbar .region-selecter").append(html);
        // });

        //选择合伙人后区域发生改变
        $("#map-toolbar .cooperator-selecter").change(function(){
            if($(this).val() == ''){
                $("#map-toolbar .region-selecter").html('<option value="">所有区域</option>');
                $.each(result.data.region, function(index, data){
                    html = '<option value="'+ data.region_name +'">'+ data.region_name +'</option>';
                    $("#map-toolbar .region-selecter").append(html);
                });
                return;
            }
            $("#map-toolbar .region-selecter").html('<option value="">所有区域</option>');
            var cooperator_id = $(this).val();
            $.each(result.data.cooperatorToRegion, function(index, data){
                if(data.cooperator_id == cooperator_id){
                    $.each(result.data.region, function(index ,region){
                        if(region.region_id == data.region_id){
                            html = '<option value="'+ region.region_name +'">'+ region.region_name +'</option>';
                            $("#map-toolbar .region-selecter").append(html);
                        }
                    });
                }
            });
        });
    });


    //选择区域后地图定位偏移到该地区
    $("#map-toolbar .region-selecter").change(function(){
        // loadMarker();
        // 地图事件处理
        map.on('moveend', function(){
            console.log('移动结束事件');
            loadMarker();
        });
        map.on('zoomend', loadMarker);

        var city = $(this).val();
        $("[name = 'region_name']").val(city);
        // map.setCity(city);
        //地理编码,返回地理编码结果
        geocoder.getLocation(city, function (status, result) {
            if (status === 'complete' && result.info === 'OK') {
                map.setZoomAndCenter(12,result.geocodes[0].location);
            } else {
                if(city == ''){
                    map.setZoom(5);
                }else{
                    alert("找不到相关地址");
                }
            }
        });
/*        geocoder.getLocation(city, function(status, result) {
            if (status === 'complete' && result.info === 'OK') {
                console.log(result);
                map.setZoomAndCenter(11, [result.geocodes[0].location.lng, result.geocodes[0].location.lat]);
                //TODO:获得了有效经纬度，可以做一些展示工作
                //比如在获得的经纬度上打上一个Marker
            }else{
                //获取经纬度失败
            }
        });
*/
    });

    //合伙人区域列表
    function cooperator(callback){
        $.ajax('index.php?route=admin/index/cooperator', {
            dataType: 'json',
            data: {},
            method: 'POST',
            global: false,
            success:callback/* function (result) {
                callback(result);
            }*/
        });
    }

    /**
     * 合伙人和区域列表（树）
     */
    var data = [{
        text: '西通电子',
        place_id: 0,
        state: {
            selected: true
        },
        nodes:[{
            text: '珠海市',
            place_id: 1,
            state: {
                expanded: true
            }
        }]
    }];
    var treeview = $('#treeview');
    treeview.treeview({data: data});

    /**
     * 合伙人和区域列表（树）的事件处理
     */
    treeview.on('click', 'ul li span.expand-icon', function(e){ // 点击树上的展开或者收缩按钮
        e.preventDefault();
        e.stopPropagation();

        var nodeid = $(this).parent().data('nodeid');
        treeview.treeview('toggleNodeExpanded', nodeid);
    }).on('click', 'ul li', function(){ // 点击树的某一项
        var nodeid = $(this).data('nodeid'), t = '';
        console.log(treeview.treeview('getNode', nodeid).place_id);
        if(nodeid!=0) {
            do{
                var parent = treeview.treeview('getParent', nodeid);
                if(parent && parent.nodeId!=0) {
                    t = parent.text + ' - ' + t;
                    nodeid = parent.nodeId;
                }
            }while(parent && parent.nodeId!=0);
        }
        $(this).parentsUntil('.dropdown').last().prev().html(t + $(this).text() + ' <span class="caret"></span>');
    });

    /**
     * 工具栏上的刷新按钮：重新刷新Marker点
     */
    $('.btn-refresh-marker').on('click', loadMarker);

    /**
     * TODO 工具栏上的“标注”按钮
     */
    $('.btn-map-label').on('click', function () {

    });

    /**
     * 初始化测距工具
     */
    var ruler; //测距工具
    function initRuler() {
        map.plugin(["AMap.RangingTool"], function() {
            ruler = new AMap.RangingTool(map);
            AMap.event.addListener(ruler, "end", function(e) {
                ruler.turnOff();
                map.setDefaultCursor(__defaultCursor);
            });
        });
    }

    /**
     * 工具栏上的工具：测距和分享
     */
    $('.tools-select').on('click', '> li.tool-ruler', function () { // 测距
        __defaultCursor = map.getDefaultCursor();
        map.setDefaultCursor('crosshair');
        ruler.turnOn();
    }).on('click', '> li.tool-share', function () { // TODO 分享

    });
    var __defaultCursor;

    /**
     * 全屏地图
     */
    $('.btn-map-maximize').on('click', function() {
        if(__map_maximized) {
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

    /**
     * 显示热力图
     */
    $('input#show-heatmap').on('change', function() {
        showHeatMap = $(this).prop('checked');
        // 关闭显示Marker
        $.each(_markers, function (index, marker) {
            if(typeof marker != 'undefined') {
                showHeatMap ? marker.hide() : marker.show() ;
            }
        });
        // 显示热力图
        if (showHeatMap) {
            // 初始化热力图数据
            if (typeof heatmapData == 'undefined') {
                getHeatMapData('', function() {
                    $(".heatmap-box").show();
                    $('.show-heatmap-select li').first().trigger("click");
                    heatmap.show();
                });
            } else {
                $(".heatmap-box").show();
                $('.show-heatmap-select li').first().trigger("click");
                heatmap.show();
            }

        } else { // 关闭热力图
            heatmap.hide();
            $(".heatmap-box").hide();
        }
    });

    /**
     * 热力图搜索时间段
     */
    $(".heatmap_datetimepicker").daterangepicker({
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
        }
    }).change(function(e) {
        var timeRound = $(this).val();
        getHeatMapData(timeRound, function() {
            loadHeatMap($('.show-heatmap-select li.active').data('heatmap_type') || "");
        });
    });

    /**
     * 热力图选项
     */
    $('.show-heatmap-select li').on('click', function() {
        if($(this).hasClass('active')) return;
        $(this).parent().prev().html($(this).find('a').text() + ' <span class="caret"></span>');
        setActive($(this));
        showHeatMapStatusType = $(this).data('heatmap_type');
        loadHeatMap(showHeatMapStatusType);
    });


    /**
     * 显示单车编号
     */
    $('input#show-bike-number').on('change', function() {
        showBikeNumber = $(this).prop('checked');
        $(".bike-marker-bike-number").toggle(showBikeNumber);
    });

    /**
     * 工具栏上显示单车的选择菜单
     */
    $('.show-bike-select li').on('click', function() {
        if($(this).hasClass('active')) return;
        $(this).parent().prev().html($(this).find('a').text() + ' <span class="caret"></span>');
        setActive($(this));
        showBikeStatusType = $(this).data('bike_type');
        for(var i in _markers) {
            isMarkerVisible(_markers[i].bike) ? _markers[i].show() : _markers[i].hide();
        }
    });



    /**
     * 根据单车类型的选择来决定某个marker点是否要显示
     * @param marker
     */
    function isMarkerVisible(bike) {
        if(showBikeStatusType=='') {
            return bike.is_hide=='0';
        }
        else if(showBikeStatusType=='cant_finish') {
            return  bike.cant_finish=='1' && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='low_battery') {
            return Math.abs(parseInt(bike.battery))<=25 && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='illegal_parking') {
            return bike.hasIllegalParking=='1' && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='fault') {
            return bike.hasFault=='1' && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='offline24') {
            return bike.offline24=='1' && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='offline') {
            return bike.offline=='1' && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='using') {
            return bike.is_using=='1' && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='noUsedDays2') {
            return parseInt(bike.noUsedDays)>=2 && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='noUsedDays3') {
            return parseInt(bike.noUsedDays)>=3 && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='noUsedDays4') {
            return parseInt(bike.noUsedDays)>=4 && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='noUsedDays5') {
            return parseInt(bike.noUsedDays)>=5 && bike.is_hide=='0';
        }
        else if(showBikeStatusType=='noUsedDays6') {
            return  parseInt(bike.noUsedDays)>=6 && bike.is_hide=='0';
        }else if(showBikeStatusType == 'bikeHide'){
            return bike.is_hide=='1';
        }
        return true;
    }




    /////////////////////////////////////////---单车列表---////////////////////////////////////////

    $('.show-bike-type-select li').on('click', function() {
        var checkbox = $(this).find('input[type="checkbox"]');
        checkbox.prop('checked', !checkbox.prop('checked'));
        updateBikeListType();
    });

    $('.show-bike-type-select li input[type="checkbox"]').on('click', function() {
        $(this).prop('checked', !$(this).prop('checked'));
        console.log(this, $(this),$(this).prop('checked'));

        updateBikeListType();
    });

    function updateBikeListType() {
        $('.bike-list').html('');
        $("input[name = 'q']").val('');
        var types = [];
        $('.show-bike-type-select input[type="checkbox"]:checked').each(function() {
            var t = $.trim($(this).parent().text());
            if(t.length) types.push(t);
        });
        var html = (types.length ? types.join('/') : '(全部)') + ' <span class="caret pull-right"></span>';
        $('.show-bike-type-select button').html(html);
    }


    /////////////////////////////////////////---公共函数---////////////////////////////////////////
    /**
     * 设置某个元素是active，其siblings都去掉active
     * @param $dom
     */
    function setActive($dom) {
        $dom.addClass('active').siblings().removeClass('active');
    }

    //用户信息模板
    function userInfoWindow(user_id) {
        userInfoWindowModel = $(
            '<div class="modal-dialog modal-lg" role="document">'+
            '<div class="modal-content">'+
            '<div class="modal-header">'+
            '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+
            '<h4 class="modal-title"></h4>'+
            '</div>'+
            '<div class="modal-body" style="padding-bottom: 0px;">'+
            '<ul class="nav nav-tabs nav-justified" role="tablist">'+
            '<li role="presentation" class="active"><a href="#user-modal-info" no-ajax="true" aria-controls="user-modal-info" role="tab" data-toggle="tab">基本信息</a></li>'+
            '<li role="presentation"><a href="#user-modal-money" no-ajax="true" aria-controls="user-modal-money" role="tab" data-toggle="tab">充值/提现</a></li>'+
            '<li role="presentation"><a href="#user-modal-consume" no-ajax="true" aria-controls="user-modal-consume" role="tab" data-toggle="tab">消费记录</a></li>'+
            '<li role="presentation"><a href="#user-modal-coupon" no-ajax="true" aria-controls="user-modal-coupon" role="tab" data-toggle="tab">优惠券</a></li>'+
            '<li role="presentation"><a href="#user-modal-credit" no-ajax="true" aria-controls="user-modal-credit" role="tab" data-toggle="tab">信用记录</a></li>'+
            '</ul>'+

            '<!-- Tab panes -->'+
            '<div class="tab-content">'+
            '<div role="tabpanel" class="tab-pane active" id="user-modal-info">'+
                // '基本信息（头像，用户名，手机号，实名认证状态，实名，身份证号码）'+
            '</div>'+
            '<div role="tabpanel" class="tab-pane" id="user-modal-money" style="overflow-y: auto; max-height: 600px;">'+
            '<table class="table table-striped table-hover" style="margin-bottom: 0px;">'+
            '<thead><tr><th>类型</th><th>时间</th><th>金额</th><th>渠道</th><th>状态</th></tr></thead>'+
            '<tbody>'+
                // '<tr><td>退押金</td><td>2017-04-07 11:22:33</td><td>￥99.00</td><td>支付宝</td><td>未付款</td><td><button class="btn btn-xs btn-default">退押金</button></td></tr>'+
                // '<tr><td>充余额</td><td>2017-04-06 11:22:33</td><td>￥50.00</td><td>微信小程序</td><td>已付款</td><td>-</td></tr>'+
                // '<tr><td>充余额</td><td>2017-04-04 11:22:33</td><td>￥20.00</td><td>微信公众号</td><td>未付款</td><td>-</td></tr>'+
                // '<tr><td>充余额</td><td>2017-04-03 11:22:33</td><td>￥20.00</td><td>APP-微信支付</td><td>已付款</td><td>-</td></tr>'+
                // '<tr><td>充押金</td><td>2017-04-02 11:22:33</td><td>￥99.00</td><td>支付宝</td><td>已付款</td><td>-</td></tr>'+
            '</tbody>'+
                // '<!--<tbody><tr><td colspan="6" class="text-center">暂无充值记录</td></tr></tbody>-->'+
            '</table>'+
                // '<div class="has-more" data-next="2" style="text-align: center;"><button class="btn btn-xs btn-default btn-no-border">加载更多</button></div>'+
            '</div>'+
            '<div role="tabpanel" class="tab-pane" id="user-modal-consume" style="overflow-y: auto; max-height: 600px;">'+
            '<table class="table table-striped table-hover" style="margin-bottom: 0px;">'+
            '<thead><tr><th>单车</th><th>区域</th><th>金额</th><th>结算时间</th><th>优惠券</th><th>免费车</th><th>月卡</th><th>状态</th></tr></thead>'+
            '<tbody>'+
            '</tbody>'+
                // '<!--<tbody><tr><td colspan="6" class="text-center">暂无消费记录</td></tr></tbody>-->'+
            '</table>'+
                // '<div class="has-more" data-next="2" style="text-align: center;"><button class="btn btn-xs btn-default btn-no-border">加载更多</button></div>'+
            '</div>'+
            '<div role="tabpanel" class="tab-pane" id="user-modal-coupon" style="overflow-y: auto; max-height: 600px;">'+
            '<form class="form-inline">'+
            '<div class="form-group">'+
            '<label for="user-modal-coupon-type">类型</label>'+
            '<select name="coupon_type" type="text" class="form-control" id="user-modal-coupon-type">'+
            '<option value="1">按时间的用车券</option>'+
                // '<option value="2">按次数的用车券</option>'+
            '<option value="3">代金券</option>'+
            '<option value="4" selected>折扣券</option>'+
            '</select>'+
            '</div>'+
            '<div class="form-group">'+
            '<label for="user-modal-coupon-number">&nbsp;&nbsp;数量</label>'+
            '<div class="input-group">'+
            '<input type="number" step="0.1" class="form-control" id="user-modal-coupon-number" name="number" style="width: 80px;">'+
            '<div class="input-group-addon" id="user-modal-coupon-unit">折</div>'+
            '</div>'+
            '</div>'+
            '<div class="form-group">'+
            '<label>&nbsp;&nbsp;时间有效</label>'+
            '<div class="input-group">'+
            '<input type="text" name="valid_time" value="" class="form-control date-range">'+
            '</div>'+
            '</div>'+
            '<!--注意：请按选择的类型来设置数量的step和单位-->'+
            '<button type="button" class="btn btn-primary coupon-submit"><i class="fa fa-plus"></i> 添加</button>'+
            '<input type="hidden" name="mobiles" value="" />'+
            '</form>'+
            '<table class="table table-striped table-hover" style="margin-bottom: 0px;">'+
            '<thead><tr><th>类型</th><th>数量</th><th>领/发券日期</th><th>有效期</th><th>使用时间</th></tr></thead>'+
            '<tbody>'+
                //     '<tr><td>用车券</td><td>1小时</td><td>2017-04-01</td><td>2017-08-01</td><td>2017-04-05 11:22:33</td></tr>'+
                // '<tr><td>用车券</td><td>0.5小时</td><td>2017-04-01</td><td>2017-08-01</td><td>2017-04-05 11:22:33</td></tr>'+
                // '<tr><td>用车券</td><td>1次</td><td>2017-04-01</td><td>2017-08-01</td><td>2017-04-05 11:22:33</td></tr>'+
                // '<tr><td>现金券</td><td>2元</td><td>2017-04-01</td><td>2017-08-01</td><td>2017-04-05 11:22:33</td></tr>'+
                // '<tr><td>折扣券</td><td>8.5折</td><td>2017-04-01</td><td>2017-08-01</td><td>2017-04-05 11:22:33</td></tr>'+
            '</tbody>'+
                // '<!--<tbody><tr><td colspan="5" class="text-center">暂无记录</td></tr></tbody>-->'+
            '</table>'+
                // '<div class="has-more" data-next="2" style="text-align: center;"><button class="btn btn-xs btn-default btn-no-border">加载更多</button></div>'+
            '</div>'+
            '<div role="tabpanel" class="tab-pane" id="user-modal-credit" style="overflow-y: auto; max-height: 600px;">'+
            '<div class="user-modal-credit-outter"><div class="user-modal-credit-inner">0</div></div>'+
            '<table class="table table-striped table-hover" style="margin-bottom: 0px;">'+
            '<thead><tr><th>时间</th><th>增减</th><th>原因</th><th>操作员</th><th>备注</th></tr></thead>'+
            '<tbody>'+
                //     '<tr><td>2017-04-05 11:22:33</td><td>+1</td><td>骑行完成</td><td>(系统)</td><td>-</td></tr>'+
                // '<tr><td>2017-04-05 11:22:33</td><td>+1</td><td>骑行完成</td><td>(系统)</td><td>-</td></tr>'+
                // '<tr><td>2017-04-05 11:22:33</td><td>-20</td><td>违停</td><td>dujiangyan</td><td>这里是违停的具体说明</td></tr>'+
                // '<tr><td>2017-04-05 11:22:33</td><td>+10</td><td>通过实名认证</td><td>(系统)</td><td>-</td></tr>'+
            '</tbody>'+
                // '<!--<tbody><tr><td colspan="5" class="text-center">暂无记录</td></tr></tbody>-->'+
            '</table>'+
                // '<div class="has-more" data-next="2" style="text-align: center;"><button class="btn btn-xs btn-default btn-no-border">加载更多</button></div>'+
            '</div>'+
            '</div>'+
            '</div>'+
            '<div class="modal-footer">'+
            '<button type="button" class="btn btn-default" data-dismiss="modal">关闭</button>'+
            '</div>'+
            '</div>'+
            '</div>'
        );

        $("#user-modal").html(userInfoWindowModel);

        userInfoWindowModel.on("click", "#user-modal-money .has-more", function(){
            var page = $("#user-modal-money .has-more").data('next');
            $("#user-modal-money .has-more").hide();
            cashapply(user_id, page);
            $("#user-modal-money .has-more").data('next',page+1)
        });
        userInfoWindowModel.on("click", "#user-modal-consume .has-more", function(){
            var page = $("#user-modal-consume .has-more").data('next');
            $("#user-modal-consume .has-more").hide();
            order(user_id, page);
            $("#user-modal-consume .has-more").data('next',page+1)
        });
        userInfoWindowModel.on("click", "#user-modal-coupon .has-more", function(){
            var page = $("#user-modal-coupon .has-more").data('next');
            $("#user-modal-coupon .has-more").hide();
            coupon(user_id, page);
            $("#user-modal-coupon .has-more").data('next',page+1)
        });
        userInfoWindowModel.on("click", "#user-modal-credit .has-more", function(){
            var page = $("#user-modal-credit .has-more").data('next');
            $("#user-modal-credit .has-more").hide();
            points(user_id, page);
            $("#user-modal-credit .has-more").data('next',page+1)
        });

        //点击订单记录单车编号
        userInfoWindowModel.on("click", "#order-bicycle_sn", function(){
            $('#user-modal').modal('hide');
            var bicycle_sn = $(this).text();
            $.each(_markers, function (index, marker) {
                if(typeof marker == 'undefined') return;
                if(marker.bike.bicycle_sn == bicycle_sn) {
                    updateInfoWindow(marker.bike);
                    infoWindow.open(map, marker.getPosition());
                    //                    map.setZoomAndCenter(11, [marker.bike.lng, marker.bike.lat]);
                }
            });
        });

        //添加优惠券提交
        userInfoWindowModel.on("click", ".coupon-submit", function(){
            $.ajax('index.php?route=admin/index/couponAdd', {
                dataType: 'json',
                data: userInfoWindowModel.find('.form-inline').serialize(),
                method: 'POST',
                global: false,
                success: function (result) {
                    userInfoWindowModel.find('#user-modal-coupon table tbody').empty();
                    coupon(user_id, 1);
                    alert(result.msg);
                }
            });
        });

        //处理退款
        userInfoWindowModel.on("click", "#user-modal-money table tbody tr td:eq(5) button", function(){
            var pdc_id = $(this).data('id');
            $.ajax('index.php?route=admin/index/refund', {
                dataType: 'json',
                data: {type:'agree',pdc_id:pdc_id},
                method: 'POST',
                global: false,
                success: function (result) {
                    alert(result.msg);
                }
            });
        });

        //优惠券类型
        $('[name="coupon_type"]').on("change", function () {
            if ($(this).val() == 1) {
                $('[name="number"]').parents(".input-group").html(
                    '<input type="number" class="form-control" id="user-modal-coupon-number" name="number" style="width: 80px;">'+
                    '<div class="input-group-addon" id="user-modal-coupon-unit">分钟</div>'
                );
            } if($(this).val() == 3) {
                $('[name="number"]').parents(".input-group").html(
                    '<input type="number"  class="form-control" id="user-modal-coupon-number" name="number" style="width: 80px;">'+
                    '<div class="input-group-addon" id="user-modal-coupon-unit">元</div>'
                );
            }if($(this).val() == 4) {
                $('[name="number"]').parents(".input-group").html(
                    '<input type="number" step="0.1" class="form-control" id="user-modal-coupon-number" name="number" style="width: 80px;">'+
                    '<div class="input-group-addon" id="user-modal-coupon-unit">折</div>'
                );
            }
        });

        //日期选择插件
        $('.date-range').daterangepicker({
            locale:{
                format: 'YYYY-MM-DD',
                isAutoVal:false
            }
        });
    }

    //加载用户信息
    function updateUserInfoWindow(user_id){
        $.ajax('index.php?route=admin/index/userInfo', {
            dataType: 'json',
            data: {user_id: user_id},
            method: 'POST',
            global: false,
            success: function (result) {
                console.log(result);
                var userInfo =
                    '<img style="width: 90px;" class="profile-user-img img-responsive img-circle" src="'+ result.data.avatar +'" alt="User profile picture">'+
                    '<div class="row" style = "margin-top: 15px;">'+
                        // '<div class="form-group col-sm-6">'+
                        // '<label for="" class="col-sm-5 control-label">头像</label>'+
                        // '<div class="col-sm-7">'+
                        // '<span><img style="max-width:100px;max-height:100px;" src="'+ result.data.avatar +'"/></span>'+
                        // '</div>'+
                        // '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">用户名：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.nickname +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">手机号：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.mobile +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">实名认证状态：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.verify_state +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">实名：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.real_name +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">身份证号码：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.identification +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">是否可骑车：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.available_state +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">是否冻结：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.is_freeze +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">当前余额：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>￥'+ result.data.available_deposit +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">当前押金：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>￥'+ result.data.deposit +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">上次修改手机时间：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.last_update_mobile_time +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">归属合伙人：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.cooperator_id +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">推荐人数：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.recommend_num +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">最后登录IP：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.ip +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">登陆时间：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.login_time +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">登陆设备：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.uuid +'</span>'+
                    '</div>'+
                    '</div>'+

                    '<div class="form-group col-sm-6">'+
                    '<label for="" class="col-sm-5 control-label">注册时间：</label>'+
                    '<div class="col-sm-7">'+
                    '<span>'+ result.data.add_time +'</span>'+
                    '</div>'+
                    '</div>'+
                    '</div>';

                userInfoWindowModel.find('#user-modal-info').html(userInfo);

                userInfoWindowModel.find('.user-modal-credit-inner').text(result.data.credit_point)

                userInfoWindowModel.find('[name = "mobiles"]').val(result.data.mobile);
            }
        });
        cashapply(user_id, 1);
        order(user_id, 1);
        coupon(user_id, 1);
        points(user_id, 1);
    }

    function cashapply(user_id, page){
        $.ajax('index.php?route=admin/index/cashapply', {
            dataType: 'HTML',
            data: {user_id: user_id, page: page},
            method: 'POST',
            global: false,
            success: function (html) {
                if(html){
                    userInfoWindowModel.find('#user-modal-money table tbody').append(html);
                }
            }
        });
    }
    function order(user_id, page){
        $.ajax('index.php?route=admin/index/order', {
            dataType: 'HTML',
            data: {user_id: user_id, page: page},
            method: 'POST',
            global: false,
            success: function (html) {
                if(html){
                    userInfoWindowModel.find('#user-modal-consume table tbody').append(html);
                }
            }
        });
    }
    function coupon(user_id, page){
        $.ajax('index.php?route=admin/index/coupon', {
            dataType: 'HTML',
            data: {user_id: user_id, page: page},
            method: 'POST',
            global: false,
            success: function (html) {
                if(html){
                    userInfoWindowModel.find('#user-modal-coupon table tbody').append(html);
                }
            }
        });
    }
    function points(user_id, page){
        $.ajax('index.php?route=admin/index/points', {
            dataType: 'HTML',
            data: {user_id: user_id, page: page},
            method: 'POST',
            global: false,
            success: function (html) {
                if(html){
                    userInfoWindowModel.find('#user-modal-credit table tbody').append(html);
                }
            }
        });
    }
});
