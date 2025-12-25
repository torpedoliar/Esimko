<html lang="en">
<head>
    <style media="all">
        body {
            font-family: "Courier New", Courier, monospace!important;
        }
        #table_main {
            margin-left: 0;
            font-size: 7pt;
        }
        /*th {*/
        /*    border-left: 1px solid #eaeaea;*/
        /*    border-right: 1px solid #eaeaea;*/
        /*}*/

        th.bg-gray {
            background-color: #fff;
            line-height: 10pt;
        }
        td {
            line-height: 12pt;
            vertical-align: middle;
        }
    </style>
</head>
<body>
<table id="table_main" cellpadding="0" style="margin-top: 1.9cm;">
    <?php ($index = 0); ?>
    <?php for($i = 0; $i < 25; $i++): ?>
        <?php if($i == 11): ?>
            <tr><th colspan="7" style="padding-top: 0.2cm">&nbsp;</th></tr>
            <tr><th colspan="7" style="padding-top: 0.2cm">&nbsp;</th></tr>
        <?php endif; ?>
        <tr>
            <?php ($item = $data_cetak[$i] ?? []); ?>
            <?php if(!empty($item)): ?>
                <th style="width: 0.6cm;text-align: left;padding-left: 1cm;" class="bg-gray"><?php echo e($item['nomor']); ?></th>
                <th style="width: 2cm;text-align: right;padding-left: 0.1cm"><?php echo e(date('d-m-y', strtotime($item['tanggal']))); ?></th>
                <th style="width: 3.5cm;"><?php echo e($item['sandi']); ?></th>
                <th style="width: 2cm;text-align: right;"><?php echo e(format_number($item['debit'] * -1)); ?></th>
                <th style="width: 2cm;text-align: right;">&nbsp; <?php echo e(format_number($item['kredit'])); ?></th>
                <th style="width: 2.1cm;text-align: right;">&nbsp; <?php echo e($index == 0 ? format_number($item['saldo']) : format_number($item['saldo'])); ?></th>
                <th style="width: 1.9cm;"><?php echo e($item['operator']); ?></th>

                <?php ($index++); ?>
            <?php else: ?>
                <th colspan="7" style="padding-top: 0.1cm">&nbsp;</th>
            <?php endif; ?>
        </tr
    <?php endfor; ?>
</table>

</body>
</html>
<?php /**PATH /var/www/html/resources/views/simpanan/buku_simpanan/cetak.blade.php ENDPATH**/ ?>