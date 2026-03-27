'use strict';

// =========================================================
// INICIALIZACIÓN
// =========================================================
var $_SESSION;
var tsuruVolks = angular.module('tsuruVolks', ["uiSwitch", "datatables", "vcRecaptcha"]);

// =========================================================
// MÓDULOS EXTERNOS (Dependencies)
// =========================================================
angular.module('uiSwitch', [])
    .directive('switch', function () {
        return {
            restrict: 'AE',
            replace: true,
            transclude: true,
            template: function (element, attrs) {
                return `
                    <span class="switch ${attrs.class || ''}" 
                          ${attrs.ngModel ? `ng-click="${attrs.disabled} ? ${attrs.ngModel} : ${attrs.ngModel}=!${attrs.ngModel}${attrs.ngChange ? '; ' + attrs.ngChange + '()' : ''}"` : ''}
                          ng-class="{ checked:${attrs.ngModel}, disabled:${attrs.disabled} }">
                        <small></small>
                        <input type="checkbox" 
                               ${attrs.id ? `id="${attrs.id}"` : ''} 
                               ${attrs.name ? `name="${attrs.name}"` : ''} 
                               ${attrs.ngModel ? `ng-model="${attrs.ngModel}"` : ''} 
                               style="display:none" />
                        <span class="switch-text">
                            ${attrs.on ? `<span class="on">${attrs.on}</span>` : ''}
                            ${attrs.off ? `<span class="off">${attrs.off}</span>` : ' '}
                        </span>
                    </span>
                `;
            }
        }
    });

// =========================================================
// DIRECTIVAS GLOBALES (UI Components)
// =========================================================
tsuruVolks
    .directive('uploaderModel', ["$parse", function ($parse) {
        return {
            restrict: 'A',
            link: function (scope, iElement, iAttrs) {
                iElement.on('change', function (e) {
                    $parse(iAttrs.uploaderModel).assign(scope, iElement[0].files[0]);
                });
            }
        };
    }])
    .directive('richTextEditor', function () {
        return {
            restrict: "A",
            require: 'ngModel',
            transclude: true,
            link: function (scope, element, attrs, ctrl) {
                var textarea = element.wysihtml5({ toolbar: { fa: true } });
                var editor = textarea.data('wysihtml5').editor;

                var synchronize = function () {
                    ctrl.$setViewValue(editor.getValue());
                    scope.$applyAsync(); 
                };

                editor.on('redo:composer', synchronize);
                editor.on('undo:composer', synchronize);
                editor.on('paste:composer', synchronize);
                editor.on('aftercommand:composer', synchronize);
                editor.on('change:composer', synchronize);
                editor.on('keyup:composer', synchronize);

                ctrl.$render = function () {
                    textarea.html(ctrl.$viewValue);
                    editor.setValue(ctrl.$viewValue);
                };

                ctrl.$render();
            }
        };
    })
    .directive('inner', function () {
        return {
            restrict: 'A',
            replace: false,
            link: function (scope, element, attr) {
                attr.$observe('inner', function (data) {
                    element.html(attr.inner);
                }, true);
            }
        };
    })
    .directive('convertToString', function () {
        return {
            require: 'ngModel',
            link: function (scope, element, attrs, ngModel) {
                ngModel.$parsers.push(value => parseFloat(value));
                ngModel.$formatters.push(value => '' + value);
            }
        };
    })
    .directive('converttoNumber', function () {
        return {
            require: 'ngModel',
            link: function (scope, element, attrs, ngModel) {
                ngModel.$parsers.push(value => '' + value);
                ngModel.$formatters.push(value => parseFloat(value));
            }
        }
    });

// =========================================================
// SERVICIOS GLOBALES
// =========================================================
tsuruVolks.service("srvPaginacion", function () {
    this.getPaginacion = () => { };
});

// =========================================================
// FUNCIONES AUXILIARES GLOBALES
// =========================================================
var mensaje = function (title, tipo, txtmsg) {
    new PNotify({
        title: title,
        text: txtmsg,
        type: tipo,
        styling: 'bootstrap3'
    });
};

// =========================================================
// CONTROLADORES GLOBALES (Layout & Menús)
// =========================================================

