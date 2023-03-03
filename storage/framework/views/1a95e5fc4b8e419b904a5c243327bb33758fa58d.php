<?php echo $__env->make('backend::header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<div class="container-fluid px-2 px-md-4">
    <div class="page-header min-height-30 border-radius-xl mt-4" >
    </div>
    <div class="card card-body mx-3 mx-md-4 mt-n6">
        <div class="row gx-4 mb-2">
            <div class="col-auto">
                <div class="avatar avatar-xl position-relative">
                    <img src="<?php echo e($data[0]['featured_image']); ?>" alt="profile_image" class="w-100 border-radius-lg shadow-sm">
                </div>
            </div>
            <div class="col-auto my-auto">
                <div class="h-100">
                    <h5 class="mb-1">
                        <?php echo e($data[0]['name']); ?>

                    </h5>

                </div>
            </div>

        </div>
        <div class="row">
            <div class="row">

                <div class="col-12 col-xl-12">
                    <div class="card card-plain h-100">
                        <div class="card-header pb-0 p-3">
                            <div class="row">
                                <div class="col-md-8 d-flex align-items-center">
                                    <h6 class="mb-0">Description</h6>
                                </div>

                            </div>
                        </div>
                        <div class="card-body p-3">
                            <p class="text-sm">
                                <?php echo e($data[0]['description']); ?>

                            </p>
                            <hr class="horizontal gray-light my-4">
                            <ul class="list-group">
                                <li class="list-group-item border-0 ps-0 pt-0 text-sm"><strong class="text-dark">Product Type:</strong> &nbsp; <?php echo e($data[0]['category']); ?></li>
                                <li class="list-group-item border-0 ps-0 text-sm"><strong class="text-dark">Product Made IN:</strong> &nbsp; <?php echo e($data[0]['country']); ?></li>

                            </ul>
                        </div>
                    </div>
                </div>
<?php if(!empty($data[0]['allimage']))
{ ?>
                <div class="col-12 mt-4">
                    <div class="mb-5 ps-3">
                        <h6 class="mb-1">Images</h6>
                        <p class="text-sm"></p>
                    </div>
                    <div class="row">
                        <?php

                        foreach($data[0]['allimage'] as $img)
                        {
                            ?>
                        <div class="col-xl-3 col-md-6 mb-xl-0 mb-4">
                            <div class="card card-blog card-plain">
                                <div class="card-header p-0 mt-n4 mx-3">
                                    <a class="d-block shadow-xl border-radius-xl">
                                        <img src="<?php echo e($img['image']); ?>" alt="img-blur-shadow" class="img-fluid shadow border-radius-lg">
                                    </a>
                                </div>

                            </div>
                        </div>
<?php } ?>


                    </div>
                </div>
                <?php }?>

                <?php if(!empty($data[0]['allvariations']))
                { ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card my-4">

                            <div class="card-body px-0 pb-2">
                                <div class="table-responsive p-0">
                                    <div class="mb-5 ps-3">
                                        <h6 class="mb-1">Variations</h6>
                                        <p class="text-sm"></p>
                                    </div>
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                        <tr>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Photo</th>
                                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Size</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Material</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Color</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">SKU</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">USD Wholesale</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">USD Retail</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Inventory</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Weight</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Length</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Width</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Height</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Tariff Code</th>

                                        </tr>
                                        </thead>
                                        <tbody>
                                            <?php

                                        foreach($data[0]['allvariations'] as $variation)
                                        {
                                            ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div>
                                                        <img src="<?php echo e($variation['preview_images']); ?>" class="avatar avatar-sm me-3 border-radius-lg" alt="user6">
                                                    </div>

                                                </div>
                                            </td>
                                            <td>
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['value1']); ?></p>

                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['value2']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['value3']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['sku']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['usd_wholesale_price']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['usd_retail_price']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['inventory']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['weight']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['length']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['width']); ?><?php echo e($variation['width_unit']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['height']); ?><?php echo e($variation['height_unit']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($variation['tariff_code']); ?></p>
                                            </td>
                                        </tr>
                                        <?php }?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
<?php }?>


<?php if(!empty($data[0]['pre_packs']))
{ ?>
                <div class="row">
                    <div class="col-12">
                        <div class="card my-4">

                            <div class="card-body px-0 pb-2">
                                <div class="table-responsive p-0">
                                    <div class="mb-5 ps-3">
                                        <h6 class="mb-1">Pre Pack</h6>
                                        <p class="text-sm"></p>
                                    </div>
                                    <table class="table align-items-center mb-0">
                                        <thead>
                                        <tr>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Style</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pack Name</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Size Ratio</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Size Range</th>
                                            <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Pack Price</th>


                                        </tr>
                                        </thead>
                                        <tbody>
                                            <?php

                                        foreach($data[0]['pre_packs'] as $prerPack)
                                        {
                                            ?>
                                        <tr>

                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($prerPack['style']); ?></p>

                                            </td>
                                            <td class="align-middle text-center text-sm">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($prerPack['pack_name']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($prerPack['size_ratio']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($prerPack['size_range_value']); ?></p>
                                            </td>
                                            <td class="align-middle text-center">
                                                <p class="text-xs font-weight-bold mb-0"><?php echo e($prerPack['packs_price']); ?></p>
                                            </td>

                                        </tr>
                                        <?php }?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php }?>


            </div>
        </div>
    </div>
</div>

<?php echo $__env->make('backend::footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php /**PATH /home/bazarcenter/public_html/staging.bazarcenter.ca/Modules/Backend/Resources/views/ProductDetail.blade.php ENDPATH**/ ?>