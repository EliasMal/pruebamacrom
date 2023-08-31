<div ng-controller="RefaccionesNewCtrl">
    <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 text-dark"><i class="fa fa-truck"></i> Refacciones</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="?mod=home">Inicio</a></li>
                            <li class="breadcrumb-item"><a href="?mod=Refacciones"> Refacciones</a></li>
                            <li class="breadcrumb-item active">Nueva Refacción</li>
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
                        <div class="card-header d-flex p-0">
                            <h3 class="card-title p-3">
                                <i class="fa fa-users mr-1"></i>
                                Agregar nueva Refaccion
                            </h3>
                            <div class="card-tools">
                                <button class="btn btn-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>

                        </div><!-- /.card-header -->
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 col-lg-4" >
                                    <section class="text-center" >
                                    
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
                                    </div>
                                </div>
                                <div class="col-md-8 col-lg-8">
                                    <form name="frmrefaccion" id="frmrefaccion" novalidate>
                                        <h4>Datos generales del producto</h4>
                                        <hr />
                                        <div class="row">
                                            <div class="col-md-4 col-lg-4">
                                                <label>Clave: <span class="text-danger">*</span></label>
                                                <input type="number" name="txtclave" id="txtclave" class="form-control" ng-model="refaccion.Clave"  ng-model-options="{debounce:500}" ng-change="getArticulovolks()" ng-disabled="habilitado" required/>
                                            </div>
                                            <div class="col-md-8 col-lg-8">
                                                <label>Refacción: <span class="text-danger">*</span></label>
                                                <input type="text" name="txtrafaccion" id="txtrefaccion" class="form-control" ng-model="refaccion.refaccion" ng-disabled="habilitado" required/>
                                            </div>
                                            <div class="col-md-6 col-lg-6">
                                                <label>No. Parte: <span class="text-danger">*</span></label>
                                                <input type="text" name="txtnoparte" id = "txtnoparte" class="form-control" ng-model="refaccion.noParte" ng-disabled="habilitado" required/>
                                            </div>
                                            <div class="col-md-6 col-lg-6">
                                                <label>Categoria: <span class="text-danger">*</span></label>
                                                <select name="slccategoria" id="slccategoria" class="form-control" ng-model="refaccion.Categoria" ng-change="getMarcas()" ng-disabled="habilitado" required>
                                                    <optgroup label="Selecciona la categoria de la refaccion">
                                                        <option ng-repeat="categoria in categorias" value="{{categoria._id}}">{{categoria.Categoria}}</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                            <div class="col-md-6 col-lg-6">
                                                <label>Marca del Vehiculo: <span class="text-danger">*</span></label>
                                                <select name="slcmarcav" id="slcmarcav" class="form-control" ng-model = "refaccion.Marca" ng-change="getVehiculos()" ng-disabled="habilitado" required>
                                                    <optgroup label="Selecciona la marca del vehiculo">
                                                        <option ng-repeat="marca in Marcas" value="{{marca._id}}">{{marca.Marca}}</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                            <div class="col-md-6 col-lg-6">
                                                <label>Vehiculo: <span class="text-danger">*</span></label>
                                                <select name="slcvehiculo" id="slcvehiculo" class="form-control" ng-model = "refaccion.Vehiculo" ng-change="getModelos()" ng-disabled="habilitado" required>
                                                    <optgroup label="Selecciona el vehiculo">
                                                        <option ng-repeat="vehiculo in Vehiculos" value="{{vehiculo._id}}">{{vehiculo.Modelo}}</option>
                                                    </optgroup>
                                                </select>
                                            </div>
                                            <div class="col-md-6 col-lg-6">
                                                <label>Modelo: <span class="text-danger">*</span></label>
                                                <select name="slcmodelo" id="slcmodelo" class="form-control" ng-model="refaccion.Modelo" ng-disabled="habilitado" required>
                                                    <optgroup label="Selecciona el año del Vehiculo">
                                                        <option ng-repeat="modelo in Modelos" value="{{modelo._id}}">{{modelo.Anio}}</option>
                                                    </optgroup>
                                                </select>
                                            </div>
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
                                                            <input type="checkbox" class="switch" id="switch-nuevo" name="switch-nuevo" ng-model="refaccion.Nuevo" ng-disabled="habilitado">
                                                            <label for="switch-nuevo">Refaccion Nueva</label>
                                                        </span>
                                                    </li>
                                                    <li class="nav-item">
                                                        <span class="switch switch-sm">
                                                        <input type="checkbox" class="switch" id="switch-oferta" name="switch-oferta" ng-model="refaccion.Oferta" ng-disabled="habilitado">
                                                        <label for="switch-oferta">Refaccion en Oferta</label>
                                                    </span>
                                                    </li>
                                                    <li class="nav-item">
                                                        <span class="switch switch-sm">
                                                        <input type="checkbox" class="switch" id="switch-liquidacion" name="switch-liquidacion" ng-model="refaccion.liquidacion" ng-disabled="habilitado">
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
                                                </ul>
                                            </div>
                                            <div class="col-md-3">
                                                <ul class="nav flex-column">
                                                    <li class="nav-item">
                                                        
                                                        <div class="input-group" >
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                              </div>
                                                            <input ng-disabled="!refaccion.Nuevo" type="text" name="cldrefnueva" id="cldrefnueva" 
                                                            class="form-control calendario" ng-model="refaccion.fechaNueva" placeholder="0000-00-00"> 
                                                        </div>
                                                       
                                                    </li>
                                                    <li class="nav-item">
                                                        <div class="input-group" >
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text" id="basic-addon1">
                                                                    <i class="fa fa-calendar"></i>
                                                                </span>
                                                            </div>
                                                            <input ng-disabled="!refaccion.Oferta" type="text" name="cldrefoferta" id="cldrefoferta" 
                                                            class="form-control calendario" ng-model="refaccion.fechaOferta" placeholder="0000-00-00"> 
                                                        </div>
                                                       
                                                    </li>
                                                    <li class="nav-item">

                                                    </li>
                                                    <li class="nav-item">

                                                    </li>
                                                </ul>


                                            </div>
                                            <div class="col-md-5">
                                                <label>Precio Publico: <span class="text-danger">*</span></label>
                                                <input type="number" name="txtpreciopublico" id="txtpŕeciopublico" class="form-control numeric" 
                                                ng-model="refaccion.Precio1" ng-disabled="habilitado" required/>
                                                <label ng-show="refaccion.Oferta">Precio Oferta:</label>
                                                <input type="number" name="txtpreciopublico" id="txtpŕeciopublico" class="form-control numeric" 
                                                ng-show="refaccion.Oferta" ng-model="refaccion.Precio2" ng-disabled="habilitado"/>
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
                                        <div class="row">
                                            <div class="col-md-12">
                                                <label>Descripcion de la Refaccion:</label>
                                                <textarea name="txtdescripcion" id="txtdescripcion" cols="30" rows="5" class="form-control" ng-model="refaccion.Descripcion" ng-disabled="habilitado"></textarea>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button type="button" class="btn btn-primary pull-right" ng-disabled="frmrefaccion.$invalid" ng-click="habilitado? btnNuevaRefaccion() : btnGuardarRefaccion()"><i class="fa fa-save"></i> {{habilitado? "Nuevo":"Guardar"}}</button>&nbsp;
                            <button type="button" class="btn btn-secondary pull-right" ng-click="btnRegresar()"> Cancelar</button>

                        </div>
                    </div>
                </section>
            </div>
    <!--        <div class="row">
                <div class="col lg 12">
                    <button type="button" class="btn btn-danger" ng-click="btnAgregarRefaccion()"><i class="fa fa-plus-square"></i> Agregar Refaccion</button>
                    <span class="switch switch-sm pull-right">
                        <input type="checkbox" class="switch" id="switch-id" ng-model="historico" ng-click="getRefacciones()">
                        <label for="switch-id">Historico</label>
                    </span>
                </div>
            </div>
            <hr />
            <div class="row">
                <div class="col-lg-12">
                    <input type="text" name="txtbuscar" id="txtbuscar" placeholder="Ingresa el codigo o nombre de la refaccion a buscar ...." class="form-control"/>
                </div>
            </div>-->
        </div>
    </section>
</div>