<nav class="main-header navbar navbar-expand navbar-light border-bottom bg-white" ng-controller="HeaderCtrl" ng-cloak>
  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#"><i class="fa fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="?mod=home" class="nav-link">Inicio</a>
    </li>
  </ul>

  <ul class="navbar-nav ml-auto">
    
    <li class="nav-item dropdown">
      <a class="nav-link" data-toggle="dropdown" href="#" title="Usuarios en Línea">
        <i class="fas fa-users" style="font-size: 1.2rem;"></i>
        <span class="badge badge-success navbar-badge pulso-online">{{usuariosOnline.length}}</span>
      </a>
      
      <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow border-0" style="border-radius: 0.5rem; overflow: hidden;">
        <span class="dropdown-item dropdown-header font-weight-bold bg-light border-bottom">
          <i class="fas fa-circle text-success mr-1 text-xs pulso-online"></i> {{usuariosOnline.length}} En línea
        </span>

        <div style="max-height: 280px; overflow-y: auto;">
          <a href="#" class="dropdown-item py-2 border-bottom" ng-repeat="user in usuariosOnline">
            <div class="media align-items-center">
              <img src="Images/usuarios/nouser.png" alt="User" class="img-size-32 mr-3 img-circle shadow-sm">
              <div class="media-body">
                <h3 class="dropdown-item-title font-weight-bold text-sm text-dark">
                  {{user.nombreCompleto}}
                </h3>
                <p class="text-xs text-muted mb-0">@{{user.Username}}</p>
              </div>
            </div>
          </a>

          <div class="dropdown-item text-center text-muted py-4" ng-if="usuariosOnline.length === 0 || !usuariosOnline">
            <i class="fas fa-user-slash fa-2x mb-2 text-light"></i>
            <p class="text-xs mb-0">Solo tú estás conectado</p>
          </div>
        </div>
      </div>
    </li>

    <li class="nav-item">
      <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">
        <i class="fa fa-th-large" style="font-size: 1.2rem;"></i>
      </a>
    </li>
  </ul>
</nav>

<style>
  .pulso-online { animation: latido 1.5s infinite; }
  @keyframes latido {
      0% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.4; transform: scale(0.85); }
      100% { opacity: 1; transform: scale(1); }
  }
</style>