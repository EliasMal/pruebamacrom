<html>
	<head>
	<meta charset="UTF-8">
	</head>

	<body>
		<div class="contenedor contenedor-principal global__content" ng-controller="ComprasCtrl">
			<form name="frmcompra" id="frmcompra" novalidate>
				<main class="compras">
					<article class="cliente" ng-controller="ProfileCtrl" ng-init="pag='Direcciones'">
						<div>
							<h4 class="cliente__titulo txtred">Dirección Entrega</h4>
							<div class="cliente__datos" ng-repeat="dire in profile.arrayDomicilios" ng-show="dire.Predeterminado == 1" >
								<p>{{Costumer.profile.nombres}} {{Costumer.profile.Apellidos}}.</p>
								<p>{{dire.Domicilio}} #{{dire.numExt}}, {{dire.Colonia}}.</p>
								<p>{{dire.Ciudad}}, {{dire.Estado}}, {{dire.Codigo_postal}}.</p>
								<p>{{dire.Referencia}}.</p>
								<p>Tel: {{dire.Telefono}}</p>
							</div>
							<div class="cliente__datos" ng-show="profile.arrayDomicilios[0].Predeterminado != 1" >
								<p>{{Costumer.profile.nombres}} {{Costumer.profile.Apellidos}}.</p>
								<p><b>NOTA:</b> Agrega o selecciona una direccion para seguir con tu compra.</p>
							</div>
						</div>
						<div class="cliente__opciones" ng-controller="ProfileCtrl" ng-init="pag='Direcciones'">
											<div class="cliente__opciones--editar" ng-show="profile.arrayDomicilios[0].Predeterminado != 0">
												<button id="abrirModal" class="form-control fs-12 m-t-10 cliente__opciones--button" ng-click="btneditDomicilio(Costumer.dataDomicilio.data._id)" ng-show="profile.arrayDomicilios[0]"><img style="height:12px;" src="../images/icons/Editar.svg">&nbsp;&nbsp;Editar Dirección</button>
											</div>
											<div class="cliente__opciones--otra" ng-show="profile.arrayDomicilios[0]">
												<button id="abrirModal1" class="form-control fs-12 m-t-10 cliente__opciones--button"><img style="height:12px;" src="../images/icons/Seleccionar.svg">&nbsp;&nbsp;Seleccionar Otra</button>
											</div>
											<div class="cliente__opciones--nueva">
												<button id="abrirModal2" class="form-control fs-12 m-t-10 cliente__opciones--button"><img style="height:12px; margin-left:-10px" src="../images/icons/Agregar.svg">&nbsp;&nbsp;Agregar Nueva</button>
											</div>
									</div>
					</article>

					<article class="productos">
						<h4 class="cliente__titulo txtred center--text">Productos</h4>
						<p class="center--text txtblack">Tienes {{Numproducts}} articulo(s) en el carrito.</p>
						<div class="productos__contenedor">
							<div class="productos__datos--contenedor" ng-repeat="product in session.CarritoPrueba">
								<div class="productos__datos">
									<div class="productos__datos--img">
										<img ng-src="{{getImagen(product.imagenid)}}" alt="IMG" ng-click="btnEliminarRefaccion(product, true)" >
									</div>
									<div class="productos__datos--info">
										<a ng-click="RefaccionDetalles(product.imagenid)" class="productos__datos--info--articulo">{{product._producto}}</a>
										<p>No.Parte: {{product.No_parte}}</p>
										<p>{{product.Cantidad}} x {{product.Precio |currency}}</p>
										<div class="productos__datos--contador">
											<div class="agregarmas no-overflow">
								
												<button class="agregarmas__botones" ng-click="btnQuitar(product)">
													<i class="fs-12 fa fa-minus" aria-hidden="true"></i>
												</button>
												<div class="agregarmas__contador num-product center--text">
													<span >{{product.Cantidad}}</span>
												</div>
												<button class="agregarmas__botones" ng-click="btnAgregar(product)">
													<i class="fs-12 fa fa-plus" aria-hidden="true"></i>
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<hr>
					</article>
				</main>

				<main class="pagos">
					<article class="pagos__facturacion">
						<div>
							<h4 class="pagos__facturacion--titulo txtred">Facturación</h4>
							<div class="cliente__datos" ng-show="Costumer.facturacion == 1">
								<p>Actividad Empresarial: Persona Fisica.</p>
								<p>Razon Social: {{Costumer.dataFacturacion.data.Razonsocial}}.</p>
								<p>RFC: {{Costumer.dataFacturacion.data.Rfc}}</p>
								<p class="p-t-30">Uso de CFDI: {{Costumer.dataFacturacion.data.usocfdi}}</p>
							</div>
							<div class="cliente__datos" ng-show="Costumer.facturacion != 1" >
								<p>{{Costumer.profile.nombres}} {{Costumer.profile.Apellidos}}.</p>
								<p><b>NOTA:</b> Si posterior a esta compra deseas facturar tu pedido, es necesario solicitarla el mismo dia de tu compra, en caso de lo contrario se genera con un RFC generico.</p>
							</div>
						</div>
						<div class="cliente__opciones">
							<div class="cliente__opciones--editar">
								<button id="abrirModal3" class="form-control fs-12 m-t-10 cliente__opciones--button" ><img style="height:12px;" src="../images/icons/Editar.svg">&nbsp;&nbsp;Seleccionar</button>
							</div>
							<div class="cliente__opciones--otra">
								<button id="abrirModal4" class="form-control fs-12 m-t-10 cliente__opciones--button"><img style="height:12px;" src="../images/icons/Seleccionar.svg">&nbsp;&nbsp;Agregar Nueva</button>
							</div>
							<div class="cliente__opciones--nueva">
								<button id="facturaNo" class="form-control fs-12 m-t-10 cliente__opciones--button" ng-click="facturaNo()"><img style="height:12px; margin-left:-10px" src="../images/icons/Agregar.svg">&nbsp;&nbsp;No Usar Datos</button>
							</div>
						</div>
						
					</article>

					<article class="pagos__productos">
						<div class="pagos__productos--confirmacion">
							<div class="pagos__sitio-seguro">
								<div class="pagos__sitio-seguro--contenedor">
									<div class="pagos__sitio-seguro--info center--text">
										<img src="images/icons/Icono-seguridad.png" alt="" style="width: 50px;">
										<b>Sitio seguro</b>
										<p class="text-center txtblack">Nuestro sitio cuenta con certificaciones de seguridad por parte del MIT y Santander</p>
									</div>
											
								</div>
								<div class="pagos__cupon">
									<div class="cuponcont">
										<input type="text" class="inpcpn" placeholder="Ingresa tu cupón." id="inpCupon" style="text-align: center;" autocomplete="off">
										<button class="form-control" ng-click="cupon()" id="btncupon">Canjear</button>
									</div>
									<span class="cupon--alert" id="alert--cupon"></span>
								</div>
								<div>
									<table class="tablecompra">
										<thead>
											<tr width="50%"></tr>
											<tr width="50%"></tr>
										</thead>
										<tbody>
											<tr>
												<td class="p-r-pz">Productos</td>
													<td  class="text-right">{{subtotal() | currency}}</td>
												</tr>
											<tr ng-show="Costumer.descuento > 0">
												<td class="p-r-pz text-red">Descuento</td>
												<td class="csmdesc text-right">- {{Costumer.descuento | currency}}</td>
											</tr>
											<tr>
												<td>Costo de envio</td>
												<td class="text-right" ng-if="Costumer.Cenvio.Envio == 'L'">
													<span ng-if="dataCotizador.parcel.weight==0">Envio Gratis</span>
													<span ng-if="dataCotizador.parcel.weight>0">{{Costumer.Cenvio.Costo | currency}}</span>
												</td>
												<td ng-if="Costumer.Cenvio.Envio != 'L'" class="text-right">
													<span ng-if="dataCotizador.parcel.weight==0">Envio Gratis</span>
													<span ng-if="dataCotizador.parcel.weight>0">{{Costumer.Cenvio.Costo | currency}}</span>
												</td>
											</tr>
											<tr ng-if="Costumer.Cenvio.Envio == 'N' && dataCotizador.parcel.weight>0">
												<td>Costos de envio</td>
												<td><button class="btn form-control cotizar__button" ng-click="btncotizar()">Cotizar</button></td>
											</tr>
											<tr class="table tbl-cms">
												<td>Total(con iva)</td>
												<td  class="text-right"><h5 class="text-red"><b>{{Ttotal() | currency}}</b></h6></td>
											</tr>
										</tbody>
									</table>
								</div>	
								
								<button class="btn btn-danger bg9 form-control pagar--button" id="btnfacomp" ng-click="btnPagar()" ng-show="subtotal() > 0"> {{session.autentificacion==1? 'Pagar':'Inicia Session'}}</button>
								<p class="fs-12 m-t-10" ng-show="session.autentificacion==1">
									<label class="checkbox ">
										<input type="checkbox" name="aviso" id="aviso" ng-model="Costumer.aviso" required>
										<span class="check"></span>
										<div class="fs-12 p-l-40"> He leido y estoy de acuerdo con las <a href="?mod=Terminos-condiciones" class="text-red fs-12">condiciones generales</a> 
												y el <a href="?mod=aviso-de-privacidad" class="text-red fs-12"> aviso de privacidad</a>
										</div>
									</label>
								</p>
						</div>
					</article>

					<article class="metodo__pago">
						<div class="metodo__pago--contenedor">
							<h4 class="metodo__pago--titulo txtred" >Método de Pago</h4>
							<div class="metodo__pago--metodos">
								<div class="metodo__pago--metodo">
									<button class="metodopago bo18 h-120" id="btncredito" ng-click="metarjeta()">
										<label class="radio2">Tarjeta de crédito / debito</label>
										<img src="images/icons/VISA MASTERCARD.svg" alt="" class ="img-fluid mx-auto d-block" style="width: 150px;">
									</button>	
								</div>

								<div class="metodo__pago--metodo">
									<button class="metodopago bo18 h-120" id="btnefectivo" ng-click="medeposito()">
										<label class="radio2">Deposito en efectivo</label>
										<img src="images/icons/OXXO.svg" alt=""  class ="img-fluid mx-auto d-block" style="width: 150px;">
									</button>
								</div>

								<div class="metodo__pago--metodo">
									<button class="metodopago bo18 h-120" ng-click="metransfe()" id="btntransfe">
										<label class="radio2">Tranferencia bancaria</label>
										<img src="images/icons/SPEI.svg" alt="" class ="img-fluid mx-auto d-block" style="width: 150px;">	
									</button>
								</div>
							</div>
						</div>
					</article>
				</main>

				<section ng-controller="ComprasCtrl">
					<form name="frmcompra" id="frmcompra" novalidate>
						<!-- Ventana modal Editar Dirección, por defecto no visiblel -->
						<div id="ventanaModal" class="modal" ng-controller="ProfileCtrl" ng-init="pag='Direcciones_edit'">
							<div class="ventanaModal__contenido">
								<span class="cerrar enlace">&times;</span>
								<h2 class="text-center text-white p-t-10 bg9 p-b-10">Editar dirección de envío</h2>

								<div class="ventanaModal__contenido--agregar">
									<div class="ventanaModal__contenido--dato">
										<label>Calle: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input value="comer" type="text" ng-model="dataDireccion.Domicilio" required autocomplete="off" placeholder="Ingresa Calle">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar">
									<div class="ventanaModal__contenido--dato">
										<label>Numero: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input ng-model="dataDireccion.numExt" required autocomplete="off" placeholder="Ext.">
									</div>

									<div class="ventanaModal__contenido--dato">
										<label></label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input ng-model="dataDireccion.numInt" required autocomplete="off" placeholder="Int.">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label>Colonia: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input ng-model="dataDireccion.Colonia" required autocomplete="off" placeholder="Ingresa Colonia"> 
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label class="fzm-15">Cod.Postal: <b class="obligatorio">*</b></label>
									</div>
									<div class="ventanaModal__contenido--usuario">
										<input maxlength="5" ng-model="dataDireccion.Codigo_postal" required autocomplete="off" placeholder="Codigo Postal">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label>Ciudad: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input class="text-black inp-dts" ng-model="dataDireccion.Ciudad" required autocomplete="off" placeholder="Ingresa Ciudad">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label>Estado: <b class="obligatorio">*</b></label>
									</div>
									<div class="ventanaModal__contenido--usuario">
										<select name="estado" ng-model="dataDireccion.Estado" id="optcfdi">
											<optgroup label ="Selecciona tu Estado">
												<option ng-repeat="estados in Facturacion.estados" value="{{estados.Descripcion}}">({{estados.estados}}) {{estados.Descripcion}}</option>
											</optgroup>
										</select>
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label>Referencia: </label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input ng-model="dataDireccion.Referencia" required autocomplete="off" placeholder="Ingresa Referencia">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label>Telefono: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input ng-model="dataDireccion.Telefono" required autocomplete="off" placeholder="Ingresa Telefono">
									</div>
								</div>

								<button class="btn btn-danger ventanaModal__contenido--botonguardar" ng-click="btnGuardarDireccion('save')">Guardar</button>
							</div>
						</div> <!--Fin Modal Editar Dirección-->

						<!-- Ventana modal Seleccionar Otra, por defecto no visiblel -->
						<div id="ventanaModal1" class="ventanaModal__contenedor" ng-controller="ProfileCtrl" ng-init="pag='Direcciones'">
							<div class="ventanaModal__contenido">
								<span class="cerrar1">&times;</span>
								<h2 class="center--text">Direcciones de entrega</h2>

								<div class="ventanaModal__contenido--grid" ng-repeat="dire in profile.arrayDomicilios" id="mdl2">
									<div class="ventanaModal__contenido--datos">
										<div class="ventanaModal__contenido--seleccion" ng-show="dire.Predeterminado == 1">Seleccionada</div>
										<div class="ventanaModal__contenido--info">
											<p>{{Costumer.profile.nombres}} {{Costumer.profile.Apellidos}}</p>
											<p>Telefono: {{dire.Telefono}}</p>
										</div>
										<div class="ventanaModal__contenido--info">
											<p>{{dire.Domicilio}} {{dire.numExt}}, {{dire.Colonia}}. </p>
											<p>{{dire.Ciudad}}, {{dire.Estado}}, {{dire.Codigo_postal}}.</p>
											<p>{{dire.Referencia}}</p>
										</div>
									</div>

									<div class="ventanaModal__contenido--opciones">
										<div class="ventanaModal__contenido--boton">
											<button id="abrirModal" class="form-control" ng-click="btnPredeterminado(dire._id)" ng-show="dire.Predeterminado == 0"><img src="../images/icons/Seleccionar.svg">&nbsp;&nbsp;Seleccionar</button>
										</div>

										<div class="ventanaModal__contenido--boton">
											<button id="abrirModal1" class="form-control" ng-click="btndescartarDomicilio(dire._id)"><img src="../images/icons/Basura.svg">&nbsp;&nbsp;Eliminar</button>
										</div>
									</div>

								</div>
							</div>
						</div> <!--Fin Modal Seleccionar Otra-->

						<!-- Ventana modal Agregar Nueva, por defecto no visiblel -->
						<div id="ventanaModal2" class="modal2"  ng-controller="ProfileCtrl" ng-init="pag='Direcciones_add'" >
							<div class="ventanaModal__contenido">
								<span class="cerrar2 enlace">&times;</span>
								<h2 class="center--text">Agregar dirección envío</h2>
				
								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label id="lbladdcalle">Calle: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario" id="divaddcalle">
										<input class="text-black inp-dts" placeholder="Ingresa tu Domicilio" ng-model="dataDireccion.Domicilio" id="inpaddncalle">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label id="lbladdnumext">Numero: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario" id="divaddnumext">
										<input ng-model="dataDireccion.numExt" placeholder="Ext." id="inpaddnumext">
									</div>

									<div class="ventanaModal__contenido--dato">
										<label id="lbladdnumint"></label>
									</div>

									<div class="ventanaModal__contenido--usuario" id="divaddnumint">
										<input ng-model="dataDireccion.numInt" placeholder="Int." id="inpaddnumint">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label id="lbladdcol">Colonia: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario" id="divaddcol">
										<input placeholder="Ingresa tu colonia" ng-model="dataDireccion.Colonia" id="inpaddcol">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label class="fzm-15" id="lbladdcp">Cod.Postal: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario" id="divaddcp">
										<input type="number" placeholder="00000" maxlength="5" ng-model="dataDireccion.Codigo_postal" id="inpaddcp">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label id="lbladdcity">Ciudad: <b class="obligatorio">*</b></label>
									</div>
									<div class="ventanaModal__contenido--usuario" id="divaddcity">
										<input placeholder="Ingresa tu ciudad" ng-model="dataDireccion.Ciudad" id="inpaddcity">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label id="lbladdest">Estado: <b class="obligatorio">*</b></label>
									</div>
									<div class="ventanaModal__contenido--usuario" id="divaddest">
										<input placeholder="Ingresa tu estado" ng-model="dataDireccion.Estado" id="inpaddest">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label>Referencia: </label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input placeholder="Referencia de domicilio" ng-model="dataDireccion.Referencia">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" >
									<div class="ventanaModal__contenido--dato">
										<label id="lbladdtel">Telefono: <b class="obligatorio">*</b></label>
									</div>
									<div class="ventanaModal__contenido--usuario" id="divaddtel">
										<input type="number" placeholder="Ingresa numero de Telefono" ng-model="dataDireccion.Telefono" id="inpaddtel">
									</div>
								</div>
								<span id="alertvalid" class="ventanaModal__contenido--alerta"><img src="images/icono notificacion.svg" alt="icon-user" style="width: 32px;padding-right: 2px;">Completa todos los datos para poder continuar</span>
								<button class="btn btn-danger ventanaModal__contenido--botonguardar" ng-click="inputvalidireccion()">Guardar</button>
							</div>
						</div> <!--Fin Modal Agregar Nueva-->


						<!-- Ventana modal Seleccionar Otra Facturacion, por defecto no visiblel -->
						<div id="ventanaModal3" class="modal3" ng-controller="ProfileCtrl" ng-init="pag='Facturacion'">
							<div class="ventanaModal__contenido">
								<span class="cerrar3">&times;</span>
								<h2 class="center--text">Información Facturación</h2>

								<div class="ventanaModal__contenido--grid" ng-repeat="d in Facturacion.dataFacturacion |filter: buscar | orderBy:'Predeterminado':true">
									<div class="ventanaModal__contenido--datos">
										<div class="ventanaModal__contenido--seleccion" ng-show="d.Predeterminado == 1">Seleccionada</div>
										<div class="ventanaModal__contenido--info">
											<p>Actividad Empresarial: {{d.Actividad}}.</p>
											<p>Razon Social: {{d.Razonsocial}}.</p>
											<p>RFC: {{d.Rfc}}</p>
											<p>Uso de CFDI: {{d.Descripcion}}</p>
										</div>
									</div>

									<div class="ventanaModal__contenido--info">
										<div class="ventanaModal__contenido--boton">
											<button class="form-control" name="facturacion" id="factsi" ng-click="btnFacpredetermiando(d._id, 'pre')"><img src="../images/icons/Seleccionar.svg">&nbsp;&nbsp;Seleccionar</button>
										</div>
										<div class="ventanaModal__contenido--boton">
											<button class="form-control" ng-click="btnEliminardatosfacturacion(d._id, 'del')"><img src="../images/icons/Basura.svg">&nbsp;&nbsp;Eliminar</button>
										</div>
									</div>

								</div>
							</div>
						</div> <!--Fin Modal Seleccionar Otra Facturacion-->

						<!-- Ventana modal Agregar Nueva Factura, por defecto no visiblel -->
						<div id="ventanaModal4" class="modal4" ng-controller="ProfileCtrl" ng-init="pag='Facturacion_add'">
							<form name="Facturacionform" novalidate>
								<div class="ventanaModal__contenido">
									<span class="cerrar4 enlace">&times;</span>
									<h2 class="center--text">Agregar Nuevos Datos</h2>
										
									<div class="ventanaModal__contenido--agregar" >
										<div class="ventanaModal__contenido--dato">
											<label id="agraem">Actividad Empresarial: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario">
											<select name="Actividad" ng-model="dataFacturacion.Actividad">
												<optgroup label ="Seleccion la actividad de la empresa">
													<option ng-repeat="Acti in Facturacion.Actividad" value="{{Acti.valor}}" id="novenoInput">{{Acti.valor}}</option>
												</optgroup>
											</select>
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" >
										<div class="ventanaModal__contenido--dato">
											<label id="Agrs">R.Social: <b class="obligatorio">*</b></label>
										</div>
										<div class="ventanaModal__contenido--usuario" id="dveline">
											<input name="razon" type="text" placeholder="Introduce Razon Social" ng-model="dataFacturacion.Razonsocial" id="segundoInput" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" >
										<div class="ventanaModal__contenido--dato">
											<label id="Agrfc">RFC: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario" id="dveline1">
											<input name="RFC" type="text" placeholder="Ingresa tu RFC" ng-model="dataFacturacion.Rfc" id="primerInput" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" >
										<div class="ventanaModal__contenido--dato">
											<label id="Agrdm">Domicilio: <b class="obligatorio">*</b></label>
										</div>
										<div class="ventanaModal__contenido--usuario" id="dveline3">
											<input name="domicilio" type="text" placeholder="Ingresa calle y numero" ng-model="dataFacturacion.Domicilio" id="terceroInput" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" >
										<div class="ventanaModal__contenido--dato">
											<label id="Agrcol">Colonia: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario" id="dveline4">
											<input name="colonia" type="text" placeholder="Ingresa tu colonia" ng-model="dataFacturacion.Colonia" id="cuartoInput" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" >
										<div class="ventanaModal__contenido--dato">
											<label class="fzm-15" id="Agrcp">C.Postal: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario" id="dveline5">
											<input name="cp" type="number" placeholder="Codigo Postal" maxlength="5" ng-model="dataFacturacion.Codigo_postal" id="quintoInput" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" >
										<div class="ventanaModal__contenido--dato">
											<label id="Agrciu">Ciudad: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario" id="dveline6">
											<input name="ciudad" type="text" placeholder="Ingresa tu ciudad" ng-model="dataFacturacion.Ciudad" id="sextoInput" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" >
										<div class="ventanaModal__contenido--dato">
											<label id="Agrest">Estado: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario">
											<select name="estado" ng-model="dataFacturacion.Estado" id="optcfdi">
												<optgroup label ="Selecciona tu Estado">
													<option ng-repeat="estados in Facturacion.estados" value="{{estados.Descripcion}}">({{estados.estados}}) {{estados.Descripcion}}</option>
												</optgroup>
											</select>
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" >
										<div class="ventanaModal__contenido--dato">
											<label id="Agrcfdi">CFDI: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario">
											<select name="cfdi" ng-model="dataFacturacion.cfdi" id="optcfdi">
												<optgroup label ="Seleccion el tipo de uso de tu cfdi">
													<option ng-repeat="cfdi in Facturacion.usocfdi" value="{{cfdi._id}}" id="octavoInput">({{cfdi.UsoCFDI}}) {{cfdi.Descripcion}}</option>
												</optgroup>
											</select>
										</div>
									</div>

									<span id="alertvalid1" class="ventanaModal__contenido--alerta"><img src="images/icono notificacion.svg" alt="icon-user" style="width: 32px;padding-right: 2px;">Completa todos los datos para poder continuar</span>
									<button class="btn btn-danger ventanaModal__contenido--botonguardar" ng-click="inputvalidfactura()" id="btngfact">Guardar</button>
								</div>
							</form>
						</div> <!--Fin Modal Agregar Nueva Factura-->
					</form>

					
				</section>

				<!-- Modal -->
				<div class="modal fade mdl-new" id="mdlcotizar" tabindex="-1" role="dialog" aria-labelledby="mdlcotizar" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered modal-lg" role="document">
							<div class="modal-content mdl-env-ed">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLongTitle">Selecciona tu paqueteria</h5>
									<button type="button" class="close" data-dismiss="modal" aria-label="Close">
										<span aria-hidden="true">&times;</span>
									</button>
								</div>

								<div class="modal-body">
									<div class="row">
										<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
											<section ng-show="!Costumer.dataDomicilio.Bandera">
												<div class="alert alert-danger fade-in">
													<h4><i class="fa fa-close"></i> Error </h4>
													No hay un codigo postal predeterminado para cotizar el envio, predetermine una direccion presionando el siguiente boton
												</div>

												<div class="text-center">
													<button class="btn btn-danger" ng-click="btndireccionguardadas('Direcciones')">Predeterminar una direccion</button>
												</div>

											</section>

											<section ng-show="Costumer.dataDomicilio.Bandera" >
												<div class="alert alert-warning fade-in" ng-show="flag">
													<i class="fa fa-refresh fa-spin"></i> Espera un momento por favor estamos cotizando tu envio
												</div>

												<div class="table-responsive">
													<table class="table table-hover" ng-show="!flag">
														<tbody>
															<tr ng-repeat="paq in cotizador" ng-cloak ng-click="selectenvio(paq)" style="cursor: pointer;" class="bdl-a-r">
																<td><input type="radio"></td>
																<td class="text-center"><img class="bdl-c-itm" ng-src="images/paqueterias/{{paq.provider}}.svg" 
																	alt="{{paq.provider}}"></td>
																<td>{{getFechaentrega(paq.days)}}</td>
																<td class="text-red">{{paq.total_pricing | currency}}</td>
															</tr>
														</tbody>
													</table>
												</div>
											</section>
										</div>
									</div>
								</div>

								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
								</div>

							</div>
						</div>
				</div>
			</form>
		</div>
	</body>
</html>

	


