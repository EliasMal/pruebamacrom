<!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
        <div class="p-3">
            <h5>Herramientas</h5>
            <ul class="nav nav-pills flex-column nav-sidebar">
                <li class="nav-item ">
                        <a href="?mod=Perfil" class="nav-link text-white">
                            <i class="nav-icon fa fa-sliders"></i>
                            <p>
                                Perfil del usuario
                            </p>
                        </a>
                    </li>
                    <li class="nav-item ">
                        <a onclick="cerrar_sesion()" class="nav-link text-white enlace">
                            <i class="nav-icon fa fa-sign-out"></i>
                            <p>
                               Cerrar Sesión
                            </p>
                        </a>
                    </li>
            </ul>
            
            
        </div>
        
    </aside>
    <script>
        function cerrar_sesion(){
            if(localStorage.getItem("mantenimiento") == '1'){
                alert("Usuarios bloqueados por mantenimiento, desbloquear para que puedan iniciar sesion");
            } else{
               location.href="../terminar.php";
            }
        }
    </script>
  <!-- /.control-sidebar -->

