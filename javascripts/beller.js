function init() {
  //Scroll animation and offset
  $('a.scroll-link').on("click", function(event) {
    var $anchor = $(this);
    $('html, body').stop().animate({
      scrollTop: ($($anchor.attr('href')).offset().top + 0)
    });
    event.preventDefault();
  });
  // end scroll animation
  //open seadragon //
  $('.openseadragon').each(function() {
    var currentViewer = $(this);
    var currentViewerID = currentViewer.attr('id');
    var recordID = $(this).data('record_id');
    var recordName = $(this).data('record_name');
    var iiifEndpoint = 'https://fitdil.fitnyc.edu/media/iiif/' + recordID + '/' + recordName + '/info.json';
    var viewer = OpenSeadragon({
      id: currentViewerID,
      prefixUrl: 'https://cdn.jsdelivr.net/npm/openseadragon@2.4/build/openseadragon/images/',
      showNavigator: true,
      navigatorSizeRatio: 0.1,
      minZoomImageRatio: 0.8,
      maxZoomPixelRatio: 10,
      controlsFadeDelay: 1000,
      tileSources: iiifEndpoint
    });
    viewer.world.addHandler('add-item', function(event) {
      var tiledImage = event.item;
      tiledImage.addHandler('fully-loaded-change', function() {
        $(currentViewer).parent().children('.loader').remove();
      });
    });
  });

  $('.openseadragon-popup').click(function() {
    var currentViewer = $(this);
    var currentViewerID = currentViewer.attr('id');
    var seadragon_frame = $('<div class="openseadragon-full" id="' + currentViewerID + '-frame"><div class="loader"></div></div>');
    $(this).append(seadragon_frame);
    var recordID = $(this).data('record_id');
    var recordName = $(this).data('record_name');
    var iiifEndpoint = 'https://fitdil.fitnyc.edu/media/iiif/' + recordID + '/' + recordName + '/info.json';
    var viewer = OpenSeadragon({
      id: currentViewerID + '-frame',
      prefixUrl: 'https://cdn.jsdelivr.net/npm/openseadragon@2.4/build/openseadragon/images/',
      showNavigator: true,
      navigatorSizeRatio: 0.1,
      minZoomImageRatio: 0.8,
      maxZoomPixelRatio: 10,
      controlsFadeDelay: 1000,
      tileSources: iiifEndpoint
    });
    viewer.setFullScreen(true).addHandler('full-screen', function(data) {
      if (!data.fullScreen) {
        setTimeout(function() {
          viewer.destroy();
        }, 300);
      };
    });
    viewer.world.addHandler('add-item', function(event) {
      var tiledImage = event.item;
      tiledImage.addHandler('fully-loaded-change', function() {
        $('.loader').remove();
      });
    });
  });
  // end openseadragon
  // owl Slider
  var owl = $('.owl-carousel');
  owl.owlCarousel({
    items: 2,
    margin: 30,
    nav: false,
    dots: false,
  });
  // Go to the next item
  $('#next-slide').click(function() {
    owl.trigger('next.owl.carousel');
  });
  // Go to the previous item
  $('#previous-slide').click(function() {
    // With optional speed parameter
    // Parameters has to be in square bracket '[]'
    owl.trigger('prev.owl.carousel');
  });
  //end owl
  // offcanvas navbar
  $('[data-toggle="offcanvas"]').on('click', function() {
    $('.offcanvas-collapse').toggleClass('open');
    if ($('.offcanvas-collapse').hasClass('open')) {
      $('[data-toggle="offcanvas"]').attr('aria-expanded', 'true');
    } else {
      $('[data-toggle="offcanvas"]').attr('aria-expanded', 'false');
    }
  });
  //fix modals
  $('.modal').each(function() {
    $(this).attr({
      "data-backdrop": false,
      "data-barba-prevent": "all"
    });
  });
  $('.modal-header').each(function() {
    var listItems = $(this).children();
    $(this).append(listItems.get().reverse());
  });
}
$(document).ready(function() {
  init();
  barba.init({
    transitions: [{
        name: 'slide-transition',
        leave(data) {
          if (data.trigger.className.includes("previous") || data.trigger.className.includes("navbar-brand")) {
            return gsap.to(data.current.container, {
              xPercent: 100,
              duration: .75
            });
          } else if (data.trigger.className.includes("nav-link")) {
            $('.offcanvas-collapse').toggleClass('open');
            return gsap.to(data.current.container, {
              yPercent: -100,
              duration: .75,
              delay: .5
            });
          } else {
            return gsap.to(data.current.container, {
              xPercent: -100,
              duration: .75
            });
          }
        },
        enter(data) {
          // need to scroll up on mobile because page height isn't fixed
          if (window.matchMedia('(max-width: 767px)').matches) {
            $("html, body").animate({
              scrollTop: 0
            }, "slow");
          }
          if (data.trigger.className.includes("previous") || data.trigger.className.includes("navbar-brand")) {

            return gsap.from(data.next.container, {
              xPercent: -100,
              duration: .75
            });
          } else if (data.trigger.className.includes("nav-link")) {
            return gsap.from(data.next.container, {
              yPercent: 100,
              duration: .75,
            });
          } else {

            return gsap.from(data.next.container, {
              xPercent: 100,
              duration: .75
            });
          }
        }
      },
      {
        name: 'launch',
        once(data) {
          // only run if it starts on summary page
          if ($('#main').data('barba-namespace') == 'exhibits summary') {
            var rule = CSSRulePlugin.getRule("#exhibit-landing:before");
            let tl = gsap.timeline(); //create the timeline
            return tl.from(rule, {
                transform: "translateY(-100%)",
                duration: .75,
                delay: 1
              })
              .from('#summary-block', {
                xPercent: -150,
                duration: .5,
                ease: "back.out(1.7)"
              });
          }
        }
      },
      //special case because going back to landing messes up slider
      {
        name: 'landing',
        to: {
          namespace: [
            'exhibits summary'
          ],
        },
        enter(data) {
          // need to scroll up on mobile because page height isn't fixed
          if (window.matchMedia('(max-width: 767px)').matches) {
            $("html, body").animate({
              scrollTop: 0
            }, "slow");
          }
          if (data.trigger.className.includes("previous") || data.trigger.className.includes("navbar-brand") || data.trigger.className.includes("nav-link")) {

            return gsap.from(data.next.container, {
              xPercent: -100,
              duration: .75
            });
          } else {

            return gsap.from(data.next.container, {
              xPercent: 100,
              duration: .75
            });
          }
        }
      }
    ]
  });
  barba.hooks.after(() => {
    init();
    ga('set', 'page', window.location.pathname);
    ga('send', 'pageview');
  });
});