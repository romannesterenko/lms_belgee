


$(document).ready(function () {

  $('.select2').select2({
    placeholder: 'Select Me',
    theme: 'which'
  });

  $(".select2-selection__arrow")
    .addClass("select-arrow");


  
  


  $('.tabgroup > div').hide();
  $('.tabgroup > div:first-of-type').show();
  $('.tabs a').click(function(e){
    e.preventDefault();
      var $this = $(this),
          tabgroup = '#'+$this.parents('.tabs').data('tabgroup'),
          others = $this.closest('li').siblings().children('a'),
          target = $this.attr('href');
      others.removeClass('active');
      $this.addClass('active');
      $(tabgroup).children('div').hide();
      $(target).show();
    
  })
      



  $('.file-input').change(function () {
    var curElement = $(this).parent().parent().find('.image');
    console.log(curElement);
    var reader = new FileReader();

    reader.onload = function (e) {
      // get loaded data and render thumbnail.
      curElement.attr('src', e.target.result);
    };

    // read the image file as a data URL.
    reader.readAsDataURL(this.files[0]);
  });

  $('.burger').click(function () {
    if ($('.burger,.main-menu').hasClass('open')) {
      $('.burger,.main-menu').removeClass('open');
    } else {
      $('.burger,.main-menu').addClass('open');
    }
  });

  $('ul.program-accordion a.opener').click(function () {
    $(this).parent().find("ul:first").slideToggle();
    $(this).parent().toggleClass('active');
    return false;
  });


  $('.content-slider.owl-carousel').owlCarousel({
    items: 1,
    loop: true,
    margin: 30,
    nav: true,
    dots: true,
    autoHeight: true,
    responsive: {
      0: {
        nav: false,
        items: 1,
      },
      768: {
        items: 1,
      }
    }
  });







  var sticky_navigation_offset_top = $('header').offset().top;

  // our function that decides weather the navigation bar should have "fixed" css position or not.
  var sticky_navigation = function () {
    var scroll_top = $(window).scrollTop(); // our current vertical position from the top

    // if we've scrolled more than the navigation, change its position to fixed to stick to top, otherwise change it back to relative
    if (scroll_top > sticky_navigation_offset_top) {
      $('header').addClass('fixed');
    } else {
      $('header').removeClass('fixed');
    }
  };

  // run our function on load
  sticky_navigation();

  // and run it again every time you scroll
  $(window).scroll(function () {
    sticky_navigation();
  });

  $(".toggle-password").click(function (e) {
    e.preventDefault();

    $(this).toggleClass("toggle-password");
    if (clicked == 0) {
      $(this).html('<span class="material-icons"><svg class="icon"><use xlink:href="#eye-icon"></use></svg></span>');
      clicked = 1;
    } else {
      $(this).html('<span class="material-icons"><svg class="icon"><use xlink:href="#eye-icon"></use></svg></span>');
      clicked = 0;
    }

    var input = $($(this).attr("toggle"));
    if (input.attr("type") == "password") {
      input.attr("type", "text");
    } else {
      input.attr("type", "password");
    }
  });






  function move1Tomove2() {
    if (!document.querySelector(".move-1 > .move-2")) {
      var move2Block = document.querySelector(".move-2");
      var move1Block = document.querySelector(".move-1");
      if (move2Block && move1Block) {
        move2Block.appendChild(move1Block);
      }
    }
  }

  function handleResize() {
    var width = window.innerWidth;

    if (width < 992) {
      move1Tomove2();
      return;
    }

  }

  window.addEventListener("resize", handleResize);
  handleResize();





  
  

});


if (document.documentElement.clientWidth < 992) {
  $('.carousel.owl-carousel').owlCarousel({
    items: 2,
    margin: 30,
    nav: false,
    dots: true,
    responsive: {
      0: {
        items: 1,
      },
      768: {
        items: 2,
      }
    }
  });




}






if (document.documentElement.clientWidth < 768) {


  // inspired by http://jsfiddle.net/arunpjohny/564Lxosz/1/
  $('.table-responsive-stack').each(function (i) {
    var id = $(this).attr('id');
    //alert(id);
    $(this).find("th").each(function (i) {
      $('#' + id + ' td:nth-child(' + (i + 1) + ')').prepend('<span class="table-responsive-stack-thead">' + $(this).text() + ':</span> ');
      $('.table-responsive-stack-thead').hide();

    });

  });

  $('.table-responsive-stack').each(function () {
    var thCount = $(this).find("th").length;
    var rowGrow = 100 / thCount + '%';
    //console.log(rowGrow);
    $(this).find("th, td").css('flex-basis', rowGrow);
  });




  function flexTable() {
    if ($(window).width() < 768) {

      $(".table-responsive-stack").each(function (i) {
        $(this).find(".table-responsive-stack-thead").show();
        $(this).find('thead').hide();
      });


      // window is less than 768px   
    } else {


      $(".table-responsive-stack").each(function (i) {
        $(this).find(".table-responsive-stack-thead").hide();
        $(this).find('thead').show();
      });



    }
    // flextable   
  }

  flexTable();

  window.onresize = function (event) {
    flexTable();
  };


}


var tag = document.createElement('script');
tag.src = "https://www.youtube.com/iframe_api";
document.body.appendChild(tag);

// When the YouTube API code loads, it calls this function, so it must be global
// and it must be named exactly onYouTubeIframeAPIReady.
window.onYouTubeIframeAPIReady = function () {
  var videoModules = document.querySelectorAll('.video');
  // for Internet Explorer 11 and below, convert array-like NodeList to an actual Array.
  videoModules = Array.prototype.slice.call(videoModules);
  videoModules.forEach(initializeVideoModule);
}

function initializeVideoModule(videoModule) {
  var player = new YT.Player(videoModule.querySelector('.video-placeholder'), {
    videoId: videoModule.dataset.videoId,
    events: {
      onStateChange: function (event) {
        var isEnded = event.data === YT.PlayerState.ENDED;
        // 'playing' css class controls fading the video and preivew images in/out.
        // Internet Explorer 11 and below do not support a second argument to `toggle`
        // videoModule.classList.toggle('playing', !isEnded);
        videoModule.classList[isEnded ? 'remove' : 'add']('playing');
        // if the video is done playing, remove it and re-initialize
        if (isEnded) {
          player.destroy();
          videoModule.querySelector('.video-layer').innerHTML = (
            '<div class="video-placeholder"></div>'
          );
          initializeVideoModule(videoModule);
        }
      }
    }
  });
}


 
