/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

angular.module('uiSwitch', [])
    .directive('switch', function(){
      return {
        restrict: 'AE'
      , replace: true
      , transclude: true
      , template: function(element, attrs) {
          var html = '';
          html += '<span';
          html +=   ' class="switch' + (attrs.class ? ' ' + attrs.class : '') + '"';
          html +=   attrs.ngModel ? ' ng-click="' + attrs.disabled + ' ? ' + attrs.ngModel + ' : ' + attrs.ngModel + '=!' + attrs.ngModel + (attrs.ngChange ? '; ' + attrs.ngChange + '()"' : '"') : '';
          html +=   ' ng-class="{ checked:' + attrs.ngModel + ', disabled:' + attrs.disabled + ' }"';
          html +=   '>';
          html +=   '<small></small>';
          html +=   '<input type="checkbox"';
          html +=     attrs.id ? ' id="' + attrs.id + '"' : '';
          html +=     attrs.name ? ' name="' + attrs.name + '"' : '';
          html +=     attrs.ngModel ? ' ng-model="' + attrs.ngModel + '"' : '';
          html +=     ' style="display:none" />';
          html +=     '<span class="switch-text">'; /*adding new container for switch text*/
          html +=     attrs.on ? '<span class="on">'+attrs.on+'</span>' : ''; /*switch text on value set by user in directive html markup*/
          html +=     attrs.off ? '<span class="off">'+attrs.off + '</span>' : ' ';  /*switch text off value set by user in directive html markup*/
            html += '</span>';
          return html;
        }
      }
    })
;

var $_SESSION;

var tsuruVolks = angular.module('tsuruVolks',["uiSwitch","datatables","vcRecaptcha"]);

tsuruVolks
        .directive('uploaderModel',["$parse",function($parse){
            return{
                restrict:'A',
                link: function(scope, iElement, iAttrs){
                    iElement.on('change',function(e){
                        $parse(iAttrs.uploaderModel).assign(scope, iElement[0].files[0]);
                    });
                }
            };
        }])
        .directive('richTextEditor', function() {
            return {
                restrict : "A",
                require : 'ngModel',
                //replace : true,
                transclude : true,
                //template : '<div><textarea></textarea></div>',
                link : function(scope, element, attrs, ctrl) {
        
                  var textarea = element.wysihtml5({toolbar: { fa: true }});
            
                  var editor = textarea.data('wysihtml5').editor;
        
                  // view -> model
                  var synchronize = function() {
                    ctrl.$setViewValue(editor.getValue());
                    scope.$apply();
                  };
                    editor.on('redo:composer', synchronize);
                    editor.on('undo:composer', synchronize);
                    editor.on('paste:composer', synchronize);
                    editor.on('aftercommand:composer', synchronize);
                    editor.on('change:composer', synchronize);
                    editor.on('keyup:composer', synchronize);
        
                    // model -> view
                    ctrl.$render = function() {
                        textarea.html(ctrl.$viewValue);
                        editor.setValue(ctrl.$viewValue);
                    };
            
                  ctrl.$render();
                }
            };
        })
        .directive('inner', function() {
            return {
                restrict: 'A',
                resplace: false,
                link: function(scope, element, attr) {
                    //element es un elemento jquery
                    var e = element;
    
                    attr.$observe('inner', function(data) {
                        e.html(attr.inner);
                    }, true)
                }
            };
        })
        .directive('convertToString', function() {
            return {
                require: 'ngModel',
                link: function($scope, element, attrs, ngModel) {
                        ngModel.$parsers.push(function(value) {
                                
                                return parseFloat(value);
                        });
                        ngModel.$formatters.push(function(value) {
                            
                                return '' + value;
                        });
                }
            };
        })
        .directive('converttoNumber',function(){
            return{
                    require: 'ngModel',
                    link: function($scope, element, attrs, ngModel) {
                        ngModel.$parsers.push(function(value) {
                            console.log(value)
                                        return '' + value;
                        });
                        ngModel.$formatters.push(function(value) {
                            console.log(value)
                                        return parseFloat(value);
                        });
                    }
            }
        })
        .service("srvPaginacion",function(){
            this.getPaginacion = ()=>{
                
            }
        })
        .controller('asideCtrl',asideCtrl);

function asideCtrl($scope,$http){
    var obj = $scope;
    obj.data = {
        opc:""
   }
    obj.username;
    obj.srcimagen ="Images/boxed-bg.jpg";
    
    obj.btnPrueba = function(){
        confirm("Esta es una prueba");
    }

    obj.usrCON =()=>{
        obj.data.opc= "usrCON";
        $http({
            method: 'POST',
                url: "././Modulo/Home/Ajax/Home.php",
                data: {home:obj.data}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.datos = res.data;
                    
                    if(obj.datos.Usuarios.Estatus == '0'){
                        location.href="../terminar.php";
                    }
                }else{
                    toastr.error(res.data.mensaje);
                }
            
            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }
    obj.usrCON();
}

if(window.location.href.includes("?mod=RepProductos")){
    console.log("Reportes");
}else{
    if(localStorage.getItem("dateNew")){
        localStorage.removeItem("dateNew");
        localStorage.removeItem("dateOld");
    }
}

mensaje = function(title,tipo,txtmsg){
    new PNotify({
        title: title,
        text: txtmsg,
        type: tipo,
        styling: 'bootstrap3'
    });
};

function getSession(){
    $.ajax({
        url: "./modulo/home/Ajax/session.php",
        type: "POST",
        data: {opc:"buscar"},
        beforeSend: function(){
            
        },
        success: function(e){
           $_SESSION = JSON.parse(e);
            
        }
    });
}

//getSession();


