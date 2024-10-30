<?php if (! $__env->hasRenderedOnce('ea7a0b49-b1a9-4773-9017-406bb65585b5')): $__env->markAsRenderedOnce('ea7a0b49-b1a9-4773-9017-406bb65585b5');
$__env->startPush(config('pagebuilder.site_style_var')); ?>
    <link rel="stylesheet" href="<?php echo e(asset('public/theme/edulia/packages/carousel/owl.carousel.min.css')); ?>">
<?php $__env->stopPush(); endif; ?>

<?php if (isset($component)) { $__componentOriginal4dea201d382b663324e524ef479406ce = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal4dea201d382b663324e524ef479406ce = $attributes; } ?>
<?php $component = App\View\Components\HomePageSlider::resolve(['count' => pagesetting('home_slider_count')] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('home-page-slider'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(App\View\Components\HomePageSlider::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>  <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal4dea201d382b663324e524ef479406ce)): ?>
<?php $attributes = $__attributesOriginal4dea201d382b663324e524ef479406ce; ?>
<?php unset($__attributesOriginal4dea201d382b663324e524ef479406ce); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal4dea201d382b663324e524ef479406ce)): ?>
<?php $component = $__componentOriginal4dea201d382b663324e524ef479406ce; ?>
<?php unset($__componentOriginal4dea201d382b663324e524ef479406ce); ?>
<?php endif; ?>

<?php if (! $__env->hasRenderedOnce('d24a7ecf-73dc-425e-8f16-a0f17df7aecb')): $__env->markAsRenderedOnce('d24a7ecf-73dc-425e-8f16-a0f17df7aecb');
$__env->startPush(config('pagebuilder.site_script_var')); ?>
    <script src="<?php echo e(asset('public/theme/edulia/packages/carousel/owl.carousel.min.js')); ?>"></script>
    <script>
        $('.hero_area_slider').owlCarousel({
            nav: true,
            navText: ['<i class="far fa-angle-left"></i>', '<i class="far fa-angle-right"></i>'],
            dots: false,
            items: 1,
            loop: true,
            margin: 0,
            autoplay: true,
            autoplayTimeout: 5000,
            autoplayHoverPause: true,
        });
    </script>
<?php $__env->stopPush(); endif; ?>
<?php /**PATH C:\wamp64\www\projet1\resources\views/themes/edulia/pagebuilder/home-slider/view.blade.php ENDPATH**/ ?>