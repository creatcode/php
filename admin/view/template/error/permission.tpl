<?php echo $header; ?>
<!-- Content Header (Page header) -->
<section class="content-header clearfix">
  <h1 class="pull-left">
    <span>无此权限！</span>
    <a href="javascript:;" onclick="collect('<?php echo $menu_id ?>',this)"><i class="<?php echo $menu_collect_status == 1? 'fa fa-star no-margin text-yellow' : 'fa fa-star-o text-gray'; ?>"></i></a>
  </h1>
  <?php echo $statistics_in_page_header;?>
</section>
<!-- Main content -->
<section class="content">
  <div class="row">
    <div class="col-xs-12">
      <div class="box box-primary">
        <div class="box-header with-border"></div>
        <div class="box-body">
          <p class="text-center">您没有权限访问此页面，请联系系统管理员。</p>
        </div>
      </div>
    </div>
  </div>
</section>
<?php echo $footer;?>