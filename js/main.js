
(function ($) {
    "use strict";

    /*[ Load page ]
    ===========================================================*/
    $(".animsition").animsition({
        inClass: 'fade-in',
        outClass: 'fade-out',
        inDuration: 1000,
        outDuration: 500,
        linkElement: '.animsition-link',
        loading: true,
        loadingParentElement: 'html',
        loadingClass: 'animsition-loading-1',
        loadingInner: '<div data-loader="ball-scale"></div>',
        timeout: false,
        timeoutCountdown: 2000,
        onLoadEvent: true,
        browser: [ 'animation-duration', '-webkit-animation-duration'],
        overlay : false,
        overlayClass : 'animsition-overlay-slide',
        overlayParentElement : 'html',
        transition: function(url){ window.location.href = url; }
    });

    /*[ Back to top ]
    ===========================================================*/
    var windowH = $(window).height()/2;

    $(window).on('scroll',function(){
        if ($(this).scrollTop() > windowH) {
            $("#myBtn").css('display','flex');
        } else {
            $("#myBtn").css('display','none');
        }
    });

    $('#myBtn').on("click", function(){
        $('html, body').animate({scrollTop: 0}, 300);
    });

     /** Slick Slider **/
     $('.slick').slick({
        method: {},
        dots: true,
        autoplay: true,
        infinite: true,
        autoplaySpeed: 2000,
        slidesToShow: 3,
        slidesToScroll: 1
      });

    /*[ Show header dropdown ]
    ===========================================================*/
    $('.js-show-header-dropdown').on('click', function(){
        $(this).parent().find('.header-dropdown');
        $("#usercba").css("background-color","#fff");
        $('.js-show-header-dropdown').css("color","#000");
    });

    const navigation = document.querySelector(".contenedor__navegacion--mobile");
    navigation.addEventListener("click", () => {
        navigation.classList.toggle("active");
    });

    var menu = $('.js-show-header-dropdown');
    var sub_menu_is_showed = -1;

    for(var i=0; i<menu.length; i++){
        $(menu[i]).on('click', function(){

                if(jQuery.inArray( this, menu ) == sub_menu_is_showed){
                    $(this).parent().find('.header-dropdown').toggleClass('show-header-dropdown');
                    sub_menu_is_showed = -1;
                    $("#usercba").css("background-color","transparent");
                    $('.js-show-header-dropdown').css("color","#fff");
                }
                else {
                    for (var i = 0; i < menu.length; i++) {
                        $(menu[i]).parent().find('.header-dropdown').removeClass("show-header-dropdown");

                    }

                    $(this).parent().find('.header-dropdown').toggleClass('show-header-dropdown');
                    sub_menu_is_showed = jQuery.inArray( this, menu );

                }
        });
    }

    const hero = document.querySelector('.hero');
    function activate(e) {
      if (e.target.matches('.hero') || !e.target.matches('.secundaria')) return;
      [hero.src, e.target.src] = [e.target.src, hero.src];
    }
    
    window.addEventListener('click',activate,false);



    $(".js-show-header-dropdown, .header-dropdown").click(function(event){
        event.stopPropagation();
    });

    $(window).on("click", function(){
        for (var i = 0; i < menu.length; i++) {
            $(menu[i]).parent().find('.header-dropdown').removeClass("show-header-dropdown");
        }
        sub_menu_is_showed = -1;
        $("#usercba").css("background-color","transparent");
        $('.js-show-header-dropdown').css("color","#fff");
    });

    //Show header dropdown carrito
    $('.js-show-header-dropdown1').on('click', function(){
        $(this).parent().find('.header-dropdown');
        $("#carrisvg").css("filter","brightness(2)");
        $("#divcarri").css("background-color","white");
    });

    //cambio contraseña
    $('#C__Contraseña').on('click', function(){
        $("#DatosGenerales").addClass('non-active');
        $("#CambioContraseña").removeClass('non-active');
        $(this).addClass('underline--link');
        $(this).removeClass('underline--link__none');
        $("#D__Generales").addClass('underline--link__none');
        $("#D__Generales").removeClass('underline--link');
    });
    //cambio datosgenerales
    $('#D__Generales').on('click', function(){
        $("#CambioContraseña").addClass('non-active');
        $("#DatosGenerales").removeClass('non-active');
        $(this).addClass('underline--link');
        $(this).removeClass('underline--link__none');
        $("#C__Contraseña").addClass('underline--link__none');
        $("#C__Contraseña").removeClass('underline--link');
    });

    var menu1 = $('.js-show-header-dropdown1');
    var sub_menu_is_showed1 = -1;

    for(var i=0; i<menu1.length; i++){
        $(menu1[i]).on('click', function(){

                if(jQuery.inArray( this, menu1 ) == sub_menu_is_showed1){
                    $(this).parent().find('.header-dropdown').toggleClass('show-header-dropdown');
                    sub_menu_is_showed1 = -1;
                    $("#carrisvg").css("filter","brightness(0) invert(1)");
                    $("#divcarri").css("background-color","transparent");
                }
                else {
                    for (var i = 0; i < menu1.length; i++) {
                        $(menu[i]).parent().find('.header-dropdown').removeClass("show-header-dropdown");

                    }

                    $(this).parent().find('.header-dropdown').toggleClass('show-header-dropdown');
                    sub_menu_is_showed1 = jQuery.inArray( this, menu1 );

                }
        });
    }

    $(".js-show-header-dropdown1, .header-dropdown").click(function(event){
        event.stopPropagation();
    });

    $(window).on("click", function(){
        for (var i = 0; i < menu1.length; i++) {
            $(menu1[i]).parent().find('.header-dropdown').removeClass("show-header-dropdown");
        }
        sub_menu_is_showed1 = -1;
        $("#carrisvg").css("filter","brightness(0) invert(1)");
        $("#divcarri").css("background-color","transparent");
    });

    /*Darkmode*/
    $(".switch__darkmode").each(function() {
        if(localStorage.getItem('darkmode') == "light"){
            $(this).prop("checked", false);
            $("#MainDark").removeClass('main__theme--dark');
            $("#FooterDark").removeClass('main__theme--dark');
            $("#CabeceraDark").removeClass('main__theme--dark');
            $("#BodyDark").removeClass('body__theme--dark');
            $("#PerfilDark").removeClass('perfil__theme--dark');
            $("#switch__darkmode").removeClass('fa-moon');
            $("#PerfilDark").addClass('perfil__theme--light');
            $("#MainDark").addClass('main__theme--light');
            $("#FooterDark").addClass('main__theme--light');
            $("#CabeceraDark").addClass('main__theme--light')
            $("#BodyDark").addClass('body__theme--light');
            $("#switch__darkmode").addClass('fa-sun');
        } else if(localStorage.getItem('darkmode') == "dark"){
            $(this).prop("checked", true);
            $("#switch__darkmode").removeClass('fa-sun');
            $("#BodyDark").removeClass('body__theme--light');
            $("#MainDark").removeClass('main__theme--light');
            $("#FooterDark").removeClass('main__theme--light');
            $("#CabeceraDark").removeClass('main__theme--light');
            $("#PerfilDark").removeClass('perfil__theme--light');
            $("#PerfilDark").addClass('perfil__theme--dark');
            $("#MainDark").addClass('main__theme--dark');
            $("#FooterDark").addClass('main__theme--dark');
            $("#CabeceraDark").addClass('main__theme--dark');
            $("#BodyDark").addClass('body__theme--dark');
            $("#switch__darkmode").addClass('fa-moon');
        }
    });
    
    $(".switch__darkmode").on('click',function() {
        if($(this).is(':checked')){
            localStorage.setItem('darkmode',"dark");
            $(".switch__darkmode").removeClass('fa-sun');
            $("#BodyDark").removeClass('body__theme--light');
            $("#MainDark").removeClass('main__theme--light');
            $("#FooterDark").removeClass('main__theme--light');
            $("#CabeceraDark").removeClass('main__theme--light');
            $("#PerfilDark").removeClass('perfil__theme--light');
            $("#PerfilDark").addClass('perfil__theme--dark');
            $("#MainDark").addClass('main__theme--dark');
            $("#CabeceraDark").addClass('main__theme--dark');
            $("#FooterDark").addClass('main__theme--dark');
            $("#BodyDark").addClass('body__theme--dark');
            $(".switch__darkmode").addClass('fa-moon');

        }else {

            localStorage.setItem('darkmode',"light");
            $(".switch__darkmode").removeClass('fa-moon');
            $("#MainDark").removeClass('main__theme--dark');
            $("#CabeceraDark").removeClass('main__theme--dark');
            $("#FooterDark").removeClass('main__theme--dark');
            $("#BodyDark").removeClass('body__theme--dark');
            $("#PerfilDark").removeClass('perfil__theme--dark');
            $("#PerfilDark").addClass('perfil__theme--light');
            $("#MainDark").addClass('main__theme--light');
            $("#CabeceraDark").addClass('main__theme--light');
            $("#FooterDark").addClass('main__theme--light');
            $("#BodyDark").addClass('body__theme--light');
            $(".switch__darkmode").addClass('fa-sun');
        }
    });

    /*[ Show content Product detail ]
    ===========================================================*/
    $('.active-dropdown-content .js-toggle-dropdown-content').toggleClass('show-dropdown-content');
    $('.active-dropdown-content .dropdown-content').slideToggle('fast');

    $('.js-toggle-dropdown-content').on('click', function(){
        $(this).toggleClass('show-dropdown-content');
        $(this).parent().find('.dropdown-content').slideToggle('fast');
    });

    /*[ Play video 01]
    ===========================================================*/
    var srcOld = $('.video-mo-01').children('iframe').attr('src');

    $('[data-target="#modal-video-01"]').on('click',function(){
        $('.video-mo-01').children('iframe')[0].src += "&autoplay=1";

        setTimeout(function(){
            $('.video-mo-01').css('opacity','1');
        },300);
    });

    $('[data-target="#modal-login"]').on('click',function(){
        $('.video-mo-01').children('iframe')[0].src += "&autoplay=1";

        setTimeout(function(){
            $('.video-mo-01').css('opacity','1');
        },300);
    });

})(jQuery);

