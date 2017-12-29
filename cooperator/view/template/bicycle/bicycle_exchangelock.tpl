<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
    <h1 class="pull-left">
        <span>单车管理</span>
        <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
    </h1>
    <?php echo $statistics_in_page_header;?>
</section>
<!-- Main content -->

<?php
	if($type==='normal'){//普通锁换锁
?>
<style>
#breadcrumbs-one{
  background: none;
  border-radius: 5px;
  overflow: hidden;
  width: 290px;
  margin: 0;
  padding: 0;
  list-style: none;
}
#breadcrumbs-one li{
  float: left;
}
#breadcrumbs-one a{
  padding: .7em 1em .7em 2em;
  float: left;
  text-decoration: none;
  color: #444;
  position: relative;
  text-shadow: 0 1px 0 rgba(255,255,255,.5);
  background-color: #ddd;
  background-image: linear-gradient(to right, #f5f5f5, #ddd);  
}
#breadcrumbs-one li:first-child a{
  padding-left: 1em;
  border-radius: 5px 0 0 5px;
}
#breadcrumbs-one a::after,
#breadcrumbs-one a::before{
  content: "";
  position: absolute;
  top: 50%;
  margin-top: -1.5em;   
  border-top: 1.5em solid transparent;
  border-bottom: 1.5em solid transparent;
  border-left: 1em solid;
  right: -1em;
}
#breadcrumbs-one a::after{ 
  z-index: 2;
  border-left-color: #ddd;  
}
#breadcrumbs-one a::before{
  border-left-color: #ccc;  
  right: -1.1em;
  z-index: 1; 
}
</style>
<section class="content">
<ul id="breadcrumbs-one">
    <li class='li_bread li_b_1'><a href="javascript:void(0);">1.锁信息</a></li>
    <li class='li_bread li_b_2'><a href="javascript:void(0);">2.单车信息</a></li>
    <li class='li_bread li_b_3'><a href="javascript:void(0);">3.完成</a></li>
</ul>

