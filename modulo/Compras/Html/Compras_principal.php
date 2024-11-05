<html>
	<head>
	<meta charset="UTF-8">
	</head>

	<body>
		<div class="contenedor contenedor-principal global__content" id="MainDark" ng-controller="ComprasCtrl">
			<form name="frmcompra" id="frmcompra" novalidate>
				<main class="compras">
					<article class="cliente" ng-controller="ProfileCtrl" ng-init="pag='Direcciones'">

						<div>
							<h4 class="cliente__titulo txtred">Dirección Entrega</h4>
							<div class="cliente__datos" ng-repeat="dire in profile.arrayDomicilios" ng-show="dire.Predeterminado == 1" >
								<p><b ng-show="!dataflag"><i class="fa fa-sync-alt fa-spin"></i>Cargando Datos.</b><b>{{Costumer.profile.nombres}} {{Costumer.profile.Apellidos}}.</b></p>
								<p>{{dire.Domicilio}} #{{dire.numExt}}, {{dire.Colonia}}.</p>
								<p>{{dire.Ciudad}}, {{dire.Estado}}, {{dire.Codigo_postal}}.</p>
								<p>{{dire.Referencia}}.</p>
								<p class="p-t-30">Tel: {{dire.Telefono}}</p>
							</div>
							<div class="cliente__datos" ng-show="profile.arrayDomicilios[0].Predeterminado != 1" >
								<p><b ng-show="!dataflag"><i class="fa fa-sync-alt fa-spin"></i>Cargando Datos.</b><b>{{Costumer.profile.nombres}} {{Costumer.profile.Apellidos}}.</b></p>
								<p><b>NOTA:</b> Agrega o selecciona una direccion para seguir con tu compra.</p>
							</div>
						</div>

						<div class="cliente__opciones" ng-controller="ProfileCtrl" ng-init="pag='Direcciones'">
							<div class="cliente__opciones--otra" ng-show="profile.arrayDomicilios[0]">
								<span id="abrirModal1" class="form-control cliente__opciones--button click"><i class="far fa-check-square"></i>Seleccionar Otra</span>
							</div>

							<div class="cliente__opciones--nueva">
								<span id="abrirModal2" class="form-control cliente__opciones--button click"><i class="fa fa-plus"></i>Agregar Nueva</span>
							</div>
											
							<div class="cliente__opciones--editar" ng-show="profile.arrayDomicilios[0].Predeterminado != 0">
								<span class="cliente__opciones--button form-control click" id="abrirModal0" ng-click="btneditDomicilio(Costumer.dataDomicilio.data._id)" ng-show="profile.arrayDomicilios[0]"><i class="fa fa-edit"></i>Editar Dirección</span>
							</div>

						</div>

					</article>

					<article class="productos">
						<p class="center--text txtred">Tienes {{Numproducts}} articulo(s) en el carrito.</p>
						<div class="productos__contenedor">
							<div class="productos__datos--contenedor" ng-repeat="product in session.CarritoPrueba">
								<div class="productos__datos">
									<div class="productos__datos--img" ng-click="btnEliminarRefaccion(product, true)">
										<img ng-src="{{getImagen(product.imagenid)}}" width="90px" height="120px" alt="IMG">
									</div>
									<div class="productos__datos--info">
										<a ng-click="RefaccionDetalles(product.imagenid)" class="productos__datos--info--articulo">{{product._producto}}</a>
										<p>No.Parte: {{product.No_parte}}</p>
										<p class="text-dark" ng-hide="product.RefaccionOferta == '1'">{{product.Cantidad}} x {{product.Precio |currency}}</p>
										<p class="text-dark" ng-show="product.RefaccionOferta == '1'">{{product.Cantidad}} x {{product.Precio2 | currency}}</p>

										<div class="productos__datos--contador">
											<div class="agregarmas no-overflow">
								
												<button class="agregarmas__botones" ng-click="btnQuitar(product)">
													<i class="fa fa-minus" aria-hidden="true"></i>
												</button>
												<div class="agregarmas__contador--compras num-product center--text">
													<span>{{product.Cantidad}}</span>
												</div>
												<button class="agregarmas__botones" ng-click="btnAgregar(product)">
													<i class="fa fa-plus" aria-hidden="true"></i>
												</button>

											</div>
										</div> 

									</div>
								</div>
								<hr class="productos__datos--divisor">
							</div>
						</div>
						<hr>
					</article>
				</main>

				<main class="pagos">
					<article class="pagos__facturacion"  ng-controller="ProfileCtrl" ng-init="pag='Facturacion'">
						<div>
							<h4 class="pagos__facturacion--titulo txtred">Facturación</h4>
							<div class="cliente__datos" ng-repeat="facliente in Facturacion.dataFacturacion" ng-show="facliente.Predeterminado == 1">
								<b ng-show="!dataflag"><i class="fa fa-sync-alt fa-spin"></i>Cargando Datos.</b>
								<p>Actividad Empresarial: Persona Fisica.</p>
								<p>Razon Social: {{facliente.Razonsocial}}.</p>
								<p>RFC: {{facliente.Rfc}}</p>
								<p class="p-t-30">Uso de CFDI: {{facliente.Descripcion}}</p>
							</div>
							<div class="cliente__datos" ng-show="Facturacion.dataFacturacion[0].Predeterminado != 1" >
								<p><b ng-show="!dataflag"><i class="fa fa-sync-alt fa-spin"></i>Cargando Datos.</b><b>{{Costumer.profile.nombres}} {{Costumer.profile.Apellidos}}.</b></p>
								<p><b>NOTA:</b> Si posterior a esta compra deseas facturar tu pedido, es necesario solicitarla el mismo dia de tu compra, en caso de lo contrario se genera con un RFC generico.</p>
							</div>
						</div>
						
						<div class="cliente__opciones">
							<div class="cliente__opciones--editar" ng-show="Facturacion.dataFacturacion[0]">
								<span id="abrirModal3" class="form-control cliente__opciones--button click" ><i class="far fa-check-square"></i>Seleccionar</span>
							</div>
							<div class="cliente__opciones--otra">
								<span id="abrirModal4" class="form-control cliente__opciones--button click"><i class="fa fa-plus"></i>Agregar Nueva</span>
							</div>
							<div class="cliente__opciones--nueva" ng-show="Facturacion.dataFacturacion[0].Predeterminado != 0 && Costumer.facturacion != 0">
								<span id="facturaNo" class="form-control cliente__opciones--button" ng-click="facturaNo()"><i class="fa fa-ban"></i>No Usar Datos</span>
							</div>
						</div>
						
					</article>

					<article class="pagos__productos">
						<div class="pagos__productos--confirmacion">
							<div class="pagos__sitio-seguro">
								<div class="pagos__sitio-seguro--contenedor">
									<div class="pagos__sitio-seguro--info center--text">
										<!-- <img src="https://macromautopartes.com/images/icons/Icono-seguridad.png" alt="seguridad" style="width:5rem;"> -->
										<img src="images/icons/Icono-seguridad.png" alt="seguridad" style="width:5rem;">
										<b>Sitio seguro</b>
										<p class="text-center txtblack">Nuestro sitio cuenta con certificaciones de seguridad por parte del MIT y Santander</p>
									</div>
											
								</div>
								<div class="pagos__cupon">
									<div class="cuponcont">
										<input type="text" class="inpcpn" placeholder="Ingresa tu cupón." id="inpCupon" autocomplete="off">
										<button class="form-control" ng-click="cupon()" id="btncupon">Canjear</button>
									</div>
									<span class="cupon--alert" id="alert--cupon"></span>
								</div>

								<div>
									<table class="tablecompra">
										<tbody>
											<tr>
												<td>Productos</td>
													<td  class="text-right">{{subtotal() | currency}}</td>
												</tr>
											<tr ng-show="Costumer.descuento > 0">
												<td class="text-red">Descuento: <u>{{Costumer.valor_cpn}}%</u></td>
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
												<td class="flexcontent__end"><button class="form-control cotizar__button" ng-click="btncotizar()">Cotizar</button></td>
											</tr>
											<tr class="table tbl-cms">
												<td>Total(con iva)</td>
												<td  class="text-right"><h5 class="text-red"><b>{{Ttotal() | currency}}</b></h6></td>
											</tr>
										</tbody>
									</table>
								</div>	
								<button id="abrirModal5" class="btn btn-danger bg9 form-control pagar--button click" ng-show="subtotal() > 0">Pagar</button>
								<p ng-show="session.autentificacion==1">
									<label class="checkbox ">
										<input type="checkbox" name="aviso" id="aviso" ng-model="Costumer.aviso" required>
										<span class="check"></span>
										<div class="p-l-40 acepto__tyc"> He leido y estoy de acuerdo con las <a href="?mod=Terminos-condiciones" class="text-red">condiciones generales</a> 
												y el <a href="?mod=aviso-de-privacidad" class="text-red"> aviso de privacidad</a>.
										</div>
									</label>
								</p>
						</div>
					</article>

					<!-- Ventana modal Metodo De Pago, por defecto no visiblel -->
					<div id="ventanaModal5" class="ventanaModal__contenedor">
							<div class="ventanaModal__contenido pagometodo">
								<span class="cerrar5 closem">&times;</span>
								<h2 class="center--text">Metodos de Pago</h2>

								<article class="metodo__pago">
									<div class="metodo__pago--contenedor">
										<div class="metodo__pago--metodos">
											<div class="metodo__pago--metodo" disabled style="opacity:0.1">
												<button class="metodopago bo18 h-120" id="btncredito" ng-click="metarjeta()" disabled>
													<label class="radio2">Tarjeta de crédito / debito</label>
													<img ng-src="https://macromautopartes.com/images/icons/VISA MASTERCARD.svg" alt="tarjeta credito" class ="img-fluid mx-auto d-block" width="150px" height="31px">
													<!-- <img ng-src="images/icons/VISA MASTERCARD.svg" alt="tarjeta credito" class ="img-fluid mx-auto d-block" width="150px" height="31px" style="opacity:0.1"> -->
												</button>	
											</div>

											<div class="metodo__pago--metodo">
												<button class="metodopago bo18 h-120" id="btnefectivo" ng-click="medeposito()">
													<label class="radio2">Deposito en efectivo</label>
													<img ng-src="https://macromautopartes.com/images/icons/OXXO.svg" alt="deposito efectivo"  class ="img-fluid mx-auto d-block" width="150px" height="31px">
													<!-- <img ng-src="images/icons/OXXO.svg" alt="deposito efectivo"  class ="img-fluid mx-auto d-block" width="150px" height="31px"> -->
												</button>
											</div>

											<div class="metodo__pago--metodo">
												<button class="metodopago bo18 h-120" ng-click="metransfe()" id="btntransfe">
													<label class="radio2">Tranferencia bancaria</label>
													<img ng-src="https://macromautopartes.com/images/icons/SPEI.svg" alt="transferencia bancaria" class ="img-fluid mx-auto d-block" width="150px" height="31px">	
													<!-- <img ng-src="images/icons/SPEI.svg" alt="transferencia bancaria" class ="img-fluid mx-auto d-block" width="150px" height="31px"> -->
												</button>
											</div>
										</div>
									</div>
								</article>
								<button class="btn btn-danger bg9 form-control confirmar--pago" ng-click="btnPagar()">Confirmar Pago</button>
								<div class="clip">
									<span class="form-control confirmando--pago"><b><i class="fa fa-sync-alt fa-spin"></i></b></span>
								</div>
							</div>
					</div> <!--Fin Modal Metodo De Pago-->
				</main>

				<section ng-controller="ComprasCtrl">
					<form name="frmcompra" id="frmcompra" novalidate>
						<!-- Ventana modal Editar Dirección, por defecto no visiblel -->
						<div id="ventanaModal0" class="modal" ng-controller="ProfileCtrl" ng-init="pag='Direcciones_edit'">
							<div class="ventanaModal__contenido">
								<span class="cerrar0 enlace closem">&times;</span>
								<h2 class="text-center text-white bg9">Editar dirección de envío</h2>

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
								<span class="cerrar1 closem">&times;</span>
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
											<button class="form-control" ng-click="btnPredeterminado(dire._id)" ng-show="dire.Predeterminado == 0"><i class="far fa-check-square"></i>Seleccionar</button>
										</div>

										<div class="ventanaModal__contenido--boton">
											<button class="form-control" ng-click="btndescartarDomicilio(dire._id)"><i class="far fa-trash-alt"></i>Eliminar</button>
										</div>
									</div>

								</div>
							</div>
						</div> <!--Fin Modal Seleccionar Otra-->

						<!-- Ventana modal Agregar Nueva, por defecto no visiblel -->
						<div id="ventanaModal2" class="modal2"  ng-controller="ProfileCtrl" ng-init="pag='Direcciones_add'" >
							<div class="ventanaModal__contenido">
								<span class="cerrar2 enlace closem">&times;</span>
								<h2 class="center--text">Agregar dirección envío</h2>
				
								<div class="ventanaModal__contenido--agregar" id="agregar_div1">
									<div class="ventanaModal__contenido--dato">
										<label id="agregar_lbl1">Calle: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input class="text-black inp-dts" placeholder="Ingresa tu Domicilio" ng-model="dataDireccion.Domicilio" id="agregar_1">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" id="agregar_div2">
									<div class="ventanaModal__contenido--dato">
										<label id="agregar_lbl2">Numero: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input ng-model="dataDireccion.numExt" placeholder="Ext." id="agregar_2">
									</div>

									<div class="ventanaModal__contenido--dato">
										<label id="agregar_lbl3"></label>
									</div>

									<div class="ventanaModal__contenido--usuario" id="agregar_div3">
										<input ng-model="dataDireccion.numInt" placeholder="Int." id="agregar_3">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" id="agregar_div4">
									<div class="ventanaModal__contenido--dato">
										<label id="agregar_lbl4">Colonia: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input placeholder="Ingresa tu colonia" ng-model="dataDireccion.Colonia" id="agregar_4">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" id="agregar_div5" >
									<div class="ventanaModal__contenido--dato">
										<label class="fzm-15" id="agregar_lbl5">Cod.Postal: <b class="obligatorio">*</b></label>
									</div>

									<div class="ventanaModal__contenido--usuario">
										<input type="number" placeholder="00000" maxlength="5" ng-model="dataDireccion.Codigo_postal" id="agregar_5">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" id="agregar_div6">
									<div class="ventanaModal__contenido--dato">
										<label id="agregar_lbl6">Ciudad: <b class="obligatorio">*</b></label>
									</div>
									<div class="ventanaModal__contenido--usuario">
										<input placeholder="Ingresa tu ciudad" ng-model="dataDireccion.Ciudad" id="agregar_6">
									</div>
								</div>

								<div class="ventanaModal__contenido--agregar" id="agregar_div7">
									<div class="ventanaModal__contenido--dato">
										<label id="agregar_lbl7">Estado: <b class="obligatorio">*</b></label>
									</div>
									<div class="ventanaModal__contenido--usuario">
										<input placeholder="Ingresa tu estado" ng-model="dataDireccion.Estado" id="agregar_7">
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

								<div class="ventanaModal__contenido--agregar" id="agregar_div8">
									<div class="ventanaModal__contenido--dato">
										<label id="agregar_lbl8">Telefono: <b class="obligatorio">*</b></label>
									</div>
									<div class="ventanaModal__contenido--usuario">
										<input type="number" placeholder="Ingresa numero de Telefono" ng-model="dataDireccion.Telefono" id="agregar_8">
									</div>
								</div>
								<!-- <span id="alertvalid" class="ventanaModal__contenido--alerta"><img src="https://macromautopartes.com/images/icono notificacion.svg" alt="icon-user" style="width:3.2rem; padding-right: 2px;">Completa todos los datos para poder continuar</span> -->
								<span id="alertvalid" class="ventanaModal__contenido--alerta"><img src="images/icono notificacion.svg" alt="icon-user" style="width:3.2rem; padding-right: 2px;">Completa todos los datos para poder continuar</span>
								<button class="btn btn-danger ventanaModal__contenido--botonguardar" ng-click="inputvalidireccion()">Guardar</button>
							</div>
						</div> <!--Fin Modal Agregar Nueva-->


						<!-- Ventana modal Seleccionar Otra Facturacion, por defecto no visiblel -->
						<div id="ventanaModal3" class="modal3" ng-controller="ProfileCtrl" ng-init="pag='Facturacion'">
							<div class="ventanaModal__contenido">
								<span class="cerrar3 closem">&times;</span>
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
											<button class="form-control" name="facturacion" id="factsi" ng-click="btnFacpredetermiando(d._id, 'pre')"><i class="far fa-check-square"></i>Seleccionar</button>
										</div>
										<div class="ventanaModal__contenido--boton">
											<button class="form-control" ng-click="btnEliminardatosfacturacion(d._id, 'del')"><i class="far fa-trash-alt"></i>Eliminar</button>
										</div>
									</div>

								</div>
							</div>
						</div> <!--Fin Modal Seleccionar Otra Facturacion-->

						<!-- Ventana modal Agregar Nueva Factura, por defecto no visiblel -->
						<div id="ventanaModal4" class="modal4" ng-controller="ProfileCtrl" ng-init="pag='Facturacion_add'">
							<form name="Facturacionform" novalidate>
								<div class="ventanaModal__contenido">
									<span class="cerrar4 enlace closem">&times;</span>
									<h2 class="center--text">Agregar Nuevos Datos</h2>
										
									<div class="ventanaModal__contenido--agregar" id="Fagregar_div0">
										<div class="ventanaModal__contenido--dato">
											<label id="Fagregar_lbl0">Actividad Empresarial: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario">
											<select name="Actividad" ng-model="dataFacturacion.Actividad">
												<optgroup label ="Seleccion la actividad de la empresa">
													<option ng-repeat="Acti in Facturacion.Actividad" value="{{Acti.valor}}" id="Fagregar_0">{{Acti.valor}}</option>
												</optgroup>
											</select>
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" id="Fagregar_div1">
										<div class="ventanaModal__contenido--dato">
											<label id="Fagregar_lbl1">R.Social: <b class="obligatorio">*</b></label>
										</div>
										<div class="ventanaModal__contenido--usuario" id="dveline">
											<input name="razon" type="text" placeholder="Introduce Razon Social" ng-model="dataFacturacion.Razonsocial" id="Fagregar_1" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" id="Fagregar_div2">
										<div class="ventanaModal__contenido--dato">
											<label id="Fagregar_lbl2">RFC: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario">
											<input name="RFC" type="text" placeholder="Ingresa tu RFC" ng-model="dataFacturacion.Rfc" id="Fagregar_2" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" id="Fagregar_div3">
										<div class="ventanaModal__contenido--dato">
											<label id="Fagregar_lbl3">Domicilio: <b class="obligatorio">*</b></label>
										</div>
										<div class="ventanaModal__contenido--usuario">
											<input name="domicilio" type="text" placeholder="Ingresa calle y numero" ng-model="dataFacturacion.Domicilio" id="Fagregar_3" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" id="Fagregar_div4">
										<div class="ventanaModal__contenido--dato">
											<label id="Fagregar_lbl4">Colonia: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario">
											<input name="colonia" type="text" placeholder="Ingresa tu colonia" ng-model="dataFacturacion.Colonia" id="Fagregar_4" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" id="Fagregar_div5">
										<div class="ventanaModal__contenido--dato">
											<label class="fzm-15" id="Fagregar_lbl5">C.Postal: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario">
											<input name="cp" type="number" placeholder="Codigo Postal" maxlength="5" ng-model="dataFacturacion.Codigo_postal" id="Fagregar_5" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" id="Fagregar_div6">
										<div class="ventanaModal__contenido--dato">
											<label id="Fagregar_lbl6">Ciudad: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario">
											<input name="ciudad" type="text" placeholder="Ingresa tu ciudad" ng-model="dataFacturacion.Ciudad" id="Fagregar_6" autocomplete="off">
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" id="Fagregar_div7">
										<div class="ventanaModal__contenido--dato">
											<label id="Fagregar_lbl7">Estado: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario">
											<select name="estado" ng-model="dataFacturacion.Estado" id="optcfdi">
												<optgroup label ="Selecciona tu Estado">
													<option ng-repeat="estados in Facturacion.estados" value="{{estados.Descripcion}}" id="Fagregar_7">({{estados.estados}}) {{estados.Descripcion}}</option>
												</optgroup>
											</select>
										</div>
									</div>

									<div class="ventanaModal__contenido--agregar" id="Fagregar_div8">
										<div class="ventanaModal__contenido--dato">
											<label id="Fagregar_lbl8">CFDI: <b class="obligatorio">*</b></label>
										</div>

										<div class="ventanaModal__contenido--usuario">
											<select name="cfdi" ng-model="dataFacturacion.cfdi" id="optcfdi">
												<optgroup label ="Seleccion el tipo de uso de tu cfdi">
													<option ng-repeat="cfdi in Facturacion.usocfdi" value="{{cfdi._id}}" id="Fagregar_8">({{cfdi.UsoCFDI}}) {{cfdi.Descripcion}}</option>
												</optgroup>
											</select>
										</div>
									</div>

									<!-- <span id="alertvalid1" class="ventanaModal__contenido--alerta"><img src="https://macromautopartes.com/images/icono notificacion.svg" alt="icon-user" style="width: 3.2rem; padding-right: 2px;">Completa todos los datos para poder continuar</span> -->
									<span id="alertvalid1" class="ventanaModal__contenido--alerta"><img src="images/icono notificacion.svg" alt="icon-user" style="width: 3.2rem; padding-right: 2px;">Completa todos los datos para poder continuar</span>
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
								<button type="button" class="close" id="cotizarclose" data-dismiss="modal" aria-label="Close">
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
												<i class="fa fa-sync-alt fa-spin"></i> Espera un momento por favor estamos cotizando tu envio
											</div>
											<div class="table-responsive">
												<table class="table table-hover" ng-show="!flag">
													<tbody>
														<tr ng-repeat="paq in cotizador" ng-cloak ng-click="selectenvio(paq)" class="bdl-a-r enlace">
															<td><input type="radio"></td>
															<!-- <td class="text-center"><img class="bdl-c-itm" ng-src="https://macromautopartes.com/images/paqueterias/{{paq.provider}}.svg" alt="{{paq.provider}}"></td> -->
															<td class="text-center"><img class="bdl-c-itm" ng-src="images/paqueterias/{{paq.provider}}.svg" alt="{{paq.provider}}"></td>
															<td>{{getFechaentrega(paq.days)}}</td>
															<!-- <td ng-show="paq.days<=3"><i class="fas fa-dollar-sign"></i></td>
															<td ng-show="paq.days<=3"><i class="fas fa-bolt"></i></td> -->
															<td class="text-red">{{paq.total_pricing | currency}}</td>
														</tr>
													</tbody>
												</table>
											</div>
										</section>
									</div>
								</div>
							</div>

						</div>
					</div>
				</div>
			</form>
		</div>
	</body>
</html>

	


