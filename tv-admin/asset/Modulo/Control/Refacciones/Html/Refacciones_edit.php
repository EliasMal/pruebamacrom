<div ng-controller="RefaccionesEditCtrl">
    <div class="content-header" ng-init="refaccion.id = {id}">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><i class="fa fa-truck"></i> Refacciones</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="?mod=home">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="?mod=Refacciones"> Refacciones</a></li>
                            <li class="breadcrumb-item active">Editar Refacción</li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <section class="col-xs-12 col-xl-12 col-sm-12 col-md-12 col-lg-12 connectedSortable">
                    <div class="card card-danger">
                        <div class="card-header ">
                            <h3 class="card-title ">
                                <i class="fa fa-users mr-1"></i>
                                Editar Refaccion
                            </h3>
                            <div class="card-tools">
                                <button class="btn btn-tool" data-card-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>

                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-sm-12 col-md-4 col-lg-4 col-xl-4">
                                    <section class="text-center">
                                        <img ng-src="{{img}}" alt="imagen refaccion" class="img-thumbnail" ng-style="backgroudimg">
                                    </section>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-12 col-lg-12">
                                            <label for="tagtitle">Tag title:</label>
                                            <input type="text" class="form-control" name="tagtitle" id="tagtitle" ng-model="refaccion.tag_title" ng-disabled="habilitado">
                                        </div>
                                        <div class="col-md-12 col-lg-12">
                                            <label for="tagalt">Tag alt:</label>
                                            <input type="text" class="form-control" name="tagalt" id="tagalt" ng-model="refaccion.tag_alt" ng-disabled="habilitado">
                                        </div>
                                        <div class="col-md-12 col-lg-12">
                                            <label for="">Producto Creado:</label>
                                            <input type="text" class="form-control" value="{{refaccion.userCreated}}, {{refaccion.dateCreated}}" disabled/>
                                        </div>
                                        <div class="col-md-12 col-lg-12">
                                            <label for="">Ultimo en modificar:</label>
                                            <input type="text" class="form-control" value="{{refaccion.userModify}}, {{refaccion.dateModify}}" disabled/>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-12 col-md-8 col-lg-8 col-xl-8 mt-2">
                                    <ul class="nav nav-tabs" id="tabs" role="tablist">
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link active" id="Datosgenerales-tab" data-toggle="tab" href="#Datosgenerales" 
                                            role="tab" aria-controls="Datosgenerales" aria-selected="true"> Datos Generales</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="Galeria-tab" data-toggle="tab" href="#Galeria" 
                                            role="tab" aria-controls="Galeria" aria-selected="true">Galeria</a>
                                        </li>
                                        <li class="nav-item" role="presentation">
                                            <a class="nav-link" id="Galeria-tab" data-toggle="tab" href="#Vehiculo" 
                                            role="tab" aria-controls="Galeria" aria-selected="true">Vehiculos</a>
                                        </li>
                                        <li class="nav-item" role="presentation" ng-show="isAdmin">
                                            <a class="nav-link" id="Galeria-tab" data-toggle="tab" href="#Actividad" 
                                            role="tab" aria-controls="Galeria" aria-selected="true">Actividad <i class="fas fa-chart-line"></i></a>
                                        </li>
                                    </ul>
                                    <div class="tab-content" id="myTabContent">
                                        <div class="tab-pane fade show active" id="Datosgenerales" role="tabpanel" aria-labelledby="Datosgenerales-tab">
                                            <br>
                                            <div class="row">
                                                
                                                <div class="col-md-12 col-lg-12">
                                                    <form name="frmrefaccion" id="frmrefaccion" novalidate>
                                                        <h4>Datos generales del producto</h4>
                                                        <div class="row">
                                                            <div class="col-md-4 col-lg-4">
                                                                <label>Clave: <span class="text-danger">*</span></label>
                                                                <input type="text" name="txtclave" id="txtclave" class="form-control" ng-model="refaccion.Clave" ng-disabled="habilitado" required/>
                                                                <!-- <input type="text" name="txtclave" id="txtclave" class="form-control" ng-model="refaccion.Clave" ng-disabled="habilitado" ng-model-options="{debounce:1000}" ng-change="refaccionModificada()" required/> -->
                                                                <!--<button class="btn btn-primary" ng-disabled="habilitado" ng-click="Buscarproducto">Buscar</button>-->
                                                            </div>
                                                            <div class="col-md-8 col-lg-8">
                                                                <label>Refacción: <span class="text-danger">*</span></label>
                                                                <input type="text" name="txtrafaccion" id="txtrefaccion" class="form-control" ng-model="refaccion.Producto" ng-disabled="habilitado" required/>
                                                            </div>
                                                            <div class="col-md-6 col-lg-6">
                                                                <label>No. Parte: <span class="text-danger">*</span></label>
                                                                <input type="text" name="txtnoparte" id = "txtnoparte" class="form-control" ng-model="refaccion.No_parte" ng-disabled="habilitado" required/>
                                                            </div>
                                                            <div class="col-md-6 col-lg-6">
                                                                <label>Categoria: <span class="text-danger">*</span></label>
                                                                <select name="slccategoria" id="slccategoria" class="form-control" ng-model="refaccion._idCategoria" ng-change="getMarcas()" ng-disabled="habilitado" required>
                                                                    <optgroup label="Selecciona la categoria de la refaccion">
                                                                        <option ng-repeat="categoria in categorias" value="{{categoria._id}}">{{categoria.Categoria}}</option>
                                                                    </optgroup>
                                                                </select>
                                                            </div>
                                                            
                                                           <!--   -->
                                                            <div class="col-md-6 col-lg-6">
                                                                <label>Imagen de la refacción:</label>
                                                                <div class="custom-file">
                                                                    <input type="file" class="custom-file-input" name="txtfile" id="txtfile" uploader-model="refaccion.file" ng-disabled="habilitado"/>
                                                                    <label class="custom-file-label" for="txtfile">Choose file...</label>
                                                                    
                                                                </div>
                                                                
                                                            </div>
                                                            <div class="col-md-6 col-lg-6">
                                                                <label for="slcproveedor">Proveedor</label>
                                                                <select name="slcproveedor" id="slcproveedor" class="form-control" ng-model="refaccion.id_proveedor" ng-disabled="habilitado">
                                                                    <optgroup label="Selecciona el proveedor de la refaccion">
                                                                        <option ng-repeat="prov in Proveedor" value="{{prov._id}}">{{prov.Proveedor}}</option>
                                                                    </optgroup>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <br />
                                                        <div class="row">
                                                            <div class="col-md-4">
                                                                <ul class="nav flex-column">
                                                                <li class="nav-item">
                                                                    <span class="switch switch-sm">
                                                                        <input type="checkbox" class="switch" id="switch-nuevo" name="switch-nuevo" ng-model="refaccion.RefaccionNueva" ng-disabled="habilitado">
                                                                        <label for="switch-nuevo">Refaccion Nueva</label>
                                                                        
                                                                    </span>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <span class="switch switch-sm">
                                                                    <input type="checkbox" class="switch" id="switch-oferta" name="switch-oferta" ng-model="refaccion.RefaccionOferta" ng-disabled="habilitado">
                                                                    <label for="switch-oferta">Refaccion en Oferta</label>
                                                                </span>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <span class="switch switch-sm">
                                                                    <input type="checkbox" class="switch" id="switch-liquidacion" name="switch-liquidacion" ng-model="refaccion.RefaccionLiquidacion" ng-disabled="habilitado" >
                                                                    <label for="switch-liquidacion">Refaccion en Liquidacion</label>
                                                                </span>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <span class="switch switch-sm">
                                                                    <input type="checkbox" class="switch" id="switch-activo" name="switch-activo" ng-model="refaccion.Estatus" ng-disabled="habilitado" >
                                                                    <label for="switch-activo">Refaccion Activa</label>
                                                                </span>
                                                                </li>
                                                                <li class="nav-item">
                                                                    <span class="switch switch-sm">
                                                                    <input type="checkbox" class="switch" id="switch-envio" name="switch-envio" ng-model="refaccion.Enviogratis" ng-disabled="habilitado" >
                                                                    <label for="switch-envio">Envio Gratis</label>
                                                                </span>
                                                                </li>
                                                                <li class="nav-item" ng-show="isAdmin">
                                                                    <span class="switch switch-sm">
                                                                        <input type="checkbox" class="switch" id="switch-publicar" name="switch-publicar" ng-model="refaccion.Publicar" ng-disabled="habilitado" >
                                                                        <label for="switch-publicar">Publicar</label>
                                                                    </span>
                                                                </li>
                
                                                            </ul>
                                                            </div>
                                                            <div class="col-md-3">
        
                                                            </div>
                                                            <div class="col-md-5">
                                                                <label>Precio Publico: <span class="text-danger">*</span></label>
                                                                <input type="text" name="txtpreciopublico" id="txtpŕeciopublico" class="form-control numeric" ng-model="refaccion.Precio1" ng-disabled="habilitado" required/>
                                                                <label ng-show="refaccion.RefaccionOferta">Precio Oferta:</label>
                                                                <input type="text" name="txtpreciopublico" id="txtpŕeciopublico" class="form-control numeric" ng-show="refaccion.RefaccionOferta" ng-model="refaccion.Precio2" ng-disabled="habilitado"/>
                                                            </div>
                                                        </div>
                                                        <hr />
                                                        <h4>Dimensiones del producto</h4>
                                                        <div class="row">
                                                            <div class="col-md-3 col-lg-3">
                                                                <label>Alto</label>
                                                                <div class="input-group mb-2">
                                                                    <input type="text" convert-to-number name="txtalto" id="txtalto" ng-model="refaccion.Alto" class="form-control numeric" ng-disabled="habilitado" />
                                                                    <div class="input-group-append">
                                                                        <div class="input-group-text">cm.</div>
                                                                    </div>
                                                                </div>
                                                                
                                                            </div>
                                                            <div class="col-md-3 col-lg-3">
                                                                <label>Largo</label>
                                                                <div class="input-group mb-2">
                                                                    <input type="text" convert-to-number name="txtlargo" id="txtlargo" ng-model="refaccion.Largo" class="form-control numeric" ng-disabled="habilitado" />
                                                                    <div class="input-group-append">
                                                                        <div class="input-group-text">cm.</div>
                                                                    </div>
                                                                </div>
                                                                
                                                            </div>
                                                            <div class="col-md-3 col-lg-3">
                                                                <label>Ancho</label>
                                                                <div class="input-group mb-2">
                                                                    <input type="text" convert-to-number name="txtancho" id="txtancho" ng-model="refaccion.Ancho" class="form-control numeric" ng-disabled="habilitado" />
                                                                    <div class="input-group-append">
                                                                        <div class="input-group-text">cm.</div>
                                                                    </div>
                                                                </div>
                                                                
                                                            </div>
                                                            <div class="col-md-3 col-lg-3">
                                                                <label>Peso</label>
                                                                <div class="input-group mb-2">
                                                                    <input type="text" convert-to-number name="txtpeso" id="txtpeso" ng-model="refaccion.Peso" class="form-control numeric" ng-disabled="habilitado" />
                                                                    <div class="input-group-append">
                                                                        <div class="input-group-text">Kg.</div>
                                                                    </div>
                                                                </div>
                                                                
                                                            </div>
                                                        </div>
                                                        <hr />
                                                        <h4>Producto en sucursales</h4>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="text-center">Refacciones encontradas en existencia: <b>{{exisTotales}}</b></div>
                                                                <table class="table table-bordered table-striped">
                                                                    <thead>
                                                                        <th>Sucursal</th>
                                                                        <th>Existencias</th>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr ng-repeat="exis in existencias.Table">
                                                                            <td>{{exis.em_nombre}}</td>
                                                                            <td>{{exis.existencia}}</td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <hr />
                                                        <h4>Detalles</h4>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <label>Descripcion de la Refaccion:</label>
                                                                <textarea name="txtdescripcion" id="txtdescripcion" cols="30" rows="5" class="form-control" ng-model="refaccion.Descripcion" ng-disabled="habilitado"></textarea>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 col-lg-12 mt-3">
                                                    <button type="button" class="btn btn-primary float-right" 
                                                    ng-disabled="frmrefaccion.$invalid" ng-click="habilitado? btnEditarRefaccion() : btnSaveRefaccion()">
                                                    <i class="fa fa-save"></i> {{habilitado? "Editar":"Guardar cambios"}}</button>&nbsp;
                                                    <button type="button" class="btn btn-secondary float-right mr-2" ng-click="btnRegresar()" > Cancelar</button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="tab-pane fade " id="Galeria" role="tabpanel" aria-labelledby="Galeria-tab">
                                            <br>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-xl-4 col-md-4 col-lg-4" ng-repeat="gal in dataGaleria">
                                                    <div class="card card-danger card-outline">
                                                        <div class="card-body">
                                                            <img ng-src="{{getImagen(gal)}}"
                                                                alt="{{gal.tag_alt}}" title="{{gal.tag_title}}" class="img-fluid pad">
                                                            <hr>
                                                            
                                                            <button class="btn btn-sm btn-outline-danger pull-right"
                                                                type="button"
                                                                ng-click="btnEliminarImagen(gal._id)">Desactivar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                            <hr>
                                            <div class="row">
                                                <div class="col-xs-12 col-sm-12 col-xl-12 col-md-12 col-lg-12">
                                                    <button name="btnNuevaCategoria" ng-click="btnNuevaCategoria()" class="btn btn-danger">Nueva Galeria</button>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="tab-pane fade " id="Vehiculo" role="tabpanel" aria-labelledby="Galeria-tab">
                                            <div class="col-md-12 col-lg-12 mt-2">
                                                <button class="btn btn-outline-danger mb-2" ng-click="btnNuevoVehiculo()"><i class="fa fa-car"></i> Nuevo Vehiculo</button>
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th width="15%" class="text-center">Marca</th>
                                                                <th width="15%" class="text-center">Modelo</th>
                                                                <th width="15%" class="text-center">Desde</th>
                                                                <th width="15%" class="text-center">Hasta</th>
                                                                <th width="15%" class="text-center">Transmision</th>
                                                                <th width="15%" class="text-center">Accion</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr ng-repeat="RV in Compatibilidad" ng-show="RV.clave == refaccion.Clave" class="center--text">
                                                                <td>{{RV.Marca}}</td>
                                                                <td>{{RV.Modelo}}</td>
                                                                <td>{{RV.ainicial}}</td>
                                                                <td class="text-center">{{RV.afinal}}</td>
                                                                <td class="text-center">{{RV.transmision}}</td>
                                                                <td class="text-center">
                                                                    <div class="dropdown d-block d-sm-none">
                                                                        <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-expanded="false">
                                                                          Dropdown button
                                                                        </button>
                                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                                          <a class="dropdown-item" href="#">Action</a>
                                                                          <a class="dropdown-item" href="#">Another action</a>
                                                                          <a class="dropdown-item" href="#">Something else here</a>
                                                                        </div>
                                                                      </div>
                                                                    <section class="d-none d-sm-block">
                                                                        <button  class="btn btn-danger" ng-click="btnBorrarRvehiculo(RV)"> <i class="fa fa-close"></i></button>  
                                                                    </section>
                                                                    
                                                                </td>
                                                            </tr>
                                        
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            
                                        </div>

                                        <div class="tab-pane fade " id="Actividad" role="tabpanel" aria-labelledby="Galeria-tab" ng-show="isAdmin">
                                            <div class="col-md-12 col-lg-12 mt-2">
                                                <div class="table-responsive">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th width="15%" class="text-center">Usuario</th>
                                                                <th width="15%" class="text-center">Modificó</th>
                                                                <th width="15%" class="text-center">Fecha</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>

                                                            <tr ng-repeat="act in actividad" class="center--text">
                                                                <td>{{act.usuario}}</td>
                                                                <td><p class="datosdiff_txt">{{act.datosdiff}}</p></td>
                                                                <td>{{act.fecha_modificacion}}</td>
                                                            </tr>
                                        
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        
                                    </div>
                                    
                                </div>
                            </div>

                        </div>
                    </div>
                </section>
            </div>
        </div>
    </section>
    <!-- Modal -->
    <div class="modal fade" id="Mcategoria" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{nuevo? "Nuevo":"Editar"}} Imagen</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-4" aling="center">
                            <img ng-src="{{imgGaleria}}" alt="imagen proveedor" class="img-responsive img-thumbnail" />
                        </div>
                        <div class="col-lg-8">
                            <label for="imagenprincipal">Subir Imagen</label>
                            <div class="input-group">
                                <div class="custom-file">
                                    <input class="custom-file-input archivos" id="Principal" type="file"
                                        uploader-model="Galeria.file">
                                    <label for="imagenprincipal"
                                        class="custom-file-label">{{Galeria.Categoria==="Principal"? Galeria.name:Galeria.placeholder}}</label>
                                </div>
                            </div>
                            <label for="slcMarca">Tag Title</label>
                            <input type="text" name="txttitle" id="txttitle" class="form-control" ng-model="Galeria.tag_title" />
                            <label for="slcMarca">Tag alt</label>
                            <input type="text" name="txtalt" id="txtalt" class="form-control" ng-model="Galeria.tag_alt" />

                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" ng-click="btnsubirimagen()" ng-disabled="btnsave">
                        {{nuevo? "Subir":"Guardar"}} Imagen</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
    <div class="modal fade" id="mdlVehiculo" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalCenterTitle">{{nuevo? "Nuevo":"Editar"}} Vehiculo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="subir__vehiculo">
                        <form action="Modulo/Control/Refacciones/Ajax/Vehiculos.php" method="POST" >
                            <select name="clave" class="form-control d-grid" ng-model="clave">
                                <optgroup>
                                    <option disabled selected>Selecciona la Clave</option>
                                    <option value="{{refaccion.Clave}}">{{refaccion.Clave}}</option>
                                </optgroup>
                            </select>

                            <select name="id_imagen" class="form-control d-grid" ng-model="id_imagen">
                                <optgroup>
                                    <option disabled selected>Selecciona la ID</option>
                                    <option value="{{refaccion._id}}">{{refaccion._id}}</option>
                                </optgroup>
                            </select>

                            <select name="idmarca" ng-model="vehiculo.id_Marca_RefaccionVehiculo" ng-change="getVehiculos(vehiculo.id_Marca_RefaccionVehiculo)" class="form-control d-grid">
                                <optgroup>
                                    <option disabled selected>Selecciona Marca</option>
                                    <option ng-repeat="marca in Marcas" value="{{marca._id}}">{{marca.Marca}}</option>
                                </optgroup>
                            </select>

                            <select name="idmodelo" ng-model="vehiculo.id_Modelo_RefaccionVehiculo" ng-change="getModelos(vehiculo.id_Modelo_RefaccionVehiculo); getAnios(vehiculo.id_Modelo_RefaccionVehiculo)" class="form-control d-grid">
                                <optgroup>
                                    <option disabled selected>Selecciona Modelo</option>
                                    <option ng-repeat="vehiculo in Vehiculos" value="{{vehiculo._id}}">{{vehiculo.Modelo}}</option>
                                </optgroup>
                            </select>
                            <input type="text" name="generacion" placeholder="generacion" class="form-control d-grid" ng-model="generacion">
                            <input type="text" name="ainicial" placeholder="año inicial" class="form-control d-grid" ng-model="ainicial" required>
                            <input type="text" name="afinal" placeholder="año final" class="form-control d-grid" ng-model="afinal" required>
                            <input type="text" name="motor" placeholder="motor" class="form-control d-grid" ng-model="motor" required>
                            <input type="text" name="transmision" placeholder="transmision" class="form-control d-grid" ng-model="transmision" required>
                            <input type="text" name="especificaciones" placeholder="especificaciones" class="form-control d-grid" ng-model="especificaciones">
                            <input type="submit" value="Agregar" class="form-control compatibilidad__agregar">
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>

</div>