<div class="row">
        <div class="col-xs-12">
            <div class="nav-tabs-custom">
                <div class="tab-content">
                    <div class="tab-pane active">
						 <form class="form-horizontal info_fieldset" id='lock_info'><!--info_fieldset这个class没样式的，只是用来控制隐藏/显示而已-->
							<div >
								<form>
									<div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
										锁编号：<input type="text" name="search_lock_sn"  id="search_lock_sn" class="input-sm" style="border: 1px solid #a9a9a9;"/>
										<div class="pull-right">
											<button type='button' class="btn btn-primary btn-sm" onclick='search_lock()' id="btn_search_lock"><i class="fa fa-search"></i>&nbsp;搜索</button>
										</div>
									</div>
								</form> 
								<div class="row">
								
								
									<div class="form-group">
										<label class="col-sm-2 control-label">锁编号</label>
										<div class="col-sm-5">
											<input type="text" name="lock_sn" id="lock_sn" value="" class="form-control" />
										   
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">锁名称</label>
										<div class="col-sm-5">
											<input type="text" name="lock_name" id="lock_name" value="" class="form-control" />
										   
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">锁类型</label>
										<div class="col-sm-5">
											<select name="lock_type" id='lock_type' class="form-control">
												<option  value="1">GPRS</option>
												<option  value="2">蓝牙</option>
												<option  value="3">机械</option>
												<option  value="4">GPRS+蓝牙</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">合伙人</label>
										<div class="col-sm-5">
											<select name="lock_cooperator_id" id="lock_cooperator_id" class="form-control">
												<option value="0">平台</option>
												<?php foreach($cooperators as $v) { ?>
												<option value="<?php echo $v['cooperator_id']; ?>"><?php echo $v['cooperator_name']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">锁平台</label>
										<div class="col-sm-5">
											<select name="lock_platform" id="lock_platform" class="form-control">
												<option  value="0">物联锁旧平台</option>
												<option  value="1">物联锁新平台</option>
												<option  value="2">亦强锁平台</option>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">厂家</label>
										<div class="col-sm-5">
											<input type="text" name="lock_factory" id="lock_factory"  class="form-control">
											
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">批号</label>
										<div class="col-sm-5">
											<input type="text" name="batch_number" id="batch_number"  class="form-control">
									   
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-7">
											<div class="pull-right">
												<button type='button' class="btn btn-sm btn-success margin-r-5" onclick='go_next_lock()'>下一步</button>
											  
											</div>
										</div>
									</div>
									
									
								</div>
							</div>
                        </form>
						
						<form class="form-horizontal info_fieldset" id='bicycle_info'><!--info_fieldset这个class没样式的，只是用来控制隐藏/显示而已-->
							<div >
								<form>
								<div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
									单车编号：<input type="text" name="search_bicycle_sn"  id="search_bicycle_sn" class="input-sm" style="border: 1px solid #a9a9a9;" placeholder='6位编码' maxlength=6/>
									<div class="pull-right">
										<button type='button' class="btn btn-primary btn-sm" onclick='search_bycicle()'><i class="fa fa-search"></i>&nbsp;搜索</button>
									</div>
								</div>
							</form> 
							<div class="row">
								<div class="form-group">
									<label class="col-sm-2 control-label">单车编号</label>
									<div class="col-sm-5">
										<input type="text" name="bicycle_sn" id="bicycle_sn" value="" class="form-control" placeholder='6位编码' maxlength=6/>
									   
									</div>
								</div>
								<div class="form-group">
                                    <label class="col-sm-2 control-label">单车类型</label>
                                    <div class="col-sm-5">
                                        <select name="bicycle_type" id="bicycle_type" class="form-control">
                                            <?php foreach($types as $k => $v) { ?>
                                            <option value="<?php echo $k; ?>"><?php echo $v; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
								<div class="form-group">
                                    <label class="col-sm-2 control-label">所在区域</label>
                                    <div class="col-sm-5">
                                        <select name="bicycle_region_id" id="bicycle_region_id" class="form-control">
                                            <?php foreach($regions as $v) { ?>
                                            <option value="<?php echo $v['region_id']; ?>" ><?php echo $v['region_name']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
								<div class="form-group">
									<label class="col-sm-2 control-label">车锁编号</label>
									<div class="col-sm-5">
										<input type="text" name="bicycle_lock_sn" id="bicycle_lock_sn"  class="form-control" disabled=true/>
									</div>
								</div>
								<div class="form-group">
									<label class="col-sm-2 control-label">合伙人</label>
									<div class="col-sm-5">
										<select name="bicycle_cooperator_id" id="bicycle_cooperator_id" class="form-control">
											<option value="0">平台</option>
											<?php foreach($cooperators as $v) { ?>
											<option value="<?php echo $v['cooperator_id']; ?>"><?php echo $v['cooperator_name']; ?></option>
											<?php } ?>
										</select>
									</div>
								</div>
								<div class="form-group">
									<div class="col-sm-7">
										<div class="pull-right">
											<button type='button' class="btn btn-sm btn-success margin-r-5" onclick='go_next_bicycle()'>下一步</button>
											<a href="javascript:void(0);" onclick='show_step(1)' class="btn btn-default btn-sm">返回</a>
										</div>
									</div>
								</div>	
							</div>
							</div>
                        </form>
						
						<form class="form-horizontal info_fieldset" id='show_info'><!--info_fieldset这个class没样式的，只是用来控制隐藏/显示而已-->
							<div >
								
							<div class="row">
								<div class="form-group">
									<label class="col-sm-2 control-label">单车编号</label>
									<div class="col-sm-5">
										<input type="text" name="show_bicycle_sn" id="show_bicycle_sn" value="" class="form-control" disabled=true/>
										<span>已处理完成</span>
									</div>
								</div>
								
								<div class="form-group">
									<label class="col-sm-2 control-label">新锁编号</label>
									<div class="col-sm-5">
										<input type="text" name="show_new_lock_sn" id="show_new_lock_sn" value="" class="form-control" disabled=true/>
									</div>
								</div>	
								
								<div class="form-group">
									<label class="col-sm-2 control-label">旧锁编号</label>
									<div class="col-sm-5">
										<input type="text" name="show_old_lock_sn" id="show_old_lock_sn" value="" class="form-control" disabled=true/>
									</div>
								</div>	
								<div class="form-group">
									<div class="col-sm-7">
										<div class="pull-right">
											<a href="<?php echo $return_action; ?>" class="btn btn-default btn-sm">完成</a>
										</div>
									</div>
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

<script type="text/javascript">
    $(document).ready(function(){
		show_step(1);
	});
function search_lock(){<!--搜索锁-->
	var lock_sn=$('#search_lock_sn').val().trim();
	if(!lock_sn){
		return false;
	}
	loadingFadeIn();
	$.post('',{search_lock_sn:lock_sn,op:'search_lock_sn'},function(data){
		loadingFadeOut();
		console.log(data);
		if(data.data.lock_num==0){
			alert('没有查到此锁，请重新输入');
			return false;
		}else if(data.data.lock_num>1){
			alert('查到多个符合条件的锁，请重新输入');
			return false;
		}else{
			$('#lock_sn').val(data.data.lock_info[0]['lock_sn']);
			$('#lock_name').val(data.data.lock_info[0]['lock_name']);
			$("#lock_type option[value='"+data.data.lock_info[0]['lock_type']+"']").attr("selected", true);
			$("#lock_cooperator_id option[value='"+data.data.lock_info[0]['cooperator_id']+"']").attr("selected", true);
			$("#lock_platform option[value='"+data.data.lock_info[0]['lock_platform']+"']").attr("selected", true);
			$('#lock_factory').val(data.data.lock_info[0]['lock_factory']);
			$('#batch_number').val(data.data.lock_info[0]['batch_number']);
		}
	},'json');
}
function go_next_lock(){<!--锁信息那个div里面的下一步-->
	var lock_sn=$('#lock_sn').val().trim();
	var lock_type=$('#lock_type').val();
	if(!lock_sn){
		alert('锁编号必填');
		return false;
	}else{
	    if(lock_type==2){
			alert('锁类型不能是蓝牙锁');
			return false;
		}
		$('#bicycle_lock_sn').val(lock_sn);
		show_step(2);
	}
}

function search_bycicle(){<!--搜索单车-->
	var bicycle_sn=$('#search_bicycle_sn').val().trim();
	if(!bicycle_sn){
		return false;
	}
	var reg = /^\d{6}$/;<!--6位的单车编号-->
	if(!reg.test(bicycle_sn)){
		alert('请输入6位编码');
		return false;
	}
	loadingFadeIn();
	$.post('',{search_bicycle_sn:bicycle_sn,op:'search_bicycle_sn'},function(data){
		loadingFadeOut();
		console.log(data);
		if(data.data.bicycle_num==0){
			alert('没有查到此车，请重新输入');
			return false;
		}else if(data.data.bicycle_num>1){
			alert('查到多个符合条件的车，请重新输入');
			return false;
		}else{
			$('#bicycle_sn').val(data.data.bicycle_info[0]['bicycle_sn']);
			$("#bicycle_type option[value='"+data.data.bicycle_info[0]['type']+"']").attr("selected", true);
			$("#bicycle_region_id option[value='"+data.data.bicycle_info[0]['region_id']+"']").attr("selected", true);
			$("#bicycle_cooperator_id option[value='"+data.data.bicycle_info[0]['cooperator_id']+"']").attr("selected", true);
		}
	},'json');
}
function go_next_bicycle(){<!--单车信息div那个下一步-->
	var lock_sn=$('#lock_sn').val().trim();
	var lock_name=$('#lock_name').val().trim();
	var lock_type=$('#lock_type').val().trim();
	var lock_cooperator_id=$('#lock_cooperator_id').val().trim();
	var lock_platform=$('#lock_platform').val().trim();
	var lock_factory=$('#lock_factory').val().trim();
	var lock_batch_number=$('#batch_number').val().trim();
	
	var bicycle_sn=$('#bicycle_sn').val().trim();
	var bicycle_type=$('#bicycle_type').val().trim();
	var bicycle_region_id=$('#bicycle_region_id').val().trim();
	var bicycle_cooperator_id=$('#bicycle_cooperator_id').val().trim();
	
	if(!lock_sn){
		alert('锁编号不能空');
		return false;
	}
	if(!bicycle_sn){
		alert('单车编号不能空');
		return false;
	}
	var reg = /^\d{6}$/;<!--大佬说是6位数字-->
	if(!reg.test(bicycle_sn)){
		alert("单车编号请输入6位数字!");
		return false;
	}
	var r=confirm("信息核对无误吗？");
	if (r==true)
	{
		loadingFadeIn();
		var post_data={
			lock_sn:lock_sn,
			lock_name:lock_name,
			lock_type:lock_type,
			lock_cooperator_id:lock_cooperator_id,
			lock_platform:lock_platform,
			lock_factory:lock_factory,
			lock_batch_number:lock_batch_number,
			bicycle_sn:bicycle_sn,
			bicycle_type:bicycle_type,
			bicycle_region_id:bicycle_region_id,
			bicycle_cooperator_id:bicycle_cooperator_id,
			op:'exchange'
		};
		$.post('',post_data,function(data){
			//console.log(data);
			loadingFadeOut();
            if(data.data.state==-2){
				alert(data.data.info);
				return false;
            }else if(data.data.state==-3){
                alert(data.data.info);
                return false;
			}else {
                show_step(3);
                $('#show_bicycle_sn').val(data.data.bicycle_sn);
                $('#show_new_lock_sn').val(data.data.new_lock_sn);
                $('#show_old_lock_sn').val(data.data.old_lock_sn);
            }
		},'json');
	}
	
}
function show_step(step){<!--步骤样式控制-->
	$('.info_fieldset').css('display','none');
	$('.li_bread').hide();
	for(var i=1;i<=step;i++){
		$('.li_b_'+i).show();
	}
	switch(step){
		case 1:
			$('#lock_info').css('display','block');
			break;
		case 2:
			$('#bicycle_info').css('display','block');
			break;
		case 3:
			$('#show_info').css('display','block');
			break;
		default :
	}
}
</script>
<?php
	}else if($type==='bt'){//蓝牙换锁
?>
<style>
#breadcrumbs-one{
background: none;
border-radius: 5px;
overflow: hidden;
width: 290px;
margin: 0;
padding: 0;
list-style: none;
}
#breadcrumbs-one li{
  float: left;
}
#breadcrumbs-one a{
  padding: .7em 1em .7em 2em;
  float: left;
  text-decoration: none;
  color: #444;
  position: relative;
  text-shadow: 0 1px 0 rgba(255,255,255,.5);
  background-color: #ddd;
  background-image: linear-gradient(to right, #f5f5f5, #ddd);
}
#breadcrumbs-one li:first-child a{
  padding-left: 1em;
  border-radius: 5px 0 0 5px;
}
#breadcrumbs-one a::after,
#breadcrumbs-one a::before{
  content: "";
  position: absolute;
  top: 50%;
  margin-top: -1.5em;
  border-top: 1.5em solid transparent;
  border-bottom: 1.5em solid transparent;
  border-left: 1em solid;
  right: -1em;
}
#breadcrumbs-one a::after{
  z-index: 2;
  border-left-color: #ddd;
}
#breadcrumbs-one a::before{
  border-left-color: #ccc;
  right: -1.1em;
  z-index: 1;
}
</style>
<section class="content">
	<ul id="breadcrumbs-one">
		<li class='li_bread li_b_1'><a href="javascript:void(0);">1.单车信息</a></li>
		<li class='li_bread li_b_2'><a href="javascript:void(0);">2.完成</a></li>
	</ul>

	<div class="row">
		<div class="col-xs-12">
			<div class="nav-tabs-custom">
				<div class="tab-content">
					<div class="tab-pane active">
						<form class="form-horizontal info_fieldset" id='bicycle_info'><!--info_fieldset这个class没样式的，只是用来控制隐藏/显示而已-->
							<div >
								<form>
									<div class="dataTables_length fa-border" style="margin: 10px 0; padding: 10px">
										单车编号：<input type="text" name="search_bicycle_sn"  id="search_bicycle_sn" class="input-sm" style="border: 1px solid #a9a9a9;" placeholder='6位编码' maxlength=6/>
										<div class="pull-right">
											<button type='button' class="btn btn-primary btn-sm" onclick='search_bycicle()'><i class="fa fa-search"></i>&nbsp;搜索</button>
										</div>
									</div>
								</form>
								<div class="row">
									<div class="form-group">
										<label class="col-sm-2 control-label">单车编号</label>
										<div class="col-sm-5">
											<input type="text" name="bicycle_sn" id="bicycle_sn" value="" class="form-control" placeholder='6位编码' maxlength=6/>

										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">单车类型</label>
										<div class="col-sm-5">
											<select name="bicycle_type" id="bicycle_type" class="form-control">
												<?php foreach($types as $k => $v) { ?>
												<option value="<?php echo $k; ?>"><?php echo $v; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">所在区域</label>
										<div class="col-sm-5">
											<select name="bicycle_region_id" id="bicycle_region_id" class="form-control">
												<?php foreach($regions as $v) { ?>
												<option value="<?php echo $v['region_id']; ?>" ><?php echo $v['region_name']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">车锁编号</label>
										<div class="col-sm-5">
											<input type="text" name="bicycle_lock_sn" id="bicycle_lock_sn"  class="form-control" disabled=true/>
										</div>
									</div>
									<div class="form-group">
										<label class="col-sm-2 control-label">合伙人</label>
										<div class="col-sm-5">
											<select name="bicycle_cooperator_id" id="bicycle_cooperator_id" class="form-control">
												<option value="0">平台</option>
												<?php foreach($cooperators as $v) { ?>
												<option value="<?php echo $v['cooperator_id']; ?>"><?php echo $v['cooperator_name']; ?></option>
												<?php } ?>
											</select>
										</div>
									</div>
									<div class="form-group">
										<div class="col-sm-7">
											<div class="pull-right">
												<button type='button' class="btn btn-sm btn-success margin-r-5" onclick='go_next_bicycle()'>下一步</button>
												<a href="<?php echo $return_action; ?>"  class="btn btn-default btn-sm">返回</a>
											</div>
										</div>
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

<script type="text/javascript">
    $(document).ready(function(){
        show_step(1);
    });
    function search_bycicle(){<!--搜索单车-->
    var bicycle_sn=$('#search_bicycle_sn').val().trim();
    if(!bicycle_sn){
    return false;
    }
    var reg = /^\d{6}$/;<!--6位的单车编号-->
    if(!reg.test(bicycle_sn)){
        alert('请输入6位编码');
        return false;
    }
    loadingFadeIn();
    $.post('',{search_bicycle_sn:bicycle_sn,op:'search_bicycle_sn'},function(data){
        loadingFadeOut();
        console.log(data);
        if(data.data.bicycle_num==0){
            alert('没有查到此车，请重新输入');
            return false;
        }else if(data.data.bicycle_num>1){
            alert('查到多个符合条件的车，请重新输入');
            return false;
        }else{
            $('#bicycle_sn').val(data.data.bicycle_info[0]['bicycle_sn']);
            if(data.data.bicycle_info[0]['lock_sn']){
                $('#bicycle_lock_sn').val(data.data.bicycle_info[0]['lock_sn']);
			}else {
                $('#bicycle_lock_sn').val('此车无锁');
            }
            $("#bicycle_type option[value='"+data.data.bicycle_info[0]['type']+"']").attr("selected", true);
            $("#bicycle_region_id option[value='"+data.data.bicycle_info[0]['region_id']+"']").attr("selected", true);
            $("#bicycle_cooperator_id option[value='"+data.data.bicycle_info[0]['cooperator_id']+"']").attr("selected", true);
        }
    },'json');
    }
    function go_next_bicycle(){<!--单车信息div那个下一步-->


    var bicycle_sn=$('#bicycle_sn').val().trim();
    var bicycle_type=$('#bicycle_type').val().trim();
    var bicycle_region_id=$('#bicycle_region_id').val().trim();
    var bicycle_cooperator_id=$('#bicycle_cooperator_id').val().trim();


    if(!bicycle_sn){
        alert('单车编号不能空');
        return false;
    }
    var reg = /^\d{6}$/;<!--大佬说是6位数字-->
    if(!reg.test(bicycle_sn)){
        alert("单车编号请输入6位数字!");
        return false;
    }
    var r=confirm("信息核对无误吗？");
    if (r==true)
    {
        loadingFadeIn();
        var post_data={
            bicycle_sn:bicycle_sn,
            bicycle_type:bicycle_type,
            bicycle_region_id:bicycle_region_id,
            bicycle_cooperator_id:bicycle_cooperator_id,
            op:'exchange'
        };
        $.post('',post_data,function(data){
            console.log(data);
            loadingFadeOut();
            show_step(2);
            alert('操作完成，请进入APP完成剩余换锁流程');
            window.location.href='<?php echo $return_action;?>';
        },'json');
    }

    }
    function show_step(step){<!--步骤样式控制-->

    $('.li_bread').hide();
    for(var i=1;i<=step;i++){
    $('.li_b_'+i).show();
    }
    }
</script>

<?php
	}
?>
<?php echo $footer;?>
