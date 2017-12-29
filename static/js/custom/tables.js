/*
 * 	Additional function for tables.html
 *	Written by ThemePixels	
 *	http://themepixels.com/
 *
 *	Copyright (c) 2012 ThemePixels (http://themepixels.com)
 *	
 *	Built for Amanda Premium Responsive Admin Template
 *  http://themeforest.net/category/site-templates/admin-templates
 */

$(function(){
		///// 删除表里的选择的行 /////
	$('.deletebutton').click(function(){
		var tb = $(this).attr('title');							// get target id of table								   
		var sel = false;												//initialize to false as no selected row
		var ch = $('#'+tb).find('tbody input[type=checkbox]');		//get each checkbox in a table
		
		//check if there is/are selected row in table

		ch.each(function(){
			if($(this).is(':checked')) {
				sel = true;
				if(!confirm('是否取消该订单?')){
					return;
				}												//set to true if there is/are selected row
				$(this).parents('tr').fadeOut(function(){
					$(this).remove();								//remove row when animation is finished
				});
			}
		});
		
		if(!sel) alert('请选择要操作的订单！');								//alert to no data selected
	});
});