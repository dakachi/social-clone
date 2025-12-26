
<?php $__env->startSection('content'); ?>
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-200" style="max-width: 900px; margin: 0 auto;">
        <div class="container mx-auto px-8 py-10">
            
            <?php echo $__env->make("steps/steps", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            <!-- Step Content -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-8 md:p-10">
                <?php echo $__env->make("steps/step1", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make("steps/step2", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make("steps/step3", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <?php echo $__env->make("steps/step4", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
                
            <?php echo $__env->make("steps/navigation", array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /var/www/social-bit1/installer/views/install.blade.php ENDPATH**/ ?>