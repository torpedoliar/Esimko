<div class="card">
    <div class="card-header">
        <h5><?php echo e(empty($jurnal) ? 'Tambah' : 'Ubah'); ?> Akun</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-lg-5">
                <form action="<?php echo e(url('keuangan/jurnal' . (empty($jurnal) ? '' : ('/' . $jurnal->id)))); ?>" method="post" id="form_info">
                    <?php echo csrf_field(); ?>
                    <?php if(!empty($jurnal)): ?>
                        <?php echo method_field('put'); ?>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="no_jurnal">No.Jurnal</label>
                                <input type="text" class="form-control" name="no_jurnal" id="no_jurnal" value="<?php echo e($jurnal->no_jurnal ?? ''); ?>" />
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="tanggal">Tanggal</label>
                                <input type="text" class="form-control datepicker" name="tanggal" id="tanggal" value="<?php echo e(format_date($jurnal->tanggal ?? '')); ?>" />
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <textarea type="text" class="form-control" name="keterangan" id="keterangan" rows="4"><?php echo $jurnal->keterangan ?? ''; ?></textarea>
                    </div>
                    <div class="d-flex flex-row justify-content-between" style="gap: 10px;">
                        <button class="btn btn-primary" type="submit"><?php echo e(empty($jurnal) ? 'Lanjutkan' : 'Simpan'); ?></button>
                        <button class="btn btn-secondary" type="button" onclick="discard()">Batal</button>
                        <div class="d-flex flex-row justify-content-end" style="flex-grow: 1;gap: 10px">
                            <?php if(!empty($jurnal)): ?>
                                <button class="btn btn-danger" type="button" onclick="delete_data(<?php echo e($jurnal->id); ?>)">Delete</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-lg-7" id="jurnal_detail"></div>
        </div>
    </div>
</div>

<script>
    $('.datepicker').datepicker({
        autoclose: true,
        format: "dd-mm-yyyy",
    });
    $form_info = $('#form_info');
    $form_info.submit((e) => {
        e.preventDefault();
        let url = $form_info.attr('action');
        let data = new FormData($form_info.get(0));
        $.ajax({
            url, data,
            type: 'post',
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: (result) => {
                <?php if(empty($jurnal)): ?>
                    info(result.id);
                <?php else: ?>
                    discard();
                <?php endif; ?>
            },
        }).fail((xhr) => {
            console.log(xhr.responseText);
        });
    });

    <?php if(!empty($jurnal)): ?>
    $jurnal_detail = $('#jurnal_detail');
    jurnal_detail = () => {
        $.get("<?php echo e(url('keuangan/jurnal/' . $jurnal->id . '/detail')); ?>", (result) => {
            $jurnal_detail.html(result);
        }).fail((xhr) => {
            $jurnal_detail.html(xhr.responseText);
        });
    }
    jurnal_detail();
    <?php endif; ?>
</script>
<?php /**PATH /var/www/html/resources/views/keuangan/jurnal/_info.blade.php ENDPATH**/ ?>