//Abrir y cerrar modales
const dom = document.querySelector(".Macrom_page");
const noflow = document.querySelector("#BodyDark");
const sidebar_menu = document.querySelector("#abrirModal6");
const cont_h = document.querySelector(".contenedor__header");
document.querySelectorAll(".click").forEach(el => {
    el.addEventListener("click", e => {
        const id = e.target.getAttribute("id");
        var id_m = id.charAt(10);
        let cerrar = []; let modales = [];
    for(var m = 0; m <= 6 ; m++){
        modales[m] = document.getElementById("ventanaModal"+m);
        cerrar[m] = document.getElementsByClassName("cerrar"+m)[0];
        if(m == id_m){
            modales[m].style.display="block";
            noflow.classList.add("no-overflow");
            dom.classList.add("no-overflow");
            sidebar_menu.classList.remove("fa-bars");
            sidebar_menu.classList.add("fa-reply");
            cont_h.classList.remove("pd-1");
        }
        if(e.target == cerrar[m]){
            modales[m].style.display='none';
            noflow.classList.remove("no-overflow");
            dom.classList.remove("no-overflow");
            sidebar_menu.classList.add("fa-bars");
            sidebar_menu.classList.remove("fa-reply");
            cont_h.classList.add("pd-1");
        }

        }
    });
});

