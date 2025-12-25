<div class="card">
    <div class="card-header">
        <h5><?php echo e(empty($akun) ? 'Tambah' : 'Ubah'); ?> Akun</h5>
    </div>
    <div class="card-body">
        <form action="<?php echo e(url('keuangan/akun' . (empty($akun) ? '' : ('/' . $akun->id)))); ?>" method="post" id="form_info">
            <?php echo csrf_field(); ?>
            <?php if(!empty($akun)): ?>
                <?php echo method_field('put'); ?>
            <?php endif; ?>
            <input type="hidden" name="kode" value="<?php echo e($kode); ?>" />
            <input type="hidden" name="parent_kode" value="<?php echo e($parent_kode); ?>" />
            <input type="hidden" name="parent_id" value="<?php echo e($parent_id); ?>" />
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="kode_tampil">Kode Akun</label>
                        <input type="text" class="form-control" name="kode_tampil" id="kode_tampil" value="<?php echo e($akun->kode_tampil ?? ''); ?>" />
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="form-group">
                        <label for="tipe">Tipe Akun</label>
                        <select type="text" class="form-control select2" name="tipe" id="tipe">
                            <?php $__currentLoopData = $tipes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tipe): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option <?php echo e($tipe == ($akun->tipe ?? '') ? 'selected' : ''); ?>><?php echo e($tipe); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="nama">Nama Akun</label>
                <input type="text" class="form-control" name="nama" id="nama" value="<?php echo e($akun->nama ?? ''); ?>" />
            </div>
            <div class="d-flex flex-row justify-content-between" style="gap: 10px;">
                <button class="btn btn-primary" type="submit">Simpan</button>
                <button class="btn btn-secondary" type="button" onclick="discard()">Batal</button>
                <div class="d-flex flex-row justify-content-end" style="flex-grow: 1;gap: 10px">
                    <?php if(!empty($akun)): ?>
                        <button class="btn btn-info" type="button" onclick="info('', '<?php echo e($akun->kode); ?>')">Tambah Sub</button>
                        <button class="btn btn-danger" type="button" onclick="delete_data(<?php echo e($akun->id); ?>)">Delete</button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    $('.select2').select2();
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
            success: () => discard(),
        }).fail((xhr) => {
            console.log(xhr.responseText);
        });
    });
</script>
<?php /**PATH /var/www/html/resources/views/keuangan/akun/_info.blade.php ENDPATH**/ ?>