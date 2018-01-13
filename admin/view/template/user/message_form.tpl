<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo @$lang['t2'];?></span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
    <?php echo $statistics_in_page_header;?>
</section>
<form class="form-horizontal" method="post" action="<?php echo $action; ?>">
<!-- Main content -->
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
                        <div class="row">
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t16'];?></label>
                                <div class="col-sm-5">
                                    <input type="text" name="msg_title" value="<?php echo $data['msg_title']; ?>" class="form-control">
                                    <?php if (isset($error['msg_title'])) { ?><div class="text-danger"><?php echo $error['msg_title']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t17'];?></label>
                                <div class="col-sm-5">
                                    <button type="button" class="img-thumbnail button-upload" style="outline: none;" data-tag="logo" data-action="<?php echo $upload_action; ?>" data-tage="image">
                                        <img src="<?php echo $data['msg_image_url']; ?>" alt="<?php echo @$lang['t17'];?>" style="max-width: 100px; max-height: 100px;" class="imageurl">
                                        <input type="hidden" name="msg_image" value="<?php echo $data['msg_image']; ?>" placeholder="<?php echo @$lang['t17'];?>" class="filepath">
                                    </button>
                                    <?php if (isset($error['msg_image'])) { ?><div class="text-danger"><?php echo $error['msg_image']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t2'];?><?php echo @$lang['t18'];?></label>
                                <div class="col-sm-5">
                                    <label class="margin-r-5">
                                        <input type="radio" name="user_type" value="0" class="button-all-users" <?php echo $data['user_type']==0 ? 'checked' : ''; ?> />
                                        <?php echo @$lang['t19'];?>
                                    </label>
                                    
                                    <span class="choose-users-num"></span>
                                    <label class="margin-r-5">
                                        <input type="radio" name="user_type" value="2" class="button-all-users" <?php echo $data['user_type']==2 ? 'checked' : ''; ?> /><?php echo @$lang['t43'];?>
                                    <select name="region_id" id="region_id" class="input-sm" onchange="show_city(this)" disabled="disabled">
                                        <option value="">--全部区域--</option>
                                        <?php foreach($filter_regions as $k => $v) { ?>
                                        <option value="<?php echo $v['region_id']; ?>" <?php echo (string)$v['region_id'] == $data['region_id'] ? 'selected' : ''; ?>><?php echo $v['region_name']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <select name="city_id" id="city_id" class="input-sm" disabled="disabled">
                                        <option value="">--全部城市--</option>
                                    </select>
                                    </label>
                                    
                                    <!-- <label>
                                        <input type="radio" name="user_type" value="3" class="button-choose-city" <?php echo $data['user_type']==3 ? 'checked' : ''; ?> />
                                        <?php echo @$lang['t21'];?>
                                        <select name="city_id" class="input-sm" disabled="disabled">
                                            <option value="0">--<?php echo @$lang['t22'];?>--</option>
                                            <?php foreach($filter_citys as $v){
                                                if($data['city_id'] ==$v['city_id']){
                                            ?>
                                            <option selected="selected" value="<?php echo $v['city_id']; ?>"><?php echo $v['city_name']; ?> </option>
                                            <?php
                                                }else{
                                            ?>
                                            <option value="<?php echo $v['city_id']; ?>"><?php echo $v['city_name']; ?> </option>
                                            <?php }
                                             } ?>
                                        </select>
                                    </label> -->
                                    <label>
                                        <input type="radio" name="user_type" value="1" class="button-choose-users" <?php echo $data['user_type']==1 ? 'checked' : ''; ?> />
                                        <?php echo @$lang['t23'];?>
                                    </label>&nbsp;
                                </div>
                            </div>
                            <div class="form-group mobiles-box" <?php echo $data['user_type']==1 ? '' : 'style="display: none;"'; ?>>
                                <label class="col-sm-2 control-label"></label>
                                <div class="col-sm-5">
                                    <textarea name="mobiles" class="form-control" rows="3" placeholder="<?php echo @$lang['t24'];?>"><?php echo $data['mobiles']; ?></textarea>
                                    <?php if (isset($error['mobiles'])) { ?><div class="text-danger"><?php echo $error['mobiles']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t25'];?></label>
                                <div class="col-sm-5">
                                    <textarea name="msg_abstract" class="form-control" rows="3"><?php echo $data['msg_abstract']; ?></textarea>
                                    <?php if (isset($error['msg_abstract'])) { ?><div class="text-danger"><?php echo $error['msg_abstract']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t26'];?></label>
                                <div class="col-sm-5">
                                    <input type="text" name="msg_link" value="<?php echo $data['msg_link']; ?>" class="form-control"> <button id="select_ad_modal" type="button" class="btn btn-sm btn-success margin-r-5">添加广告链接</button>
                                    <?php if (isset($error['msg_link'])) { ?><div class="text-danger"><?php echo $error['msg_link']; ?></div><?php } ?>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-2 control-label"><?php echo @$lang['t27'];?></label>
                                <div class="col-sm-5">
                                    <div class="margin-bottom custom_content">
                                        <textarea name="msg_content" class="form-control" rows="5"><?php echo $data['msg_content']; ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-7">
                                <div class="text-center">
                                    <button type="submit" class="btn btn-sm btn-success margin-r-5" style="padding: 0 30px;height: 40px;font-size: 14px;line-height: 40px;"><?php echo @$lang['t28'];?></button>
                                    <a href="<?php echo $return_action; ?>" class="btn btn-sm btn-default" style="padding: 0 30px;height: 40px;font-size: 14px;line-height: 40px;"><?php echo @$lang['t29'];?></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
</form>
<script type="text/javascript">
    $(function () {
         /**
         * 自定义用户对象
         */
         $('[name="user_type"]').change(function() {
            $(".mobiles-box").toggle($(this).val()=='1');
        });
    })
</script>
<div style="display:none;" class="modal fade bs-example-modal-lg in" id="personAddModel" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="false">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="personAddModelContent">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                <span class="modal-header-title" id="myModalLabel"><?php echo @$lang['t30'];?></span>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-xs-12" id="managerList">

                    </div>
                </div>
            </div>
            <div class="modal-footer" id="pagination">

            </div>
        </div>
    </div>
</div>
<script>
  var region_data=new Array();
    <?php
        foreach($filter_regions as $key=>$val){
    ?>
            region_data[<?php echo $val['region_id']?>]=new Array();
            <?php
                foreach($val['city'] as $key2=>$val2){
            ?>
                region_data[<?php echo $val['region_id']?>][<?php echo $val2['city_id']?>]="<?php echo $val2['city_name']?>";
            <?php
                }
            ?>
    <?php
        } 
    ?>
    function show_city(t){
        var region_id=$(t).val();
        var a='<option value="">--全部城市--</option>';
        if(region_id){
            region_data[region_id].forEach(function (item,index,input) {
        a+="<option value="+index+">"+item+"</option>";
            });
            $("#city_id").html(a); 
        }

    }
    function init_city(){
        var region_id="<?php echo $data['region_id'];?>";
        var city_id="<?php echo $data['city_id'];?>";
        var a='<option value="">--全部城市--</option>';
        if(region_id&&city_id){
            region_data[region_id].forEach(function (item,index,input) {
        a+="<option value="+index;
                if(index==city_id){
                    a+=" selected ";
                }
                a+=">"+item+"</option>";
            });
        }
        $("#city_id").html(a); 
    }
    $(function(){
         init_city();
    });
    
</script>
<script type="text/javascript">   

        $("input[name='user_type']").change(function () {
        var a =$("input[name='user_type']:checked").val();
        if (a==2) {
            $("select[name='region_id']").removeAttr('disabled');
            $("select[name='city_id']").removeAttr('disabled','disabled');
        }else{
            $("select[name='region_id']").attr('disabled','disabled');
            $("select[name='city_id']").attr('disabled','disabled');
        }
    // console.log(a);
    })
    
    
    
    var page = 1;
    function getAdList(page) {
        $.get('<?php echo $get_modal_ad_url;?>', {
            page: page
        }, function (data) {
            if (data.data.items.length > 0) {
                var html = '';
                html += '<thead>' +
                            '<tr>' +
                                '<th><?php echo @$lang['t31'];?></th>' +
                                '<th><?php echo @$lang['t32'];?></th>' +
                                '<th><?php echo @$lang['t33'];?></th>' +
                                '<th><?php echo @$lang['t34'];?></th>' +
                                '<th><?php echo @$lang['t35'];?></th>' +
                            '</tr>' +
                        '</thead>';
                html += '<tbody>';
                var len = data.data.items.length;
                var items = data.data.items;
                for (var i = 0; i < len; i++) {
                    var item = items[i];
                    html += '<tr data-adv_id="' + item.adv_id + '">';
                    html += '<td>' + item.adv_region_id + '</td>';
                    html += '<td>' + item.adv_start_time + '</td>';
                    html += '<td>' + item.adv_end_time + '</td>';
                    html += '<td><img class="bigimage" src="' + item.adv_image + '" height="35"></td>';
                    html += '<td>' + item.adv_add_memo + '</td>';
                    html += '</tr>';
                }
                html += '</tbody>';
                $table = $('<table class="table table-bordered table-hover dataTable" role="grid"></table>');
                $table.html(html);

                $('#managerList').empty();
                $('#managerList').html($table);
            }

            var curPage = data.data.page;
            var pageCount = data.data.totalPage;

            var pagination = '';
            pagination += '<ul class="pagination pager_cus">';
            pagination=pagination+"<li><a><?php echo @$lang['t36'];?> "+(curPage);
            pagination=pagination+" <?php echo @$lang['t37'];?> "+pageCount+" <?php echo @$lang['t38'];?></a></li>";
            pagination += "<li><a href='javascript:getAdList(";
            pagination += page;
            pagination += ");'>« <?php echo @$lang['t39'];?></a></li>";

            if (curPage > 1) {
                pagination += "<li><a href='javascript:getAdList(";
                pagination += (curPage - 1) + ");'>« <?php echo @$lang['t40'];?></a></li>";
            }

            var start = curPage - 3;
            var end = curPage + 3;
            if (start < 0) {
                end = end - start;
            }

            if (end > (pageCount - 1)) {
                end = pageCount - 1;
                start = end - 7;
            }

            for (var j = start; j <= end; j++) {
                if(j > -1 && j < pageCount){
                    if(curPage - 1 == j){
                        pagination += "<li class='active'><a href='javascript:getAdList(";
                        pagination += (j + 1) + ");'>"+(j+1)+"</a></li>";
                    }else{
                        pagination += "<li><a href='javascript:getAdList(";
                        pagination += (j + 1) + ");'>"+(j+1)+"</a></li>";
                    }
                }
            }

            if(curPage < pageCount - 1){
                pagination += "<li><a href='javascript:getAdList(";
                pagination += (curPage + 1) + ");'><?php echo @$lang['t41'];?> »</a></li>";
            }

            pagination += "<li><a href='javascript:getAdList(";
            pagination += pageCount + ");'>« <?php echo @$lang['t42'];?></a></li>";

            $('#pagination').empty();
            $('#pagination').append(pagination);

            $('#personAddModel').modal('show');
        });
    }

    function nextPage() {
        page++;
        getAdList(page);
    }

    $(document).ready(function () {
        $('#select_ad_modal').click(function () {
            getAdList(page);
        });

        $('#managerList').on('click', 'tr', function () {
            var advId = $(this).data('adv_id');
            var apiUrl = "https://api.s-bike.cn/index.php?route=article/index/ad&adv_id=" + advId;
            $('input[name="msg_link"]').val(apiUrl);
            $('#personAddModel').modal('hide');
        });
    });
</script>

<?php echo $footer;?>