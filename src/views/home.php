<?php
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Simera | Sistema Gestion Turnos</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../dist/css/adminlte.min.css">
    <!-- overlayScrollbars -->
    <link rel="stylesheet" href="../plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
</head>

<body class="hold-transition sidebar-mini layout-fixed" data-panel-auto-height-mode="height">
    <div class="wrapper">

        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <!-- Left navbar links -->
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <!-- Right navbar links -->
            <ul class="navbar-nav ml-auto">
                <li class="nav-item dropdown">
                    <a class="nav-link" data-toggle="dropdown" href="#">
                        <i class="far fa-user"></i> <?php echo htmlspecialchars($_SESSION['user']['usuanom']); ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <a href="#" class="dropdown-item">
                            <i class="fas fa-user mr-2"></i> Perfil
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="../controllers/AuthController.php?action=logout" class="dropdown-item" target="_top">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </a>
                        <!-- <a href="../controllers/AuthController.php?action=logout" class="dropdown-item">
                            <i class="fas fa-sign-out-alt mr-2"></i> Cerrar Sesión
                        </a> -->
                    </div>
                </li>
            </ul>
        </nav>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <!-- Brand Logo -->
            <a href="home.php" class="brand-link">
                <img src="../dist/img/AdminLTELogo.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
                    style="opacity: .8">
                <span class="brand-text font-weight-light">Promarisco</span>
            </a>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Sidebar user panel (optional) -->
                <div class="user-panel mt-3 pb-3 mb-3 d-flex">
                    <div class="image">
                        <img src="../dist/img/user2-160x160.jpg" class="img-circle elevation-2" alt="User Image">
                    </div>
                    <div class="info">
                        <a href="#" class="d-block"><?php echo htmlspecialchars($_SESSION['user']['usuanomred']); ?></a>
                        <?php if (isset($_SESSION['user']['profiles'])): ?>
                            <small>
                                <?php
                                $profiles = array_column($_SESSION['user']['profiles'], 'perfdesc');
                                echo htmlspecialchars(implode(', ', $profiles));
                                ?>
                            </small>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- SidebarSearch Form -->
                <div class="form-inline">
                    <div class="input-group" data-widget="sidebar-search">
                        <input class="form-control form-control-sidebar" type="search" placeholder="Buscar..."
                            aria-label="Search">
                        <div class="input-group-append">
                            <button class="btn btn-sidebar">
                                <i class="fas fa-search fa-fw"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Sidebar Menu -->
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                        data-accordion="false">
                        <li class="nav-header">SIMERA</li>
                        <?php if (isset($_SESSION['menu']) && !empty($_SESSION['menu'])): ?>
                            <?php foreach ($_SESSION['menu'] as $item): ?>
                                <?php if (isset($item['main']) && is_array($item['main'])): ?>
                                    <li class="nav-item<?php echo !empty($item['submenu']) ? ' has-treeview' : ''; ?>">
                                        <a href="<?php echo isset($item['main']['SeAplNombreObjeto']) ? htmlspecialchars($item['main']['SeAplNombreObjeto']) : '#'; ?>"
                                            class="nav-link">
                                            <i
                                                class="nav-icon <?php echo isset($item['main']['SeAplFontIcon']) ? htmlspecialchars($item['main']['SeAplFontIcon']) : 'fas fa-circle'; ?>"></i>
                                            <p>
                                                <?php echo isset($item['main']['SeAplDescripcion']) ? htmlspecialchars($item['main']['SeAplDescripcion']) : 'Menú'; ?>
                                                <?php if (!empty($item['submenu'])): ?>
                                                    <i class="right fas fa-angle-left"></i>
                                                <?php endif; ?>
                                            </p>
                                        </a>

                                        <?php if (!empty($item['submenu'])): ?>
                                            <ul class="nav nav-treeview">
                                                <?php foreach ($item['submenu'] as $subitem): ?>
                                                    <li
                                                        class="nav-item<?php echo !empty($subitem['applications']) ? ' has-treeview' : ''; ?>">
                                                        <a href="<?php echo isset($subitem['main']['SeAplNombreObjeto']) ? htmlspecialchars($subitem['main']['SeAplNombreObjeto']) : '#'; ?>"
                                                            class="nav-link">
                                                            <i
                                                                class="<?php echo isset($subitem['main']['SeAplFontIcon']) ? htmlspecialchars($subitem['main']['SeAplFontIcon']) : 'fas fa-circle'; ?> nav-icon"></i>
                                                            <p>
                                                                <?php echo isset($subitem['main']['SeAplDescripcion']) ? htmlspecialchars($subitem['main']['SeAplDescripcion']) : 'Submenú'; ?>
                                                                <?php if (!empty($subitem['applications'])): ?>
                                                                    <i class="right fas fa-angle-left"></i>
                                                                <?php endif; ?>
                                                            </p>
                                                        </a>

                                                        <?php if (!empty($subitem['applications'])): ?>
                                                            <ul class="nav nav-treeview">
                                                                <?php foreach ($subitem['applications'] as $app): ?>
                                                                    <li class="nav-item">
                                                                        <a href="<?php echo isset($app['SeAplNombreObjeto']) ? htmlspecialchars($app['SeAplNombreObjeto']) : '#'; ?>"
                                                                            class="nav-link">
                                                                            <i
                                                                                class="<?php echo isset($app['SeAplFontIcon']) ? htmlspecialchars($app['SeAplFontIcon']) : 'fas fa-circle'; ?> nav-icon"></i>
                                                                            <p><?php echo isset($app['SeAplDescripcion']) ? htmlspecialchars($app['SeAplDescripcion']) : 'Aplicación'; ?>
                                                                            </p>
                                                                        </a>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li class="nav-item">
                                <a href="#" class="nav-link text-danger">
                                    <i class="nav-icon fas fa-exclamation-circle"></i>
                                    <p>No tiene menús asignados</p>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <!-- /.sidebar-menu -->
            </div>
            <!-- /.sidebar -->
        </aside>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper iframe-mode" data-widget="iframe" data-loading-screen="750">
            <div class="nav navbar navbar-expand navbar-white navbar-light border-bottom p-0">
                <div class="nav-item dropdown">
                    <a class="nav-link bg-danger dropdown-toggle" data-toggle="dropdown" href="#" role="button"
                        aria-haspopup="true" aria-expanded="false">Cerrar</a>
                    <div class="dropdown-menu mt-0">
                        <a class="dropdown-item" href="#" data-widget="iframe-close" data-type="all">Cerrar Todos</a>
                        <a class="dropdown-item" href="#" data-widget="iframe-close" data-type="all-other">Cerrar
                            Otros</a>
                    </div>
                </div>
                <a class="nav-link bg-light" href="#" data-widget="iframe-scrollleft"><i
                        class="fas fa-angle-double-left"></i></a>
                <ul class="navbar-nav overflow-hidden" role="tablist"></ul>
                <a class="nav-link bg-light" href="#" data-widget="iframe-scrollright"><i
                        class="fas fa-angle-double-right"></i></a>
                <a class="nav-link bg-light" href="#" data-widget="iframe-fullscreen"><i class="fas fa-expand"></i></a>
            </div>
            <div class="tab-content">
                <div class="tab-empty">
                    <h2 class="display-4">No hay opciones seleccionadas!</h2>
                </div>
                <div class="tab-loading">
                    <div>
                        <h2 class="display-4">Opción está cargando <i class="fa fa-sync fa-spin"></i></h2>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.content-wrapper -->
        <footer class="main-footer">
            <strong>Copyright &copy; 2025 <a href="https://www.nuevapescanova.com/">Promarisco</a>.</strong>
            Todos los derechos reservados.
            <div class="float-right d-none d-sm-inline-block">
                <b>Versión</b> 1.0.0
            </div>
        </footer>

        <!-- Control Sidebar -->
        <aside class="control-sidebar control-sidebar-dark">
            <!-- Control sidebar content goes here -->
        </aside>
        <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src="../plugins/jquery/jquery.min.js"></script>
    <!-- jQuery UI 1.11.4 -->
    <script src="../plugins/jquery-ui/jquery-ui.min.js"></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src="../plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- overlayScrollbars -->
    <script src="../plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
    <!-- AdminLTE App -->
    <script src="../dist/js/adminlte.js"></script>
    <!-- AdminLTE for demo purposes -->
    <script src="../dist/js/demo.js"></script>

    <script>
        // Activar menús según la página actual
        $(document).ready(function () {
            var currentUrl = window.location.pathname.split('/').pop() || 'home.php';

            $('.nav-item a').each(function () {
                var menuUrl = $(this).attr('href').split('/').pop();
                if (currentUrl === menuUrl) {
                    $(this).addClass('active');
                    $(this).parents('.has-treeview').addClass('menu-open');
                    $(this).parents('.has-treeview').find('> a').addClass('active');
                }
            });
        });
    </script>
</body>

</html>