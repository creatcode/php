<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span><?php echo @$lang['t2'];?></span>
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
                    <li class="active"><a href="javascript:;" data-toggle="tab"><?php echo @$lang['t44'];?></a></li>
                    <li class=""><a href="<?php echo $lock_action; ?>" data-toggle="tab"><?php echo @$lang['t45'];?></a></li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="bicycle">
                        <form id="table_form" class="table_form" method="post" action="<?php echo $action; ?>">
                        <div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
                            <!--合伙人：
                            <select name="cooper_id" class="input-sm">
                                <option value="0">总部</option>
                                <?php foreach($cooperators as $v){ ?>
                                <option value="<?php echo $v['id']?>"><?php echo $v['cooperator_name']?></option>
                                <?php }?>
                            </select>-->
                            <?php echo @$lang['t46'];?>：
                            <select name="type" class="input-sm">
                                <option value="1">小强1</option>
                                <option value="2">小强2</option>
                            </select>
                            <?php echo @$lang['t47'];?>：
                            <select name="region" class="input-sm" onchange="show_city(this)">
                                <?php foreach($regions as $v){ ?>
                                <option value="<?php echo $v['region_id']?>"><?php echo $v['region_name']?></option>
                                <?php }?>
                            </select>
                            <?php echo @$lang['t48'];?>：
                            <select name="city" class="input-sm" id="city_id">
                          
                                <option value="0">--<?php echo @$lang['t8'];?>--</option>
                             
                            </select>
                            <textarea class="hide" name="bicycle_list" >{{bicycleList}}</textarea>
                        </div>
                        <div class="form-group">
                            <button type="button" v-on:click="upload" class="btn btn-default btn-sm"><i class="fa fa-upload"></i>&nbsp;<?php echo @$lang['t13'];?></button>
                        </div>
                        <?php if (isset($error)) { ?>
                        <div class="alert alert-danger"><i class="fa fa-exclamation-circle"></i>&nbsp;<?php echo $error; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                            <table id="improtTable" class="table table-bordered table-hover dataTable" role="grid">
                                <thead>
                                <tr>
                                    <td><?php echo @$lang['t49'];?></td>
                                    <td><?php echo @$lang['t26'];?></td>
                                    <td><?php echo @$lang['t50'];?></td>
                                    <td><?php echo @$lang['t51'];?></td>
                                    <td><?php echo @$lang['t52'];?></td>
                                </tr>
                                </thead>
                                <tbody>
                                <tr v-for="(item, index) in bicycleList">
                                    <td>{{item['bicycle_sn']}}</td>
                                    <td>{{item['lock_sn']}}</td>
                                    <td>{{item['has_bicycle_sn'] | translate}}</td>
                                    <td>{{item['has_lock_sn'] | translate}}</td>
                                    <td>
                                        <div class="btn-group">
                                            <button v-on:click="remove(index)" type="button" class="btn btn-danger"><i class="fa fa-fw fa-trash-o"></i></button>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                            <div class="box-footer" style="text-align:center">
                                <button type="submit" class="btn btn-info pull-right"  style="padding:0 30px;height:40px;font-size:14px;position:relative;right:50%"><?php echo @$lang['t53'];?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- /.content -->
<script src="<?php echo $static_server;?>js/vue.js"></script>
<script type="text/javascript">
    new Vue({
        el: '.content',
        data: {
            bicycleList: ''
        },
        methods:{
            upload: function (event) {
                var vueThis = this;

                var t = $(event);
                // 标签名称，上传时区分不同文件对象
                var tag = t.data("tag");
                // 附带标识参数
                var tage = t.data("tage");
                // 上传地址
                var action = t.data("action");
                // 清除旧表单
                $('#form-upload-' + tag).remove();
                // 添加新表单
                $('body').prepend('<form enctype="multipart/form-data" id="form-upload-'+ tag +'" style="display: none;"><input type="file" name="upfile" value="" /><input type="hidden" name="tage" value="'+ tage +'" /></form>');
                // 触发上传按钮
                $('#form-upload-'+ tag +' input[name=\'upfile\']').trigger('click');
                // 清除无效的计时器
                if (typeof timer != 'undefined') {
                    clearInterval(timer);
                }
                // 监听上传按钮是否有选中上传文件
                timer = setInterval(function() {
                    if ($('#form-upload-'+ tag +' input[name=\'upfile\']').val() != '') {
                        // 捕捉已选中上传文件，清除监听
                        clearInterval(timer);
                        // 将表单数据通过AJAX请求服务器
                        $.ajax({
                            url: '<?php echo $import_action; ?>',
                            type: 'post',
                            dataType: 'json',
                            data: new FormData($('#form-upload-' + tag)[0]),
                            cache: false,
                            contentType: false,
                            processData: false,
                            beforeSend: function() {
                                // 更改上传按钮图标
                                t.find('i').replaceWith('<i class="fa fa-circle-o-notch fa-spin"></i>');
                                t.prop('disabled', true);
                            },
                            complete: function() {
                                // 还原上传按钮图标
                                t.find('i').replaceWith('<i class="fa fa-upload"></i>');
                                t.prop('disabled', false);
                            },
                            success: function(json) {
                                // 文件上传成功
                                if (json['errorCode'] == 0) {
                                    console.log(vueThis.bicycleList);
                                    vueThis.bicycleList = json['data'];
                                } else {
                                    // 文件上传失败，弹出提示框
                                    alert(json['msg']);
                                }
                            },
                            error: function(xhr, ajaxOptions, thrownError) {
                                // 网络异常
                                alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
                            }
                        });
                    }
                }, 500);
            },
            remove:function(index){
                this.bicycleList.splice(index, 1);
            }
        },
        filters:{
            translate: function ($status) {
                if($status == 1){
                    return '有';
                }else{
                    return '没';
                }
            }
        }
    })
</script>
<script type="text/javascript">

    $('.date-range').daterangepicker({
        locale:{
            format: 'YYYY-MM-DD',
            isAutoVal:false,
        }
    });

</script>
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
        var a='<option value="">--<?php echo @$lang['t8'];?>--</option>';
        if(region_id){
            region_data[region_id].forEach(function (item,index,input) {
		a+="<option value="+index+">"+item+"</option>";
            });  
        }
        $("#city_id").html(a); 
    }
    function init_city(){
        var region_id="<?php echo $filter_regions[0]['region_id'];?>";

        var a='<option value="">--<?php echo @$lang['t8'];?>--</option>';
        if(region_id){
            region_data[region_id].forEach(function (item,index,input) {
		a+="<option value="+index;
                
                a+=">"+item+"</option>";
            });
        }
        $("#city_id").html(a); 
    }
    $(function(){
         init_city();
    });
</script>
<?php echo $footer;?>