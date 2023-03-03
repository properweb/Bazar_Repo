<?php echo $__env->make('backend::header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-dark shadow-dark text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">weekend</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Orders</p>
                        <h4 class="mb-0"><?php echo e($totalOrder); ?></h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">

                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-primary shadow-primary text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">person</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Brands</p>
                        <h4 class="mb-0"><?php echo e($totalBrand); ?></h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">

                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-success shadow-success text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">person</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Retailer</p>
                        <h4 class="mb-0"><?php echo e($totalRetailer); ?></h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">

                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-header p-3 pt-2">
                    <div class="icon icon-lg icon-shape bg-gradient-info shadow-info text-center border-radius-xl mt-n4 position-absolute">
                        <i class="material-icons opacity-10">weekend</i>
                    </div>
                    <div class="text-end pt-1">
                        <p class="text-sm mb-0 text-capitalize">Total Products</p>
                        <h4 class="mb-0"><?php echo e($totalProduct); ?></h4>
                    </div>
                </div>
                <hr class="dark horizontal my-0">
                <div class="card-footer p-3">

                </div>
            </div>
        </div>
    </div>
    <div class="row mt-4">
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
            <div class="card z-index-2 ">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                    <div class="bg-gradient-success shadow-success border-radius-lg py-3 pe-1">
                        <div class="chart">
                            <canvas id="brand-chart" class="chart-canvas" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="mb-0 ">Total Brands of <?php echo e(date('Y')); ?></h6>
                    <p class="text-sm "></p>
                    <hr class="dark horizontal">
                    <div class="d-flex ">
                        <i class="material-icons text-sm my-auto me-1"></i>
                        <p class="mb-0 text-sm"> </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
            <div class="card z-index-2 ">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                    <div class="bg-gradient-success shadow-success border-radius-lg py-3 pe-1">
                        <div class="chart">
                            <canvas id="retailer-chart" class="chart-canvas" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="mb-0 ">Total Retailer of <?php echo e(date('Y')); ?></h6>
                    <p class="text-sm "></p>
                    <hr class="dark horizontal">
                    <div class="d-flex ">
                        <i class="material-icons text-sm my-auto me-1"></i>
                        <p class="mb-0 text-sm"> </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mt-4 mb-4">
            <div class="card z-index-2 ">
                <div class="card-header p-0 position-relative mt-n4 mx-3 z-index-2 bg-transparent">
                    <div class="bg-gradient-success shadow-success border-radius-lg py-3 pe-1">
                        <div class="chart">
                            <canvas id="order-chart" class="chart-canvas" height="170"></canvas>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h6 class="mb-0 ">Total Orders of <?php echo e(date('Y')); ?></h6>
                    <p class="text-sm "></p>
                    <hr class="dark horizontal">
                    <div class="d-flex ">
                        <i class="material-icons text-sm my-auto me-1"></i>
                        <p class="mb-0 text-sm"> </p>
                    </div>
                </div>
            </div>
        </div>
    </div>


</div>

<?php echo $__env->make('backend::footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.5.0/Chart.min.js"></script>
    <script>
        const xValues = ["Jan", "Feb", "March", "Apr", "May","June","July","Aug","Sept","Oct","Nov","Dec"];
        const yValues = [<?php echo e($totalBrandByMonth); ?>];
        const barColors = ["#fff", "#fff","#fff","#fff","#fff","#fff","#fff","#fff","#fff","#fff","#fff","#fff"];
        new Chart("brand-chart", {
        type: "bar",
        data: {
        labels: xValues,
        datasets: [{
        backgroundColor: barColors,
        data: yValues,

    }]
    },
        options: {
        legend: {display: false},
            barRoundness: 1,
            layout: {
                padding: 10
            },
            title: {
        display: false,
        text: "Total Vendor"
    },
            scales: {

                xAxes: [{
                    barThickness: 8,
                    gridLines: {
                        display:false
                    },
                    ticks: {

                        fontColor: 'white'
                    }
                }],
                yAxes: [{
                    gridLines: {
                        display:false
                    },
                    ticks: {
                        stepSize: 1,
                        fontColor: 'white'
                    }
                }]
            }
    }
    });

        const yValues1 = [<?php echo e($totalRetailerByMonth); ?>];
        new Chart("retailer-chart", {
            type: "bar",
            data: {
                labels: xValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: yValues1,

                }]
            },
            options: {
                legend: {display: false},
                layout: {
                    padding: 10
                },
                title: {
                    display: false,
                    text: "Total Vendor"
                },
                scales: {

                    xAxes: [{
                        barThickness: 8,
                        gridLines: {
                            display:false
                        },
                        ticks: {

                            fontColor: 'white'
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display:false
                        },
                        ticks: {
                            stepSize: 1,
                            fontColor: 'white'
                        }
                    }]
                }
            }
        });

        const yValues2 = [<?php echo e($totalOrderByMonth); ?>];
        new Chart("order-chart", {
            type: "bar",
            data: {
                labels: xValues,
                datasets: [{
                    backgroundColor: barColors,
                    data: yValues2,

                }]
            },
            options: {
                legend: {display: false},
                layout: {
                    padding: 10
                },
                title: {
                    display: false,
                    text: "Total Vendor"
                },
                scales: {

                    xAxes: [{
                        barThickness: 8,
                        gridLines: {
                            display:false
                        },
                        ticks: {

                            fontColor: 'white'
                        }
                    }],
                    yAxes: [{
                        gridLines: {
                            display:false
                        },
                        ticks: {
                            stepSize: 1,
                            fontColor: 'white'
                        }
                    }]
                }
            }
        });
</script>

<?php /**PATH /home/bazarcenter/public_html/staging.bazarcenter.ca/Modules/Backend/Resources/views/Dashboard.blade.php ENDPATH**/ ?>