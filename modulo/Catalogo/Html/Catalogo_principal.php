<div ng-controller="catalogosCtrl" ng-init="refaccion.categoria='{categoria}'" class="ctrlmain global__content">
	<!-- Title Page -->
	<header class="header">
		<section class="header__contenido" ng-repeat="cat in catalogos.Escritorio">
		<picture class="header__picture">
						<source srcset="images/Banners/CATALOOG.webp" type="image/webp" class="header__picture--img">
						<img loading="lazy" src="images/Banners/{{banner.imagen}}" alt="banner">
				</picture>
		</section>
	</header>
	<!-- Content page -->
	<main class="bgblack p-b-65">
		<div class="container-fluid">
			<div class="contenedor__catalogo">
				<div class="catalogo">
					<div class="catalogo__filtrado">
						<!-- <h4 class="m-text14 p-b-32 text-white">
							Filtrar
						</h4> -->
						<div class="catalogo__filtrado--opciones">
							<label for="txtMarca" class="text-white">Armadora:</label>
							<select name="txtMarca" id="txtMarca" class="form-control" ng-model="refaccion.marca"
								ng-change="getVehiculos(true)">
								<optgroup label="Selecciona la Marca">
									<option value="T">-- Todas --</option>
									<option ng-repeat="marca in Marcas" value="{{marca._id}}">{{marca.Marca}}</option>
								</optgroup>
							</select>
							<label for="txtVehiculo" class="text-white">Vehiculo</label>
							<select name="txtVehiculo" id="txtVehiculo" class="form-control"
								ng-model="refaccion.vehiculo" ng-change="getModelos()">
								<optgroup label="Selecciona el Vehiculo">
									<option value="">-- Todas --</option>
									<option ng-repeat="vehiculo in Vehiculos" value="{{vehiculo._id}}">{{vehiculo.Modelo}}</option>
								</optgroup>
							</select>
							<label for="txtModelo" class="text-white">Modelo</label>
							<select name="txtModelo" id="txtModelo" class="form-control" ng-model="refaccion.anio"
								ng-change="getAnios()">
								<optgroup label="Selecciona el Modelo">
									<option value="">-- Todas --</option>
									<option ng-repeat="modelo in Modelos" value="{{modelo._id}}">{{modelo.Anio}}
									</option>
								</optgroup>
							</select>
							<label for="txtCategoria" class="text-white">Categorias:</label>
							<select name="txtCategoria" id="txtCategoria" class="form-control"
								ng-model="refaccion.categoria" ng-change="getCategorias(true)">
								<optgroup label="Selecciona una categoria">
									<option value="T">--Todas--</option>
									<option ng-repeat="cat in categorias" value="{{cat._id}}">{{cat.Categoria}}</option>
								</optgroup>
							</select>
						</div>
						<div class="search-product pos-relative catalogo__filtrado--buscador form-control">
							<input class="p-l-23 p-r-50" type="text" name="search-product"
								placeholder="Buscar Producto..." ng-model="refaccion.producto"
								ng-model-options="{debounce:500}" ng-change="getRefaccion()">

							<button class="ab-r-m">
								<i class="fs-12 fa fa-search" aria-hidden="true"></i>
							</button>
						</div>
					</div>

				</div>

				<div class="">
					<!--  -->
					<div class="flex-sb-m flex-w p-b-35">
						<div class="flex-w">

						</div>

						<span class="s-text8 p-t-5 p-b-5 text-white">
							Refacciones Encontradas: {{Trefacciones}} resultados
						</span>
					</div>
					
					<!-- Product -->
					<div class="row refacciones__productos">
						<div class="col-xs-12 col-sm-12 col-md-6 col-lg-3 p-b-50 half-w"
							ng-repeat="producto in Refacciones">
							<!-- Block2 -->
							<div class="block2 ">
								<div ng-click="RefaccionDetalles(producto._id)" class="block2-img enlace wrap-pic-w of-hidden
									{{producto.RefaccionOferta==1? 'block2-labelsale':''}}"
									ng-class="{
											'ribboagotado': producto.agotado ,
											'ribbonnuevo': producto.RefaccionNueva==1,
											'ribbonoferta': producto.RefaccionOferta,
											'pos-relative': !producto.agotado || producto.RefaccionNueva==0

										}"
									ng-style="{'background-color': producto.color}">
									<img ng-src="{{producto.imagen? 'images/refacciones/'+producto._id+'.png':'images/refacciones/'+producto._id+'.webp'}}"
										alt="{{producto.tag_alt}}" title = "{{producto.tag_title}}">

								</div>
								
								<div class="block2-txt p-t-20">
									<section class="descripcion-producto">
										<img ng-src="{{producto.imagenproveedor? 'images/Marcasrefacciones/' + producto.id_proveedor + '.png':'images/Marcasrefacciones/boxed-bg.jpg'}}" 
										alt="{{producto.tag_altproveedor}}" title="{{producto.tag_titleproveedor}}">
										<p class="block2-name dis-block s-text3 p-b-5 text-white">
											{{producto.Producto}}
										</p>
										
									</section>
									

									<span class="block2-price m-text6 p-r-5 p-t-5 text-white text-center">
										<h3 ng-click="RefaccionDetalles(producto._id)" class="precio enlace">{{producto.RefaccionOferta? producto.Precio2:producto.Precio1 | currency}}</h3>
									</span>
									<div class="enviogratis" ng-show="producto.Enviogratis">
										<img src="/images/icons/Icono-camion.png" alt="" >
										<p class="text-white"> <strong>Envío Gratis </strong> </p> 
									</div>
								</div>
							</div>
						</div>


					</div>
					<script type="text/javascript">function toTop() {window.scrollTo(0, 0)} </script>
					<!-- Pagination -->
					<div class="pagination p-t-26">
						<a onClick="toTop()" class="item-pagination flex-c-m trans-0-4 {{currentPage == (pag.no - 1)? 'active-pagination':''}}"
							ng-repeat='pag in pages' ng-click='setPage(pag.no)'>{{pag.no}}</a>

					</div>
				</div>
			</div>
		</div>
	</main>

</div>