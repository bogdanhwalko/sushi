<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="<?=\yii\helpers\Url::to(['/admin-panel/'])?>" class="brand-link">
        <img src="<?=$assetDir ?>/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">107 sushi</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="<?= $assetDir ?>/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">Admin</a>
            </div>
        </div>

        <!-- SidebarSearch Form -->
        <!-- href be escaped -->
        <!-- <div class="form-inline">
            <div class="input-group" data-widget="sidebar-search">
                <input class="form-control form-control-sidebar" type="search" placeholder="Search" aria-label="Search">
                <div class="input-group-append">
                    <button class="btn btn-sidebar">
                        <i class="fas fa-search fa-fw"></i>
                    </button>
                </div>
            </div>
        </div> -->

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <?php
            echo \hail812\adminlte\widgets\Menu::widget([
                'items' => [
                    [
                        'label' => 'Головна',
                        'url' => ['/admin-panel/'],
                        'iconStyle' => 'nav-icon fas fa-home'
                    ],

                    [
                        'label' => 'Товари',
                        'url' => ['/admin-panel/product/index'],
                        //'iconStyle' => 'far',
                        'icon' => 'nav-icon fas fa-utensils',
                        'visible' => true,
                    ],

                    [
                        'label' => 'Категорії',
                        'url' => ['/admin-panel/category/index'],
                        //'iconStyle' => 'far',
                        'icon' => 'nav-icon fas fa-th-large',
                        'visible' => true,
                    ],

                    [
                        'label' => 'Міста',
                        'url' => ['/admin-panel/city/index'],
                        //'iconStyle' => 'far',
                        'icon' => 'nav-icon fas fa-city',
                        'visible' => true,
                    ],

                    [
                        'label' => 'Групи Telegram',
                        'url' => ['/admin-panel/telegram-group/index'],
                        //'iconStyle' => 'far',
                        'icon' => 'nav-icon fas fa-users',
                        'visible' => true,
                    ],
                ],
            ]);
            ?>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>