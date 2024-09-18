var url = "./Modulo/Control/Contacto/Ajax/Contacto.php";

tsuruVolks
        .controller('ContactoCtrl', ["$scope","$http",ContactoCtrl]);

function ContactoCtrl($scope, $http){
    var obj = $scope;
    obj.id = 0;
    obj.datos = {};
    
    obj.data = {
        opc:"",
        id:"",
        historico : false
    }
    obj.getContactos = ()=>{
        obj.data.opc = "get";
        obj.sendData();
    }

    obj.getContacto = ()=>{
        obj.data.opc = "set";
        obj.sendData();
    }

    obj.viewMsg = (_id)=>{
        location.href="?mod=Contacto&opc=detalles&id="+_id;
    }

    obj.btnRegresar = ()=>{
        location.href="?mod=Contacto";
    }
    
    obj.sendData = ()=>{
        $http({
            method: 'POST',
                url: url,
                data: {contacto:obj.data}
            }).then(function successCallback(res){
                if(res.data.Bandera == 1){
                    obj.datos = res.data; 
                }else{
                    toastr.error(res.data.mensaje);
                }


            }, function errorCallback(res){
                toastr.error("Error: no se realizo la conexion con el servidor");
        });
    }

    angular.element(document).ready(function(){
        if(obj.data.id != ""){
            obj.getContacto();    
        }else{
            obj.getContactos();
        }
        
    });
}
