<!DOCTYPE html>
<html lang="id">
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>{{ env('APP_NAME') }}</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport"/>
    <link rel="icon" href="{{ asset('template')}}/assets/img/kaiadmin/favicon.ico" type="image/x-icon"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <!-- Fonts and icons -->
    <script src="{{ asset('template')}}/assets/js/plugin/webfont/webfont.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script>
      WebFont.load({
        google: { families: ["Public Sans:300,400,500,600,700"] },
        custom: {
          families: [
            "Font Awesome 5 Solid",
            "Font Awesome 5 Regular",
            "Font Awesome 5 Brands",
            "simple-line-icons",
          ],
          urls: ["{{ asset('template')}}/assets/css/fonts.min.css"],
        },
        active: function () {
          sessionStorage.fonts = true;
        },
      });
    </script>

    <!-- CSS Files -->
    <link rel="stylesheet" href="{{ asset('template')}}/assets/css/bootstrap.min.css" />
    <link rel="stylesheet" href="{{ asset('template')}}/assets/css/plugins.min.css" />
    <link rel="stylesheet" href="{{ asset('template')}}/assets/css/kaiadmin.min.css" />
    <link rel="stylesheet" href="{{ asset('template')}}/assets/css/demo.css" />
    <style>
      :root {
        --c-primary: #4a7bd0;
        --c-primary-dark: #3a65ad;
        --c-soft: #eef3fb;
        --c-bg: #f7f9fc;
        --c-text: #2d3a4f;
        --c-muted: #7a869a;
        --c-line: #e6ebf2;
      }

      /* Latar halaman */
      .main-panel { background: var(--c-bg); }

      /* Topbar: putih bersih, tinggi sedang */
      .main-header,
      .main-header .navbar-header { min-height: 80px; }
      .main-header .navbar-header {
        background: #fff !important;
        border-bottom: 1px solid var(--c-line) !important;
        padding: 0 24px;
      }
      .main-header .avatar-sm,
      .main-header .avatar-sm .avatar-img { width: 46px; height: 46px; }
      .main-header .profile-username { font-size: 16px; margin-left: 10px; }
      .main-header .profile-username,
      .main-header .profile-username .op-7 { color: var(--c-text) !important; }
      .main-header .badge-role {
        background: var(--c-soft);
        color: var(--c-primary-dark);
        font-size: 12px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 20px;
        margin-right: 12px;
        text-transform: capitalize;
      }

      /* Samakan tinggi logo sidebar dengan topbar */
      .sidebar .logo-header { height: 80px; }

      /* Sidebar: putih, menu aktif lembut */
      .sidebar { border-right: 1px solid var(--c-line); box-shadow: none; }
      .sidebar .nav.nav-secondary > .nav-item a { border-radius: 8px; margin: 2px 12px; }
      .sidebar .nav.nav-secondary > .nav-item i { color: var(--c-muted); }
      .sidebar .nav.nav-secondary > .nav-item p { color: var(--c-text); }
      .sidebar .nav.nav-secondary > .nav-item:not(.active) > a:hover { background: var(--c-soft) !important; }
      .sidebar .nav.nav-secondary > .nav-item.active > a { background: var(--c-soft) !important; }
      .sidebar .nav.nav-secondary > .nav-item.active > a i,
      .sidebar .nav.nav-secondary > .nav-item.active > a p,
      .sidebar .nav.nav-secondary > .nav-item.active > a .caret {
        color: var(--c-primary-dark) !important;
        font-weight: 600;
      }

      /* Judul halaman */
      .page-title { color: var(--c-text); font-weight: 700; font-size: 22px; }
      .page-header { border-left: 4px solid var(--c-primary); padding-left: 12px; }

      /* Kartu */
      .card {
        border: 1px solid var(--c-line);
        border-radius: 10px;
        box-shadow: none;
      }
      .card .card-header { background: transparent; border-bottom: 1px solid var(--c-line); }
      .card .card-title { color: var(--c-text); font-weight: 600; }

      /* Tombol */
      .btn-primary, .btn-primary:focus {
        background: var(--c-primary) !important;
        border-color: var(--c-primary) !important;
      }
      .btn-primary:hover {
        background: var(--c-primary-dark) !important;
        border-color: var(--c-primary-dark) !important;
      }
      .btn { border-radius: 6px; }

      /* Tabel */
      .table thead th { background: var(--c-bg); color: var(--c-muted); border-bottom: 1px solid var(--c-line); font-weight: 600; }

      /* Footer */
      .footer { background: #fff; border-top: 1px solid var(--c-line); }
    </style>
  </head>
  <body>
    @include('sweetalert::alert')
    <div class="wrapper">
      <!-- Sidebar -->
      <x-sidebar/>
      <!-- End Sidebar -->

      <div class="main-panel">
        <div class="main-header">
          <div class="main-header-logo">
            <!-- Logo Header -->
            <div class="logo-header" data-background-color="dark">
              <a href="{{ Auth::user()->role === 'pegawai' ? route('pegawai.dashboard') : route('home') }}" class="logo">
                <img
                  src="{{ asset('template')}}/assets/img/kaiadmin/logo_light.svg"
                  alt="navbar brand"
                  class="navbar-brand"
                  height="20"
                />
              </a>
              <div class="nav-toggle">
                <button class="btn btn-toggle toggle-sidebar">
                  <i class="gg-menu-right"></i>
                </button>
                <button class="btn btn-toggle sidenav-toggler">
                  <i class="gg-menu-left"></i>
                </button>
              </div>
              <button class="topbar-toggler more">
                <i class="gg-more-vertical-alt"></i>
              </button>
            </div>
            <!-- End Logo Header -->
          </div>
          <!-- Navbar Header -->
          <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
            <div class="container-fluid">
              <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
                <li class="nav-item topbar-user dropdown hidden-caret">
                  <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
                    <span class="badge-role">{{ Auth::user()->role }}</span>
                    <div class="avatar-sm">
                      <img src="{{ asset('template')}}/assets/img/profile.jpg" alt="..." class="avatar-img rounded-circle"/>
                    </div>
                    <span class="profile-username">
                      <span class="op-7">Hi,</span>
                      <span class="fw-bold">{{ Auth::user()->name }}</span>
                    </span>
                  </a>
                  <ul class="dropdown-menu dropdown-user animated fadeIn">
                    <div class="dropdown-user-scroll scrollbar-outer">
                      <li>
                        <div class="user-box">
                          <div class="avatar-lg">
                            <img
                              src="{{ asset('template')}}/assets/img/profile.jpg"
                              alt="image profile"
                              class="avatar-img rounded"
                            />
                          </div>
                          <div class="u-text">
                            <h4>{{ Auth::user()->name }}</h4>
                            <p class="text-muted">{{ Auth::user()->email }}</p>
                          </div>
                        </div>
                      </li>
                      <li>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#" id="btnLogout">
                            <i class="fas fa-sign-out-alt me-2"></i>{{ __('Logout') }}
                        </a>

                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                      </li>
                    </div>
                  </ul>
                </li>
              </ul>
            </div>
          </nav>
          <!-- End Navbar -->
        </div>

        <div class="container">
          <div class="page-inner">
                <div class="page-header">
                    <h4 class="page-title">@yield('page_title', 'SIBENGKEL')</h4>
                </div>
                @yield('content')
          </div>
        </div>

        <footer class="footer">
          <div class="container-fluid d-flex justify-content-between">
            <div class="copyright">
              © {{ date('Y') }} {{ env('APP_NAME') }}
            </div>
          </div>
        </footer>
      </div>
    </div>
    <!--   Core JS Files   -->
    <script src="{{ asset('template')}}/assets/js/core/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/core/popper.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/core/bootstrap.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/plugin/chart.js/chart.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/plugin/jquery.sparkline/jquery.sparkline.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/plugin/chart-circle/circles.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/plugin/datatables/datatables.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/plugin/bootstrap-notify/bootstrap-notify.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/plugin/jsvectormap/jsvectormap.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/plugin/jsvectormap/world.js"></script>
    <script src="{{ asset('template')}}/assets/js/plugin/sweetalert/sweetalert.min.js"></script>
    <script src="{{ asset('template')}}/assets/js/kaiadmin.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
        <script>
      document.getElementById('btnLogout').addEventListener('click', function (e) {
        e.preventDefault();
        swal({
          title: 'Keluar dari aplikasi?',
          text: 'Anda harus login kembali untuk mengakses sistem.',
          icon: 'warning',
          buttons: {
            cancel: { text: 'Batal', visible: true },
            confirm: { text: 'Ya, Keluar', className: 'btn btn-danger' }
          },
          dangerMode: true
        }).then(function (isConfirm) {
          if (isConfirm) {
            document.getElementById('logout-form').submit();
          }
        });
      });
    </script>
    @stack('script')s
  </body>
</html>