document.querySelectorAll(".closem").forEach(el => {
    el.addEventListener("click", e => {
      let cerrar = []; let modales = [];
      for(var m = 0; m <= 6 ; m++){
        modales[m] = document.getElementById("ventanaModal"+m);
        cerrar[m] = document.getElementsByClassName("cerrar"+m)[0];
        if(e.target == cerrar[m]){
            modales[m].style.display='none';
            noflow.classList.remove("no-overflow");
            dom.classList.remove("no-overflow");
            sidebar_menu.classList.add("fa-bars");
            sidebar_menu.classList.remove("fa-reply");
            cont_h.classList.add("pd-1");
        }
      }
    });
});
    
window.addEventListener("click",function(event){
    let modales = [];
    for(var m = 0; m <= 6 ; m++){
        modales[m] = document.getElementById("ventanaModal"+m);

        if(event.target == modales[m]){
            modales[m].style.display ="none";
            noflow.classList.remove("no-overflow");
            dom.classList.remove("no-overflow");
            sidebar_menu.classList.add("fa-bars");
            sidebar_menu.classList.remove("fa-reply");
            cont_h.classList.add("pd-1");
        }
    }
});
//Abrir y cerrar modales end.

switch(window.location.href){
    case "https://macromautopartes.com/?mod=nosotros":
        document.querySelector("#sidebar4").classList.add("sidebar__active");
    break;
    case "https://macromautopartes.com/?mod=home":
        document.querySelector("#sidebar0").classList.add("sidebar__active");
    break;
    case "https://macromautopartes.com/?mod=Blog":
        document.querySelector("#sidebar9").classList.add("sidebar__active");
    break;
    case "https://macromautopartes.com/?mod=catalogo":
        document.querySelector("#sidebar1").classList.add("sidebar__active");
    break;
    case "https://macromautopartes.com/?mod=Compras":
        document.querySelector("#sidebar2").classList.add("sidebar__active");
    break;
    case "https://macromautopartes.com/?mod=Profile&opc=Session":
        document.querySelector("#sidebar5").classList.add("sidebar__active");
    break;
    case "https://macromautopartes.com/?mod=Profile&opc=Mispedidos":
    case "https://macromautopartes.com/?mod=Profile&opc=Mispedidos_view":
        document.querySelector("#sidebar6").classList.add("sidebar__active");
    break;
    case "https://macromautopartes.com/?mod=Profile&opc=Facturacion":
    case "https://macromautopartes.com/?mod=Profile&opc=Facturacion_add":
    case "https://macromautopartes.com/?mod=Profile&opc=Facturacion_edit":
        document.querySelector("#sidebar8").classList.add("sidebar__active");    
    break;
    case "https://macromautopartes.com/?mod=Profile&opc=Direcciones":
    case "https://macromautopartes.com/?mod=Profile&opc=Direcciones_add":
    case "https://macromautopartes.com/?mod=Profile&opc=Direcciones_edit":
        document.querySelector("#sidebar7").classList.add("sidebar__active");  
    break;
}