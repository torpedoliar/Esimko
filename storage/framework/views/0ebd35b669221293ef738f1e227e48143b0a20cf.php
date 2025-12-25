<div class="card shadow-sm">
    <div class="card-header"><h5>Detail Jurnal</h5></div>
    <div class="card-body">
        <form action="<?php echo e(url('keuangan/jurnal/' . $jurnal->id . '/detail')); ?>" method="post" class="row" id="form_detail">
            <?php echo csrf_field(); ?>
            <div class="col-lg-6">
                <div class="form-group">
                    <label for="akun_id">Akun</label>
                    <select type="text" class="form-control select2" name="akun_id" id="akun_id">
                        <?php $__currentLoopData = $akun; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item->id); ?>"><?php echo e($item->kode_tampil . ' - ' . $item->nama); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group text-right">
                    <label for="debit">Debit</label>
                    <input type="text" class="form-control autonumeric" name="debit" id="debit" />
                </div>
            </div>
            <div class="col-lg-3">
                <div class="form-group text-right">
                    <label for="kredit">Kredit</label>
                    <input type="text" class="form-control autonumeric" name="kredit" id="kredit" />
                </div>
            </div>
            <div class="col-12 text-right">
                <button class="btn btn-info" type="submit">Simpan Detail</button>
            </div>
        </form>

        <table class="table mt-3">
            <thead>
            <tr>
                <th>Akun</th>
                <th class="text-right">Debit</th>
                <th class="text-right">Kredit</th>
                <th width="30px"></th>
            </tr>
            </thead>
            <tbody>
            <?php $__currentLoopData = $jurnal->details; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <tr>
                    <td><?php echo e($detail->akun->kode_tampil . ' - ' . $detail->akun->nama); ?></td>
                    <td class="text-right"><?php echo e(format_number($detail->debit)); ?></td>
                    <td class="text-right"><?php echo e(format_number($detail->kredit)); ?></td>
                    <td class="py-0 align-middle">
                        <a href="javascript:void(0)" onclick="delete_detail(<?php echo e($detail->id); ?>)" class="text-dark"><i class="bx bx-trash h3 m-0"></i></a>
                    </td>
                </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>
</div>


<script>
    $('.select2').select2();
    $('.autonumeric')
        .attr('data-a-sep', '.')
        .attr('data-a-dec',',')
        .autoNumeric({
            mDec: '0',
            vMax:'9999999999999999999999999',
            vMin: '-99999999999999999'
        });

    $form_detail = $('#form_detail');
    $form_detail.submit((e) => {
        e.preventDefault();
        let url = $form_detail.attr('action');
        let data = new FormData($form_detail.get(0));
        $.ajax({
            url, data,
            type: 'post',
            cache: false,
            processData: false,
            contentType: false,
            dataType: 'JSON',
            success: () => jurnal_detail(),
        }).fail((xhr) => {
            console.log(xhr.responseText);
        });
    });

    delete_detail = (id) => {
        Swal.fire({
            title: 'Hapus Detail ?',
            showCancelButton: true,
            confirmButtonText: 'Hapus',
            denyButtonText: 'Batal',
            confirmButtonColor: '#F46A6A',
            denyButtonColor: '#bdbdbd'
        }).then((result) => {
            if (result.value) {
                $.post("<?php echo e(url('keuangan/jurnal/' . $jurnal->id . '/detail')); ?>/" + id, {_method: 'delete', _token}, (result) => {
                    console.log(result);
                    jurnal_detail();
                }).fail((xhr) => {
                    console.log(xhr.responseText);
                });
            }
        });
    }
</script>
<?php /**PATH /var/www/html/resources/views/keuangan/jurnal/detail/_index.blade.php ENDPATH**/ ?>