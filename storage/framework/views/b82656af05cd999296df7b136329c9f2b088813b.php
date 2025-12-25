<!DOCTYPE html>
<html lang="en-US" ng-app="">
<head>
    <title><?php echo $__env->yieldContent('title'); ?></title>
    <meta charset="utf-8">
    <meta content="IE=edge" http-equiv="x-ua-compatible">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="yes" name="apple-mobile-web-app-capable">
    <meta content="yes" name="apple-touch-fullscreen">
    <link rel="stylesheet" type="text/css" href="<?php echo e(asset('assets/css/report.css')); ?>">
    <link href="<?php echo e(asset('assets/css/bootstrap.min.css')); ?> " id="bootstrap-style" rel="stylesheet" type="text/css" />
    <style>
        .table-bordered tr td, .table-bordered tr th {
            border-color: #000!important;
        }
    </style>
    <?php echo $__env->yieldContent('css'); ?>
</head>
<body>
<?php echo $__env->yieldContent('content'); ?>
<script src="<?php echo e(asset('assets/libs/jquery/jquery.min.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(asset('assets/js/jquery.qrcode.js')); ?>"></script>
<script type="text/javascript" src="<?php echo e(asset('assets/js/qrcode.js')); ?>"></script>
<?php echo $__env->yieldContent('js'); ?>
</body>
</html>
<?php /**PATH /var/www/html/resources/views/layouts/report2.blade.php ENDPATH**/ ?>