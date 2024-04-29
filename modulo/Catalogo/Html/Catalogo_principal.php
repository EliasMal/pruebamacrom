<div ng-controller="catalogosCtrl" ng-init="refaccion.categoria='{categoria}'" class="ctrlmain global__content">
	<!-- Title Page -->
	<header class="header">
		<section class="header__contenido" ng-repeat="cat in catalogos.Escritorio">
		<picture class="header__picture">
						<source srcset="https://macromautopartes.com/images/Banners/CATALOOG.webp" type="image/webp" class="header__picture--img">
						<img loading="lazy" src="https://macromautopartes.com/images/Banners/{{banner.imagen}}" alt="banner">
				</picture>
		</section>
	</header>
	<!-- Content page -->
	<main class="main__catalogo--principal">
		<div class="container-fluid">
			<div class="contenedor__catalogo">
				<div class="catalogo">
					
					<div class="catalogo__filtrado">

						<div class="catalogo__filtrado--opciones">

							<label for="txtMarca" class="">Armadora:</label>
							<select name="txtMarca" id="txtMarca" class="form-control" onchange="window.location.href='?mod=catalogo&pag=1&prod='+document.getElementById('prod_input').value+'&cate='+document.getElementById('txtCategoria').value+'&armadora='+this.value+'&mdl=&[a]='"
								ng-model="refaccion.marca">
								<optgroup label="Selecciona la Marca">
									<option value="">--Todas--</option>
									<option ng-repeat="marca in Marcas" value="{{marca._id}}">{{marca.Marca}}</option>
								</optgroup>
							</select>

							<label for="txtVehiculo" class="">Vehiculo:</label>
							<select name="txtVehiculo" id="txtVehiculo" class="form-control" onchange="window.location.href='?mod=catalogo&pag=1&prod='+document.getElementById('prod_input').value+'&cate='+document.getElementById('txtCategoria').value+'&armadora='+document.getElementById('txtMarca').value+'&mdl='+this.value+'&[a]='"
								ng-model="refaccion.vehiculo">
								<optgroup label="Selecciona el Vehiculo">
									<option value="">--Todas--</option>
									<option ng-repeat="vehiculo in Vehiculos" value="{{vehiculo._id}}">{{vehiculo.Modelo}}</option>
								</optgroup>
							</select>

							<label for="txtModelo" class="">Modelo:</label>
							<select name="txtModelo" id="txtModelo" class="form-control" onchange="window.location.href='?mod=catalogo&pag=1&prod='+document.getElementById('prod_input').value+'&cate='+document.getElementById('txtCategoria').value+'&armadora='+document.getElementById('txtMarca').value+'&mdl='+document.getElementById('txtVehiculo').value+'&[a]='+this.value"
								ng-model="refaccion.anio">
								<optgroup label="Selecciona el Modelo">
									<option value="">--Todas--</option>
									<option ng-repeat="modelo in Modelos" value="{{modelo._id}}">{{modelo.Anio}}
									</option>
								</optgroup>
							</select>

							<label for="txtCategoria" class="">Categorias:</label>
							<select name="txtCategoria" id="txtCategoria" class="form-control" onchange="window.location.href='?mod=catalogo&pag=1&prod='+document.getElementById('prod_input').value+'&cate='+this.value+'&armadora='+document.getElementById('txtMarca').value+'&mdl='+document.getElementById('txtVehiculo').value+'&[a]='+document.getElementById('txtModelo').value" 
								ng-model="refaccion.categoria">
								<optgroup label="Selecciona una categoria">
									<option value="T">--Todas--</option>
									<option ng-repeat="cat in categorias" value="{{cat._id}}">{{cat.Categoria}}</option>
								</optgroup>
							</select>

						</div>

					</div>

				</div>

				<div class="bgwhite contenedor">
					<!-- Product -->
					<div class="contenedor__buscador__encontrados">

						<div class="search-product pos-relative catalogo__filtrado--buscador form-control">
							<input type="text" name="search-product"
								placeholder="Buscar Producto..." ng-model="refaccion.producto"
								ng-model-options="{debounce:1500}" id="prod_input">
								
							<button class="ab-r-m" ng-click="getRefaccion()">
								<i class="fa fa-search" aria-hidden="true"></i>
							</button>
						</div>

						<div class="flex-sb-m flex-w Trefacciones__cont">
							<span class="s-text8">
								{{Trefacciones}} Refacciones encontradas.
							</span>
						</div>

					</div>
					<div class="nuestrosproductos__masvendidos">
							<div class="home__pr" ng-repeat="producto in Refacciones">
								<!-- Block2 -->
								<div class="productos__informacion">
									<div class="wrap-pic-w of-hidden cursorpnt" 
									ng-class="{'pos-relative': !producto.agotado}">
									<a href="?mod=catalogo&opc=detalles&_id={{producto._id}}" class="enlace">
										<!-- <img ng-src="{{producto.imagen? 'https://macromautopartes.com/images/refacciones/'+producto._id+'.png':'https://macromautopartes.com/images/refacciones/'+producto._id+'.webp'}}" alt="{{producto.Producto}}"> -->
										<img ng-src="{{producto.imagen? 'images/refacciones/'+producto._id+'.png':'images/refacciones/'+producto._id+'.webp'}}" alt="{{producto.Producto}}">
									</a>
									</div>

									<div class="block2-txt">
										<section class="descripcion-producto">

											<a href="?mod=catalogo&opc=detalles&_id={{producto._id}}" class="block2-name dis-block s-text3">{{producto.Producto}}</a>
											<div class="precio__imgprove">	
												<p class="precio__producto" ng-hide="producto.RefaccionOferta"> {{producto.Precio1 | currency}} </p>	
												<p class="precio__producto" ng-show="producto.RefaccionOferta"> <small class="line_through-red">{{producto.Precio1 | currency}}</small> </p>
												<!-- <img ng-src="{{producto.imagenproveedor? 'https://macromautopartes.com/images/Marcasrefacciones/boxed-bg.jpg':'https://macromautopartes.com/images/Marcasrefacciones/' + producto.idProveedor + '.png'}}" 
												alt="{{producto.tag_altproveedor}}" title="{{producto.tag_titleproveedor}}" class="proovedor__img"> -->
												<img ng-src="{{producto.imagenproveedor? 'images/Marcasrefacciones/' + producto.idProveedor + '.png':'images/Marcasrefacciones/boxed-bg.jpg'}}" 
												alt="{{producto.tag_altproveedor}}" title="{{producto.tag_titleproveedor}}" class="proovedor__img">
												
											</div>

										</section>

										<div class="enviogratis"  ng-show="producto.Enviogratis">
											<!-- <img src="https://macromautopartes.com/images/icons/Icono-camion.png" loading="lazy" alt=""> -->
											<img src="/images/icons/Icono-camion.png" loading="lazy" alt="">
											<p> <strong>Envío Gratis </strong> </p> 
										</div>

									</div>
								</div>
							</div>
					</div>

					<script type="text/javascript">function toTop() {window.scrollTo(0, 0)} </script>
					<!-- Pagination -->
					<div class="pagination">
						<a class="item-pagination flex-c-m trans-0-4 {{currentPage == (pag.no - 1)? 'active-pagination':''}}"
							ng-repeat='pag in pages' ng-click='setPage(pag.no)'>{{pag.no}}</a>                                                                                                                                                                                                                               
					</div>

				</div>
			</div>
		</div>
	</main>

 </div>