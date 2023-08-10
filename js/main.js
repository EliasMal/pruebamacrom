
(function ($) {
    "use strict";

    /*[ Load page ]
    ===========================================================*/
    $(".animsition").animsition({
        inClass: 'fade-in',
        outClass: 'fade-out',
        inDuration: 1500,
        outDuration: 800,
        linkElement: '.animsition-link',
        loading: true,
        loadingParentElement: 'html',
        loadingClass: 'animsition-loading-1',
        loadingInner: '<div data-loader="ball-scale"></div>',
        timeout: false,
        timeoutCountdown: 5000,
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

   /** abrir y cerrar barra navegacion en mobil **/
   var abrirmenu = document.querySelectorAll("#topbar--switch");
   var contnavegacion = document.querySelectorAll(".contenedor__navegacion");
   abrirmenu.forEach((b,i) =>{
       b.addEventListener("click",()=>{
         contnavegacion[i].classList.toggle("abierto")
       });
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

    ///*** Prueba cambio de imagen con click ***/
    $(document).ready(function() {
		$(".secundaria").click(function(){
			$("#principal").attr("src", $(this).attr("src"));
		 });
	});
    
    /*[ Block2 button wishlist ]
    ===========================================================*/
    $('.block2-btn-addwishlist').on('click', function(e){
        e.preventDefault();
        $(this).addClass('block2-btn-towishlist');
        $(this).removeClass('block2-btn-addwishlist');
        $(this).off('click');
    });

    /*[ +/- num product ]
    ===========================================================*/
    $('.btn-num-product-down').on('click', function(e){
        e.preventDefault();
        var numProduct = Number($(this).next().val());
        if(numProduct > 1) $(this).next().val(numProduct - 1);
    });

    $('.btn-num-product-up').on('click', function(e){
        e.preventDefault();
        var numProduct = Number($(this).prev().val());
        $(this).prev().val(numProduct + 1);
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

    $('[data-dismiss="modal"]').on('click',function(){
        $('.video-mo-01').children('iframe')[0].src = srcOld;
        $('.video-mo-01').css('opacity','0');
    });
    
    $('[data-target="#modal-login"]').on('click',function(){
        $('.video-mo-01').children('iframe')[0].src += "&autoplay=1";

        setTimeout(function(){
            $('.video-mo-01').css('opacity','1');
        },300);      
    });
})(jQuery);