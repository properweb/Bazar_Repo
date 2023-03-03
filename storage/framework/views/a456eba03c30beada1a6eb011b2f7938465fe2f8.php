<?php $__env->startSection('content'); ?>
    <h1>Hello World</h1>

    <p>
        This view is loaded from module: <?php echo config('product.name'); ?>

    </p>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('product::layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/bazarcenter/public_html/staging.bazarcenter.ca/Modules/Product/Resources/views/index.blade.php ENDPATH**/ ?>