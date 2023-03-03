<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo e(asset('public/assets/img/apple-icon.png')); ?>">
    <link rel="icon" type="image/png" href="<?php echo e(asset('public/assets/img/favicon.png')); ?>">
    <title>
        Bazar Admin
    </title>
    <!--     Fonts and icons     -->
    <link rel="stylesheet" type="text/css"
          href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900|Roboto+Slab:400,700"/>
    <!-- Nucleo Icons -->
    <link href="<?php echo e(asset('public/assets/css/nucleo-icons.css')); ?>" rel="stylesheet"/>
    <link href="<?php echo e(asset('public/assets/css/nucleo-svg.css')); ?>" rel="stylesheet"/>
    <!-- Font Awesome Icons -->
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.10.3/xlsx.core.min.js"></script>
    <script src="https://unpkg.com/ag-grid-enterprise@19.0.0/dist/ag-grid-enterprise.min.js"></script>
    <!-- Material Icons -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet">
    <!-- CSS Files -->
    <link id="pagestyle" href="<?php echo e(asset('public/assets/css/material-dashboard.css?v=3.0.4')); ?>" rel="stylesheet"/>
    <link rel="stylesheet" href="https://unpkg.com/ag-grid/dist/styles/ag-grid.css">
    <link rel="stylesheet" href="https://unpkg.com/ag-grid/dist/styles/ag-theme-balham.css">
    <style>
        #myGrid {
            height: 600px;
            width: 95%;
            text-align: center;
            margin: 0 auto;
        }
    </style>
</head>

<body class="g-sidenav-show  bg-gray-200">
<aside
    class="sidenav navbar navbar-vertical navbar-expand-xs border-0 border-radius-xl my-3 fixed-start ms-3   bg-gradient-dark"
    id="sidenav-main">
    <div class="sidenav-header">
        <i class="fas fa-times p-3 cursor-pointer text-white opacity-5 position-absolute end-0 top-0 d-none d-xl-none"
           aria-hidden="true" id="iconSidenav"></i>
        <a class="navbar-brand m-0" href="#url">
            <img src="<?php echo e(asset('public/assets/img/logo-ct.png')); ?>" class="navbar-brand-img h-100" alt="main_logo">
            <span class="ms-1 font-weight-bold text-white">Bazar Admin</span>
        </a>
    </div>
    <hr class="horizontal light mt-0 mb-2">
    <div class="collapse navbar-collapse  w-auto " id="sidenav-collapse-main">
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link text-white " href="dashboard">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">dashboard</i>
                    </div>
                    <span class="nav-link-text ms-1">Dashboard</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link text-white" href="vendorlist">
                    <div class="text-white text-center me-2 d-flex align-items-center justify-content-center">
                        <i class="material-icons opacity-10">table_view</i>
                    </div>
                    <span class="nav-link-text ms-1">Brands</span>
                </a>
            </li>


        </ul>
    </div>
    <div class="sidenav-footer position-absolute w-100 bottom-0 ">

    </div>
</aside>
<main class="main-content position-relative max-height-vh-100 h-100 border-radius-lg ">
    <!-- Navbar -->
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur"
         data-scroll="true">
        <div class="container-fluid py-1 px-3">

            <div class="collapse navbar-collapse mt-sm-0 mt-2 me-md-0 me-sm-4" id="navbar">
                <div class="ms-md-auto pe-md-3 d-flex align-items-center">
                    <div class="input-group input-group-outline">

                    </div>
                </div>
                <ul class="navbar-nav  justify-content-end">
                    <li class="nav-item d-flex align-items-center">
                        <a class="btn btn-outline-primary btn-sm mb-0 me-3" href="logout">Log Out</a>
                    </li>
                    <li class="nav-item d-flex align-items-center">
                        <a href="changepassword" class="nav-link text-body font-weight-bold px-0">
                            <i class="fa fa-user me-sm-1"></i>
                            <span class="d-sm-inline d-none">Change Password</span>
                        </a>
                    </li>


                </ul>
            </div>
        </div>
    </nav>
<?php /**PATH /home/bazarcenter/public_html/staging.bazarcenter.ca/Modules/Backend/Resources/views/header.blade.php ENDPATH**/ ?>