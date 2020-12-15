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
      "data-barba-prevent": "all"
    }).appendTo("body");
  });
  $('.card-footer').attr({
    "data-barba-prevent": "all"
  });
  $('.modal-header').each(function() {
    var listItems = $(this).children();
    $(this).append(listItems.get().reverse());
  });
  //fix FITting Room nav
  if (document.getElementsByTagName("title")[0].innerHTML.includes("FITting Room")) {
    $('.info').each(function() {
      var footer = $(this).parent();
      var nav = footer.find('.nav-pills');
      var new_div = $("<li class='nav-item'></li>");
      new_div.append($(this).addClass('nav-link'));
      nav.append(new_div).addClass('justify-content-between align-items-center');
    });
  }
  // collection api infitinite scroll
  if ($('#collection-api').length && $('#collection-api').data('collection-id')) {
    var collection = $('#collection-api').data('collection-id');
    var index = 1;
    loadCollection(collection, index);
    $('#load-collection').click(function() {
      index++;
      loadCollection(collection, index);
    });
  }
}

function loadCollection(collection, index) {
  $.getJSON("/api/items?collection=" + collection + "&page=" + index + "&per_page=30", function(data, status, xhr) {
    var total_results = xhr.getResponseHeader("Omeka-Total-Results");
    var index_max = Math.ceil(total_results / 30);
    if (index_max == index) {
      $('#load-collection').attr("disabled", true)
    }
    $.each(data, function(i, item) {
      element_texts = item.element_texts;
      var item_id = item.id;
      var record_name = null;
      var record_id = null;
      var title = '';
      for (var i = 0; i < element_texts.length; i++) {
        if (element_texts[i].element.name == "Record Name") {
          record_name = element_texts[i].text
        }
        if (element_texts[i].element.name == "Record ID") {
          record_id = element_texts[i].text
        }
        if (element_texts[i].element.name == "Title") {
          title = element_texts[i].text
        }
      }
      if (record_name && record_id) {
        var image_url = 'https://fitdil.fitnyc.edu/media/get/' + record_id + '/' + record_name + '/400x400/';
        var card = `
          <div class="col">
            <div class="card mb-2">
              <img class="card-img-top openseadragon-popup" id="openseadragon` + record_id + `" src="` + image_url + `" alt="` + title + `" data-record_id="` + record_id + `" data-record_name="` + record_name + `" />
              <div class="card-footer gallery-caption">
              <button class="info" type="button" name="button" data-toggle="modal" data-target="#record-modal` + record_id + `">
                <i class="fas fa-info-circle"></i><span class="sr-only">Information</span>
              </button>
              </div>
            </div>
          </div>
        `;
        var modal = `
        <!-- Record Modal -->
        <div class="modal fade" id="record-modal` + record_id + `" tabindex="-1" role="dialog" aria-labelledby="myModalLabel` + record_id + `" data-barba-prevent="all">
          <div class="modal-dialog" role="document">
            <div class="modal-content">
              <div class="modal-header">
              <span class="modal-title font-weight-bold" id="myModalLabel` + record_id + `">Item Information</span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
              </div>
              <div class="modal-body">
                <h2>` + title + `</h2>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <a href="/items/show/` + item_id + `" class="btn btn-primary" role="button">View full record</a>
              </div>
            </div>
          </div>
        </div>
        `
        $(card).appendTo('#collection-api');
        $(modal).appendTo("body");

      }
    });
    $('#load-collection').removeClass('d-none');
    //redo OpenSeadragon
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
            let tl = gsap.timeline(); //create the timeline
            return tl.from('#exhibit-landing', {
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