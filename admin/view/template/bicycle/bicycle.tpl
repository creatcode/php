<?php echo $header; ?>
<!-- content -->
<div class="content-wrapper">
    <section class="content-header">
        <h1>单车列表</h1>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header">
                        <h3 class="box-title">单车列表</h3>
                        <button type="button" class="btn btn-primary pull-right">添加单车信息</button>
                    </div>
                    <div class="box-body">
                        <?php if (isset($success)) { ?>
                        <div class="alert bg-light-blue"><i class="fa fa-check-circle"></i>&nbsp;<?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                        </div>
                        <?php } ?>
                        <div class="dataTables_wrapper form-inline dt-bootstrap">
                            <div class="row">
                                <div class="col-sm-6"></div>
                                <div class="col-sm-6"></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-12">
                                    <table class="table table-bordered table-hover dataTable" role="grid">
                                        <thead>
                                        <tr>
                                            <?php foreach ($data_columns as $column) { ?>
                                            <th><?php echo $column['text']; ?></th>
                                            <?php } ?>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($data_rows as $data) { ?>
                                        <tr>
                                            <td><?php echo $data['bicycle_sn']?></td>
                                            <td><?php echo $data['type']?></td>
                                            <td><?php echo $data['lock_sn']?></td>
                                            <td><?php echo $data['cur_lat']?></td>
                                            <td><?php echo $data['cur_lng']?></td>
                                            <td><?php echo $data['is_using']?></td>
                                        </tr>
                                        <?php } ?>
                                        </tbody>
                                        <tfoot>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="row"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<!-- content end -->
<?php echo $footer;?>
