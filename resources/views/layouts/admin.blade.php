<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>HOMMSS</title>

    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="author" content="surfside media" />
    <link rel="stylesheet" type="text/css" href="{{ asset('css/animate.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/animation.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/bootstrap-select.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/style.css') }}">
    <link rel="stylesheet" href="{{ asset('font/fonts.css') }}">
    <link rel="stylesheet" href="{{ asset('icon/style.css') }}">
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}">
    <link rel="apple-touch-icon-precomposed" href="{{ asset('images/favicon.ico') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/custom.css') }}">

    <!-- Roboto Font -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <!-- Discord-inspired CSS -->
    <style>
        /* Discord Light Theme Colors with HOMMSS Blue */
        :root {
            --discord-bg: #ffffff;
            --discord-sidebar: #f2f3f5;
            --discord-hover: #e3e5e8;
            --discord-text: #2e3338;
            --discord-muted: #747f8d;
            --discord-accent: #2275fc; /* HOMMSS Blue */
            --discord-green: #3ba55c;
            --discord-divider: #e3e5e8;
            --discord-card: #f8f9fa;
            --discord-shadow: rgba(0, 0, 0, 0.08);

            /* HOMMSS Blue Theme Variables */
            --hommss-blue: #2275fc;
            --hommss-blue-hover: #1a5fd9;
            --hommss-blue-active: #1554c7;
            --hommss-blue-light: #e6f0ff;
            --hommss-blue-dark: #0f4bb5;
        }

        /* Global Styles */
        body.body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--discord-bg);
            color: var(--discord-text);
        }

        /* Sidebar Styling */
        .section-menu-left {
            background-color: var(--discord-sidebar);
            border-right: 1px solid var(--discord-divider);
        }

        .box-logo {
            padding: 12px 16px;
            border-bottom: 1px solid var(--discord-divider);
        }

        .center-heading {
            color: var(--discord-muted);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 24px 16px 8px;
            letter-spacing: 0.02em;
        }

        .menu-list .menu-item a {
            border-radius: 4px;
            margin: 2px 8px;
            padding: 8px 12px;
            transition: background-color 0.15s ease;
        }

        .menu-list .menu-item a:hover {
            background-color: var(--discord-hover);
        }

        .menu-list .menu-item a .icon i {
            color: var(--discord-muted);
        }

        .menu-list .menu-item a .text {
            font-weight: 500;
        }

        .menu-list .menu-item.has-children .sub-menu {
            background-color: transparent;
            padding-left: 12px;
        }

        .menu-list .menu-item.has-children .sub-menu .sub-menu-item a {
            padding: 6px 12px;
            margin-left: 24px;
        }

        /* Header Styling */
        .header-dashboard {
            background-color: var(--discord-bg);
            border-bottom: 1px solid var(--discord-divider);
            padding: 0;
            box-shadow: 0 1px 3px var(--discord-shadow);
        }

        .header-dashboard .wrap {
            padding: 12px 16px;
        }

        .form-search {
            background-color: var(--discord-sidebar);
            border-radius: 4px;
            overflow: hidden;
        }

        .form-search input {
            background-color: var(--discord-sidebar);
            border: none;
            padding: 8px 12px;
            color: var(--discord-text);
        }

        .form-search .button-submit button {
            background: transparent !important;
            border: none !important;
            color: #000 !important;
            padding: 12px 16px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .form-search .button-submit button:hover {
            background: #f8f9fa !important;
            transform: translateY(-1px);
            color: #000 !important;
        }

        /* Force all search icons to be black with no background */
        .form-search .button-submit button i {
            color: #000 !important;
            font-size: 16px;
        }

        .form-search .button-submit button:hover i {
            color: #000 !important;
        }

        /* Content Area */
        .main-content {
            padding: 24px;
            background-color: var(--discord-bg);
        }

        /* Cards and Widgets */
        .wg-chart-default,
        .wg-box {
            background-color: var(--discord-card);
            border-radius: 8px;
            border: 1px solid var(--discord-divider);
            box-shadow: 0 2px 10px var(--discord-shadow);
            padding: 16px;
            margin-bottom: 16px;
        }

        /* Buttons */
        .btn-primary {
            background-color: var(--discord-accent);
            border-color: var(--discord-accent);
        }

        .btn-primary:hover {
            background-color: #4752c4;
            border-color: #4752c4;
        }

        .btn-success {
            background-color: var(--discord-green);
            border-color: var(--discord-green);
        }

        /* Tables */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table th {
            background-color: var(--discord-sidebar);
            color: var(--discord-text);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.03em;
        }

        .table td,
        .table th {
            padding: 12px 16px;
            border-bottom: 1px solid var(--discord-divider);
        }

        /* Footer */
        .bottom-page {
            border-top: 1px solid var(--discord-divider);
            padding: 16px 24px;
            color: var(--discord-muted);
            font-size: 14px;
        }

        /* Discord-style Icon Improvements */
        /* Icon Base Styling */
        [class^="icon-"],
        [class*=" icon-"] {
            color: var(--discord-muted);
            font-size: 18px;
            transition: color 0.15s ease;
        }

        /* Menu Icons */
        .menu-list .menu-item a .icon i {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            margin-right: 8px;
            color: var(--discord-muted);
        }

        .menu-list .menu-item a:hover .icon i {
            color: var(--discord-text);
        }

        .menu-list .menu-item.active a .icon i {
            color: var(--discord-accent);
        }

        /* Action Icons */
        .list-icon-function .item {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border-radius: 4px;
            background-color: transparent;
            transition: background-color 0.15s ease;
        }

        .list-icon-function .item:hover {
            background-color: var(--discord-hover);
        }

        .list-icon-function .item i {
            font-size: 16px;
        }

        .list-icon-function .item.edit i {
            color: var(--discord-accent);
        }

        .list-icon-function .item.delete i {
            color: #ed4245;
            /* Discord red */
        }

        /* Status Icons */
        .icon-check,
        .icon-check-circle {
            color: var(--discord-green);
        }

        .icon-alert-circle,
        .icon-alert-triangle {
            color: #faa61a;
            /* Discord yellow/warning */
        }

        .icon-x,
        .icon-x-circle {
            color: #ed4245;
            /* Discord red */
        }

        /* Notification Icons */
        .icon-bell {
            color: var(--discord-muted);
        }

        /* Interactive Icons */
        .icon-plus,
        .icon-edit-3,
        .icon-trash-2 {
            transition: transform 0.15s ease;
        }

        .icon-plus:hover,
        .icon-edit-3:hover,
        .icon-trash-2:hover {
            transform: scale(1.1);
        }

        /* Header Icons */
        .header-dashboard .icon {
            color: var(--discord-text);
            font-size: 20px;
        }

        /* Button Icons */
        .btn i {
            margin-right: 6px;
            font-size: 16px;
            vertical-align: text-bottom;
        }

        /* Form Icons */
        .form-group .icon {
            color: var(--discord-muted);
        }

        /* Table Scrollbar Styling */
        .wg-table {
            overflow-x: auto;
            max-width: 100%;
        }

        /* Discord-style scrollbar */
        .wg-table::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .wg-table::-webkit-scrollbar-track {
            background-color: var(--discord-sidebar);
            border-radius: 4px;
        }

        .wg-table::-webkit-scrollbar-thumb {
            background-color: #c7ccd1;
            border-radius: 4px;
        }

        .wg-table::-webkit-scrollbar-thumb:hover {
            background-color: #a0a6ad;
        }

        /* Fix for ORDER NO header specifically */
        .wg-table th:first-child {
            white-space: nowrap !important;
            min-width: 120px !important;
            /* Increased width */
            padding-left: 16px;
            padding-right: 16px;
            text-align: center;
        }

        /* Ensure all table headers have proper spacing and don't wrap */
        .wg-table th {
            white-space: nowrap !important;
            padding: 12px 16px;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            background-color: var(--discord-sidebar);
            border-bottom: 1px solid var(--discord-divider);
            overflow: visible !important;
        }

        /* Ensure table doesn't collapse columns */
        .wg-table table {
            table-layout: fixed;
            width: 100%;
            min-width: 800px;
            /* Ensure minimum width for all content */
        }

        /* Ensure horizontal scrolling works properly */
        .table-container {
            max-height: 400px;
            overflow-y: auto;
            border-radius: 0 0 8px 8px;
        }

        .wg-table {
            overflow-x: auto;
            max-width: 100%;
        }

        /* Recent orders section styling */
        .recent-orders {
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid var(--discord-divider);
            margin-bottom: 24px;
        }

        .recent-orders .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 16px;
            background-color: var(--discord-card);
            border-bottom: 1px solid var(--discord-divider);
        }

        .recent-orders .header h3 {
            font-size: 16px;
            font-weight: 600;
            margin: 0;
        }

        .recent-orders .table-container {
            max-height: 400px;
            overflow-y: auto;
        }

        /* Table row styling */
        .wg-table tbody tr {
            transition: background-color 0.15s ease;
        }

        .wg-table tbody tr:hover {
            background-color: var(--discord-hover);
        }

        /* View all link */
        .view-all {
            color: var(--discord-accent);
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
        }

        .view-all:hover {
            text-decoration: underline;
        }

        /* ========================================
           HOMMSS BLUE BUTTON STYLES FOR ADMIN
           ======================================== */

        .tf-button {
            background-color: var(--hommss-blue) !important;
            border-color: var(--hommss-blue) !important;
            color: #fff !important;
        }

        .tf-button:hover {
            background-color: var(--hommss-blue-hover) !important;
            border-color: var(--hommss-blue-hover) !important;
            color: #fff !important;
        }

        .btn-primary {
            background-color: var(--hommss-blue) !important;
            border-color: var(--hommss-blue) !important;
            color: #fff !important;
        }

        .btn-primary:hover {
            background-color: var(--hommss-blue-hover) !important;
            border-color: var(--hommss-blue-hover) !important;
            color: #fff !important;
        }

        button[type="submit"] {
            background-color: var(--hommss-blue) !important;
            border-color: var(--hommss-blue) !important;
            color: #fff !important;
        }

        button[type="submit"]:hover {
            background-color: var(--hommss-blue-hover) !important;
            border-color: var(--hommss-blue-hover) !important;
            color: #fff !important;
        }

        /* Admin specific button styles */
        .btn-success {
            background-color: var(--hommss-blue) !important;
            border-color: var(--hommss-blue) !important;
        }

        .btn-info {
            background-color: var(--hommss-blue) !important;
            border-color: var(--hommss-blue) !important;
        }

        /* Admin action buttons */
        .list-icon-function .item:not(.delete) {
            color: var(--hommss-blue) !important;
        }

        .list-icon-function .item:not(.delete):hover {
            background-color: var(--hommss-blue-light) !important;
        }

        /* Remove gray background from notification and username dropdowns */
        .header-grid .dropdown .btn.btn-secondary {
            background-color: transparent !important;
            border: none !important;
            box-shadow: none !important;
            padding: 8px 12px;
        }

        .header-grid .dropdown .btn.btn-secondary:hover,
        .header-grid .dropdown .btn.btn-secondary:focus,
        .header-grid .dropdown .btn.btn-secondary:active {
            background-color: var(--discord-hover) !important;
            border: none !important;
            box-shadow: none !important;
        }

        .header-grid .dropdown .btn.btn-secondary:focus {
            outline: none !important;
        }

        /* Style the notification and user items */
        .header-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--discord-text);
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--discord-text);
        }

        .header-user .image img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }

        .header-user .flex.flex-column {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        .header-user .body-title {
            font-weight: 500;
            color: var(--discord-text);
            margin-bottom: 2px;
        }

        .header-user .text-tiny {
            color: var(--discord-muted);
            font-size: 12px;
        }
    </style>

    @stack('styles')
</head>

<body class="body">
    <div id="wrapper">
        <div id="page" class="">
            <div class="layout-wrap">

                <!-- <div id="preload" class="preload-container">
    <div class="preloading">
        <span></span>
    </div>
</div> -->

                <div class="section-menu-left">
                    <div class="box-logo">
                        <a href="{{ route('admin.index') }}
                        " id="site-logo-inner">
                            <img class="" id="logo_header" alt="" src="{{ asset('images/logo/logo.png') }}"
                                data-light="{{ asset('images/logo/logo.png') }}" data-dark="{{ asset('images/logo/logo.png') }}">
                        </a>
                        <div class="button-show-hide">
                            <i class="icon-menu-left"></i>
                        </div>
                    </div>
                    <div class="center">
                        <div class="center-item">
                            <div class="center-heading">Main Home</div>
                            <ul class="menu-list">
                                <li class="menu-item">
                                    <a href="{{ route('admin.index') }}" class="">
                                        <div class="icon"><i class="icon-home"></i></div>
                                        <div class="text">Dashboard</div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="center-item">
                            <ul class="menu-list">
                                <li class="menu-item has-children">
                                    <a href="javascript:void(0);" class="menu-item-button">
                                        <div class="icon"><i class="icon-package"></i></div>
                                        <div class="text">Products</div>
                                    </a>
                                    <ul class="sub-menu">
                                        <li class="sub-menu-item">
                                            <a href="{{route('admin.product.add')}}" class="">
                                                <div class="text">Add Product</div>
                                            </a>
                                        </li>
                                        <li class="sub-menu-item">
                                            <a href="{{route('admin.products')}}" class="">
                                                <div class="text">Products</div>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="menu-item has-children">
                                    <a href="javascript:void(0);" class="menu-item-button">
                                        <div class="icon"><i class="icon-tag"></i></div>
                                        <div class="text">Brand</div>
                                    </a>
                                    <ul class="sub-menu">
                                        <li class="sub-menu-item">
                                            <a href="{{route('admin.brand.add')}}" class="">
                                                <div class="text">New Brand</div>
                                            </a>
                                        </li>
                                        <li class="sub-menu-item">
                                            <a href="{{route('admin.brands')}}" class="">
                                                <div class="text">Brands</div>
                                            </a>
                                        </li>
                                    </ul>
                                </li>
                                <li class="menu-item has-children">
                                    <a href="javascript:void(0);" class="menu-item-button">
                                        <div class="icon"><i class="icon-folder"></i></div>
                                        <div class="text">Category</div>
                                    </a>
                                    <ul class="sub-menu">
                                        <li class="sub-menu-item">
                                            <a href="{{route('admin.category.add')}}" class="">
                                                <div class="text">New Category</div>
                                            </a>
                                        </li>
                                        <li class="sub-menu-item">
                                            <a href="{{route('admin.categories')}}" class="">
                                                <div class="text">Categories</div>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="menu-item has-children">
                                    <a href="javascript:void(0);" class="menu-item-button">
                                        <div class="icon"><i class="icon-shopping-bag"></i></div>
                                        <div class="text">Order</div>
                                    </a>
                                    <ul class="sub-menu">
                                        <li class="sub-menu-item">
                                            <a href="{{route('admin.orders')}}" class="">
                                                <div class="text">Orders</div>
                                            </a>
                                        </li>
                                        <li class="sub-menu-item">
                                            <a href="order-tracking.html" class="">
                                                <div class="text">Order tracking</div>
                                            </a>
                                        </li>
                                    </ul>
                                </li>

                                <li class="menu-item">
                                    <a href="{{ route('admin.users') }}" class="">
                                        <div class="icon"><i class="icon-users"></i></div>
                                        <div class="text">Users</div>
                                    </a>
                                </li>

                                <li class="menu-item">
                                    <a href="{{ route('admin.settings') }}" class="">
                                        <div class="icon"><i class="icon-settings"></i></div>
                                        <div class="text">Settings</div>
                                    </a>
                                </li>

                                <li class="menu-item">
                                    <form method="POST" action="{{route('logout')}}" id="logout-form">
                                        @csrf
                                        <a href="{{route('logout')}}" class="" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                            <div class="icon"><i class="icon-log-out"></i></div>
                                            <div class="text">Logout</div>
                                        </a>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="section-content-right">

                    <div class="header-dashboard">
                        <div class="wrap">
                            <div class="header-left">
                                <!-- <a href="index-2.html">
                                    <img class="" id="logo_header_mobile" alt="" src="{{ asset('images/logo/logo.png') }}"
                                        data-light="{{ asset('images/logo/logo.png') }}" data-dark="{{ asset('images/logo/logo.png') }}"
                                        data-width="154px" data-height="52px" data-retina="{{ asset('images/logo/logo.png') }}">
                                </a> -->
                                <div class="button-show-hide">
                                    <i class="icon-menu-left"></i>
                                </div>


                                <form class="form-search flex-grow">
                                    <fieldset class="name">
                                        <input type="text" placeholder="Search here..." class="show-search" name="name"
                                            tabindex="2" value="" aria-required="true" required="">
                                    </fieldset>
                                    <div class="button-submit">
                                        <button class="" type="submit"><i class="icon-search"></i></button>
                                    </div>
                                    <!-- <div class="box-content-search" id="box-content-search">
                                        <ul class="mb-24">
                                            <li class="mb-14">
                                                <div class="body-title">Top selling product</div>
                                            </li>
                                            <li class="mb-14">
                                                <div class="divider"></div>
                                            </li>
                                            <li>
                                                <ul>
                                                    <li class="product-item gap14 mb-10">
                                                        <div class="image no-bg">
                                                            <img src="images/products/17.png" alt="">
                                                        </div>
                                                        <div class="flex items-center justify-between gap20 flex-grow">
                                                            <div class="name">
                                                                <a href="product-list.html" class="body-text">Dog Food
                                                                    Rachael Ray Nutrish®</a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="mb-10">
                                                        <div class="divider"></div>
                                                    </li>
                                                    <li class="product-item gap14 mb-10">
                                                        <div class="image no-bg">
                                                            <img src="images/products/18.png" alt="">
                                                        </div>
                                                        <div class="flex items-center justify-between gap20 flex-grow">
                                                            <div class="name">
                                                                <a href="product-list.html" class="body-text">Natural
                                                                    Dog Food Healthy Dog Food</a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="mb-10">
                                                        <div class="divider"></div>
                                                    </li>
                                                    <li class="product-item gap14">
                                                        <div class="image no-bg">
                                                            <img src="images/products/19.png" alt="">
                                                        </div>
                                                        <div class="flex items-center justify-between gap20 flex-grow">
                                                            <div class="name">
                                                                <a href="product-list.html" class="body-text">Freshpet
                                                                    Healthy Dog Food and Cat</a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                        <ul class="">
                                            <li class="mb-14">
                                                <div class="body-title">Order product</div>
                                            </li>
                                            <li class="mb-14">
                                                <div class="divider"></div>
                                            </li>
                                            <li>
                                                <ul>
                                                    <li class="product-item gap14 mb-10">
                                                        <div class="image no-bg">
                                                            <img src="images/products/20.png" alt="">
                                                        </div>
                                                        <div class="flex items-center justify-between gap20 flex-grow">
                                                            <div class="name">
                                                                <a href="product-list.html" class="body-text">Sojos
                                                                    Crunchy Natural Grain Free...</a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="mb-10">
                                                        <div class="divider"></div>
                                                    </li>
                                                    <li class="product-item gap14 mb-10">
                                                        <div class="image no-bg">
                                                            <img src="images/products/21.png" alt="">
                                                        </div>
                                                        <div class="flex items-center justify-between gap20 flex-grow">
                                                            <div class="name">
                                                                <a href="product-list.html" class="body-text">Kristin
                                                                    Watson</a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="mb-10">
                                                        <div class="divider"></div>
                                                    </li>
                                                    <li class="product-item gap14 mb-10">
                                                        <div class="image no-bg">
                                                            <img src="images/products/22.png" alt="">
                                                        </div>
                                                        <div class="flex items-center justify-between gap20 flex-grow">
                                                            <div class="name">
                                                                <a href="product-list.html" class="body-text">Mega
                                                                    Pumpkin Bone</a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li class="mb-10">
                                                        <div class="divider"></div>
                                                    </li>
                                                    <li class="product-item gap14">
                                                        <div class="image no-bg">
                                                            <img src="images/products/23.png" alt="">
                                                        </div>
                                                        <div class="flex items-center justify-between gap20 flex-grow">
                                                            <div class="name">
                                                                <a href="product-list.html" class="body-text">Mega
                                                                    Pumpkin Bone</a>
                                                            </div>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </li>
                                        </ul>
                                    </div> -->
                                </form>

                            </div>
                            <div class="header-grid">

                                <div class="popup-wrap message type-header">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                            id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="header-item">
                                                <span class="text-tiny notification-count">0</span>
                                                <i class="icon-bell"></i>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end has-content"
                                            aria-labelledby="dropdownMenuButton2" style="width: 350px; max-height: 400px; overflow-y: auto;">
                                            <li class="notification-header">
                                                <div class="d-flex justify-content-between align-items-center p-3">
                                                    <h6 class="mb-0">Notifications</h6>
                                                    <button class="btn btn-sm btn-outline-primary mark-all-read" style="font-size: 11px;">
                                                        Mark all read
                                                    </button>
                                                </div>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <div id="notification-list">
                                                <li class="no-notifications text-center p-3">
                                                    <div class="text-muted">
                                                        <i class="icon-bell me-2"></i>
                                                        No new notifications
                                                    </div>
                                                </li>
                                            </div>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a href="#" class="tf-button w-full">View all</a></li>
                                        </ul>
                                    </div>
                                </div>




                                <div class="popup-wrap user type-header">
                                    <div class="dropdown">
                                        <button class="btn btn-secondary dropdown-toggle" type="button"
                                            id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-expanded="false">
                                            <span class="header-user wg-user">
                                                <span class="image">
                                                    <img src="https://www.pngall.com/wp-content/uploads/5/Profile.png" alt="">
                                                </span>
                                                <span class="flex flex-column">
                                                    <span class="body-title mb-2">{{ Auth::user()->name }}</span>
                                                    <span class="text-tiny">Admin</span>
                                                </span>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end has-content"
                                            aria-labelledby="dropdownMenuButton3">
                                            <li>
                                                <a href="#" class="user-item">
                                                    <div class="icon">
                                                        <i class="icon-user"></i>
                                                    </div>
                                                    <div class="body-title-2">Account</div>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('logout') }}" class="user-item" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                    <div class="icon">
                                                        <i class="icon-log-out"></i>
                                                    </div>
                                                    <div class="body-title-2">Log out</div>
                                                </a>
                                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                                    @csrf
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    <div class="main-content">
                        @yield('content')

                        <div class="bottom-page">
                            <div class="body-text">Copyright © III-CINS</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('js/sweetalert.min.js') }}"></script>
    <script src="{{ asset('js/apexcharts/apexcharts.js') }}"></script>
    <script src="{{ asset('js/main.js') }}"></script>

    <!-- Notification System -->
    <script>
        $(document).ready(function() {
            // Load notifications on page load
            loadNotifications();

            // Refresh notifications every 30 seconds
            setInterval(loadNotifications, 30000);

            // Mark all as read functionality
            $('.mark-all-read').on('click', function(e) {
                e.preventDefault();
                markAllAsRead();
            });
        });

        function loadNotifications() {
            $.ajax({
                url: '{{ route("notifications.get") }}',
                type: 'GET',
                success: function(data) {
                    updateNotificationUI(data);
                },
                error: function(xhr, status, error) {
                    console.error('Error loading notifications:', error);
                }
            });
        }

        function updateNotificationUI(data) {
            // Update notification count
            $('.notification-count').text(data.unread_count);

            // Show/hide notification badge
            if (data.unread_count > 0) {
                $('.notification-count').show();
                $('.icon-bell').addClass('text-warning');
            } else {
                $('.notification-count').hide();
                $('.icon-bell').removeClass('text-warning');
            }

            // Update notification list
            const notificationList = $('#notification-list');
            notificationList.empty();

            if (data.notifications.length === 0) {
                notificationList.html(`
                    <li class="no-notifications text-center p-3">
                        <div class="text-muted">
                            <i class="icon-bell me-2"></i>
                            No new notifications
                        </div>
                    </li>
                `);
            } else {
                data.notifications.forEach(function(notification) {
                    const isReadClass = notification.is_read ? 'read' : 'unread';
                    const bgClass = notification.is_read ? '' : 'bg-light';

                    notificationList.append(`
                        <li class="notification-item ${isReadClass} ${bgClass}" data-id="${notification.id}">
                            <div class="message-item p-3" style="cursor: pointer;" onclick="handleNotificationClick(${notification.id}, '${notification.url}')">
                                <div class="d-flex">
                                    <div class="me-3">
                                        <i class="${notification.icon} text-primary"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="body-title-2 mb-1">${notification.title}</div>
                                        <div class="text-tiny text-muted">${notification.message}</div>
                                        <div class="text-tiny text-muted mt-1">
                                            <i class="icon-clock me-1"></i>${notification.time_ago}
                                        </div>
                                    </div>
                                    ${!notification.is_read ? '<div class="notification-dot bg-primary rounded-circle" style="width: 8px; height: 8px;"></div>' : ''}
                                </div>
                            </div>
                        </li>
                    `);
                });
            }
        }

        function handleNotificationClick(notificationId, url) {
            // Mark as read
            $.ajax({
                url: '{{ route("notifications.mark-read") }}',
                type: 'POST',
                data: {
                    notification_id: notificationId,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // Reload notifications to update UI
                    loadNotifications();

                    // Navigate to URL if provided
                    if (url && url !== '#') {
                        window.location.href = url;
                    }
                }
            });
        }

        function markAllAsRead() {
            $.ajax({
                url: '{{ route("notifications.mark-all-read") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    loadNotifications();

                    // Show success message
                    if (typeof swal !== 'undefined') {
                        swal({
                            title: "Success!",
                            text: response.message,
                            type: "success",
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                }
            });
        }
    </script>

    @stack('scripts')
</body>

</html>