// --- Controlador Lateral (Menú) ---
tsuruVolks.controller('asideCtrl', ['$scope', '$http', function($scope, $http) {
    var obj = $scope;
    obj.data = { opc: "" };
    obj.username;
    obj.srcimagen = "Images/boxed-bg.jpg";

    obj.btnPrueba = function () {
        confirm("Esta es una prueba");
    }

    obj.usrCON = () => {
        obj.data.opc = "usrCON";
        $http({
            method: 'POST',
            url: "././Modulo/Home/Ajax/Home.php",
            data: { home: obj.data }
        }).then(function successCallback(res) {
            if (res.data.Bandera == 1) {
                obj.datos = res.data;
                if (obj.datos.Usuarios.Estatus == '0') {
                    localStorage.clear();
                    location.href = "../terminar.php";
                }
            } else {
               if (res.data.mensaje && res.data.mensaje.trim() !== "") {
                    toastr.error(res.data.mensaje);
                }
            }
        }, function errorCallback(res) {
            if (res.data.mensaje && res.data.mensaje.trim() !== "") {
                toastr.error(res.data.mensaje);
            }
        });
    }
    obj.usrCON();
}]);

// --- Controlador de la Cabecera (TopBar) ---
tsuruVolks.controller('HeaderCtrl', ["$scope", "$http", "$interval", function($scope, $http, $interval) {
    var obj = $scope;
    obj.usuariosOnline = [];
    var urlHome = "./Modulo/Home/Ajax/Home.php"; 

    obj.getOnlineUsers = () => {
        $http.post(urlHome, { home: { opc: "get_online_users" } }).then(function(res){
            if(res.data && res.data.Bandera == 1){
                obj.usuariosOnline = res.data.Data;
            }
        }).catch(function(err) {
            console.error("Error al consultar usuarios en línea");
        });
    };

    obj.getOnlineUsers();
    $interval(obj.getOnlineUsers, 20000); // Revisa usuarios cada 20 seg
}]);


// =========================================================
//  Routing & Seguridad
// =========================================================
tsuruVolks.run(["$http", function ($http) {
    
    const currentUrl = window.location.href;

    if (currentUrl.includes("?mod=RepProductos")) {
        console.log("Módulo: Reportes de Productos");
    } else {
        if (localStorage.getItem("dateNew")) {
            localStorage.removeItem("dateNew");
            localStorage.removeItem("dateOld");
        }
    }

    if (currentUrl.includes("?mod=webprincipal")) {
        if (localStorage.getItem("TabActive") == null) {
            localStorage.setItem("TabActive", "Principal");
        }
    } else {
        localStorage.removeItem("TabActive");
    }

    // --- Global de Latido (Heartbeat / Ping) ---
    var urlPing = "./Modulo/Home/Ajax/Home.php";

    function mandarLatido() {
        if (localStorage.getItem("session") || localStorage.getItem("ultimoAcceso")) {
            $http({
                method: 'POST',
                url: urlPing,
                data: { home: { opc: "ping" } }
            }).then(function (res) {

                //Expulsión por Mantenimiento
                if (res.data && res.data.Bandera == -1) {
                    localStorage.clear();
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¡SISTEMA EN MANTENIMIENTO!',
                            text: 'El Administrador ha iniciado una actualización. Por tu seguridad y la de los datos, tu sesión ha sido cerrada.',
                            icon: 'warning',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: '<i class="fas fa-sign-out-alt mr-1"></i> Salir del Sistema'
                        }).then((result) => {
                            window.location.href = "../terminar.php";
                        });
                    } else {
                        alert("⚠️ SISTEMA EN MANTENIMIENTO ⚠️\n\nEl Administrador ha puesto el sistema en modo mantenimiento. Tu sesión se cerrará por seguridad.");
                        window.location.href = "../terminar.php";
                    }
                    return;
                }

                if (res.data && res.data.Bandera == 0 && res.data.mensaje === "Acceso denegado. Sesión expirada.") {
                    localStorage.clear();
                    window.location.href = "../terminar.php";
                }
            });
        }
    }

    if (window.location.href.includes('admin')) {
        mandarLatido();
        setInterval(mandarLatido, 60000);
    }

}]);