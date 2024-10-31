/*
 * roar - v1.0.5 - 2018-05-25
 * Copyright (c) 2018 Ciprian Popescu
 * Licensed GPLv3
 */
function roar(e,r,t){"use strict";if("object"!=typeof t&&(t={}),window.roarAlert)window.roarAlert.cancel&&(window.roarAlert.cancelElement.style=""),window.roarAlert.confirm&&(window.roarAlert.confirmElement.style=""),document.body.classList.add("roar-open"),window.roarAlert.element.style.display="block",a=window.roarAlert;else{var a={element:null,cancelElement:null,confirmElement:null};a.element=document.querySelector(".roar-alert")}if(a.cancel=void 0!==t.cancel?t.cancel:!1,a.cancelText=void 0!==t.cancelText?t.cancelText:"Cancel",a.cancelCallBack=function(e){return document.body.classList.remove("roar-open"),window.roarAlert.element.style.display="none","function"==typeof t.cancelCallBack&&t.cancelCallBack(e),!0},document.querySelector(".roar-alert-mask")&&document.querySelector(".roar-alert-mask").addEventListener("click",function(e){return document.body.classList.remove("roar-open"),window.roarAlert.element.style.display="none","function"==typeof t.cancelCallBack&&t.cancelCallBack(e),!0}),a.message=r,a.title=e,a.confirm=void 0!==t.confirm?t.confirm:!0,a.confirmText=void 0!==t.confirmText?t.confirmText:"Confirm",a.confirmCallBack=function(e){return document.body.classList.remove("roar-open"),window.roarAlert.element.style.display="none","function"==typeof t.confirmCallBack&&t.confirmCallBack(e),!0},!a.element){a.html='<div class="roar-alert" id="roar-alert" role="alertdialog"><div class="roar-alert-mask"></div><div class="roar-alert-message-body" role="alert" aria-relevant="all"><div class="roar-alert-message-tbf roar-alert-message-title">'+a.title+'</div><div class="roar-alert-message-tbf roar-alert-message-content">'+a.message+'</div><div class="roar-alert-message-tbf roar-alert-message-button">',a.cancel,a.html+='<a href="javascript:;" class="roar-alert-message-tbf roar-alert-message-button-cancel">'+a.cancelText+"</a>",a.confirm,a.html+='<a href="javascript:;" class="roar-alert-message-tbf roar-alert-message-button-confirm">'+a.confirmText+"</a>",a.html+="</div></div></div>";var l=document.createElement("div");l.id="roar-alert-wrap",l.innerHTML=a.html,document.body.appendChild(l),a.element=document.querySelector(".roar-alert"),a.cancelElement=document.querySelector(".roar-alert-message-button-cancel"),a.cancel?document.querySelector(".roar-alert-message-button-cancel").style.display="block":document.querySelector(".roar-alert-message-button-cancel").style.display="none",a.confirmElement=document.querySelector(".roar-alert-message-button-confirm"),a.confirm?document.querySelector(".roar-alert-message-button-confirm").style.display="block":document.querySelector(".roar-alert-message-button-confirm").style.display="none",a.cancelElement.onclick=a.cancelCallBack,a.confirmElement.onclick=a.confirmCallBack,window.roarAlert=a}document.querySelector(".roar-alert-message-title").innerHTML="",document.querySelector(".roar-alert-message-content").innerHTML="",document.querySelector(".roar-alert-message-button-cancel").innerHTML=a.cancelText,document.querySelector(".roar-alert-message-button-confirm").innerHTML=a.confirmText,a.cancelElement=document.querySelector(".roar-alert-message-button-cancel"),a.cancel?document.querySelector(".roar-alert-message-button-cancel").style.display="block":document.querySelector(".roar-alert-message-button-cancel").style.display="none",a.confirmElement=document.querySelector(".roar-alert-message-button-confirm"),a.confirm?document.querySelector(".roar-alert-message-button-confirm").style.display="block":document.querySelector(".roar-alert-message-button-confirm").style.display="none",a.cancelElement.onclick=a.cancelCallBack,a.confirmElement.onclick=a.confirmCallBack,a.title=a.title||"",a.message=a.message||"",document.querySelector(".roar-alert-message-title").innerHTML=a.title,document.querySelector(".roar-alert-message-content").innerHTML=a.message,window.roarAlert=a}



function imgOnError(element) {
    element.onerror = null;
    element.src = '/wp-content/plugins/property-drive/assets/images/no-image.jpg';
}



function getQueryParameters() {
    var queryString = location.search.slice(1),
        params = {};

    queryString.replace(/([^=]*)=([^&]*)&*/g, function (_, key, value) {
        params[key] = value;
    });

    return params;
}
function setQueryParameters(params) {
    var query = [],
        key,
        value;

    for (key in params) {
        if (!params.hasOwnProperty(key)) {
            continue;
        }
        value = params[key];
        query.push(key + "=" + value);
    }

    location.search = query.join("&");
}



function get_favourites() {
    // Get favorites from local storage or empty array
    var favourites = JSON.parse(localStorage.getItem('favourites')) || [];

    // Add class 'fav' to each favorite
    favourites.forEach(function (favourite) {
        if (document.querySelector('.pd-box-favourite[data-property-id="' + favourite + '"]')) {
            document.querySelector('.pd-box-favourite[data-property-id="' + favourite + '"]').classList.add('favourite');
        }
    });

    // Register click event listener
    document.querySelector('body').addEventListener('click', function (e) {
        var id = parseInt(e.target.dataset.propertyId, 10),
            index = favourites.indexOf(id);

        if (index == -1) {
            if (!isNaN(id)) {
                favourites.push(id);
                document.querySelector('h1.single-property-title .pd-box-favourite[data-property-id="' + id + '"]').classList.add('favourite');
            }
        } else {
            favourites.splice(index, 1);
            document.querySelector('h1.single-property-title .pd-box-favourite[data-property-id="' + id + '"]').classList.remove('favourite');
        }
        localStorage.setItem('favourites', JSON.stringify(favourites));

        // Save to database if user is logged in
        var request = new XMLHttpRequest();

        request.open('POST', wp4pmAjaxVar.ajaxurl, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.onload = function () {
            if (this.status >= 200 && this.status < 400) {
                // Response success
            }
        };
        request.send('action=save_user_favourite&property_id=' + id);
    });
}



/**
 * XHR API wrapper
 *
 * A wrapper for XHR API
 *
 * Usage:
 * apiPostXhr(wp4pmAjaxVar.ajaxurl, 'pd_property_view_increment', '&propertyId=' + propertyId)
 *
 * @param url string
 * @param data string
 */
function apiPostXhr(url, action, data) {
    var request = new XMLHttpRequest();

    request.open('POST', url, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.onload = function () {
        if (this.status >= 200 && this.status < 400) {
            // Response success
        } else {
            // Response error
        }
    };
    request.onerror = function() {
        // Connection error
    };
    request.send('action=' + action + data);
}



function getDetailsById(id) {
    var propertyTitle = document.querySelector(".property-grid .pid-" + id + " .property-card--title").innerHTML;
    var propertyUri = document.querySelector(".property-grid .pid-" + id).dataset.uri;

    return '<a href="' + propertyUri + '">' + propertyTitle + '</a>';
}

function destroyMap(map) {
    map.off();
    map.remove();

    getMapMarkers();
}

/**
 * Build map markers
 *
 * Build map markers using grid elements
 *
 *
 * @param url string
 * @param data string
 */
function getMapMarkers() {
    let osmMap = L.map('osm-map', {
        preferCanvas: false
    }).setView([0, 0], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(osmMap);
    L.control.scale().addTo(osmMap);

    // Markers
    let elements = document.querySelectorAll('.supernova-map-search .property-grid .property-card.has-coordinates'),
        addressPoints = [],
        //circleMarker, // Circle markers
        latitude,
        longitude,
        title,
        marker,
        i,
        icon = new L.Icon.Default();

    icon.options.shadowSize = [0, 0];

    let customIcon = L.divIcon({
        className: 'supernova-map-marker',
        html: '<svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="map-marker-alt" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" class="svg-inline--fa fa-map-marker-alt fa-w-12 fa-3x"><path fill="currentColor" d="M172.268 501.67C26.97 291.031 0 269.413 0 192 0 85.961 85.961 0 192 0s192 85.961 192 192c0 77.413-26.97 99.031-172.268 309.67-9.535 13.774-29.93 13.773-39.464 0zM192 272c44.183 0 80-35.817 80-80s-35.817-80-80-80-80 35.817-80 80 35.817 80 80 80z" class=""></path></svg>',
        iconSize: [30, 42],
        iconAnchor: [15, 42],
        shadowSize: [0, 0]
    });

    for (i = 0; i < elements.length; i++) {
        latitude = elements[i].dataset.coordinates.split('|')[0];
        longitude = elements[i].dataset.coordinates.split('|')[1];
        title = elements[i].dataset.coordinates.split('|')[2];

        addressPoints.push([parseFloat(latitude), parseFloat(longitude)]);

        // Regular marker
        marker = new L.marker([parseFloat(latitude), parseFloat(longitude)], {
            title: title,
            icon: customIcon
        }).bindPopup(getDetailsById(title)).addTo(osmMap);
    }

    // Marker clusterer
    /**
    var markers = L.markerClusterGroup({
        maxClusterRadius: 0,
        spiderfyOnMaxZoom: true,
        showCoverageOnHover: false,
        zoomToBoundsOnClick: false,
        removeOutsideVisibleBounds: true,
        chunkedLoading: true
    });
    /**/


    //for (i = 0; i < addressPoints.length; i++) {
    //    a = addressPoints[i];
    //    title = a[2];
        //marker = L.marker(new L.LatLng(a[0], a[1]), {
        //    title: title,
        //    icon: icon
        //});
        //marker.bindPopup(getDetailsById(title));
        //marker.addTo(osmMap); // Regular marker
        //markers.addLayer(marker); // Marker clusterer

        /**
        circleMarker = L.circleMarker(new L.LatLng(a[0], a[1]), {
            color: '#000000',
            fillColor: '#3388ff',
            weight: 1,
            radius: 10,
            fillOpacity: 0.5,
            title: title
        });
        circleMarker.bindPopup(getDetailsById(title));
        circleMarker.addTo(osmMap);
        /**/
    //}
    //osmMap.addLayer(markers); // Marker clusterer


    osmMap.fitBounds([addressPoints]);

    var currentZoom = parseInt(osmMap.getZoom());
    osmMap.setZoom(currentZoom - 1);



    // http://jsfiddle.net/cxZRM/
    osmMap.on('dragend zoomend viewreset autopanstart', function () {
        updateMarkers(osmMap);
    });
    /**
    markers.on('clusterclick', function () {
        updateMarkers(osmMap);
    });
    /**/

    /*
    osmMap.on("moveend", function () {
      console.log(osmMap.getCenter().toString());
      var features = [];
      osmMap.eachLayer(function (layer) {
          if (layer instanceof L.Marker) {
              if (osmMap.getBounds().contains(layer.getLatLng())) {
                  if (layer.options.title) {
                      features.push(layer.options.title);
                  }
              }
          }
      });
      console.log(features);
    });
    /**/
}


function updateMarkers(map) {
    var features = [];

    map.eachLayer(function (layer) {
        if (layer instanceof L.Marker) {
            if (map.getBounds().contains(layer.getLatLng())) {
                if (layer.options.title) {
                    features.push(layer.options.title);
                }
            }
        } else if (layer instanceof L.CircleMarker) {
            if (map.getBounds().contains(layer.getLatLng())) {
                console.log(layer);
                if (layer.options.title) {
                    features.push(layer.options.title);
                }
            }
        }
    });

    document.querySelector('.supernova-map-search').innerHTML = 'Loading...';

    var request = new XMLHttpRequest();

    request.open('POST', wp4pmAjaxVar.ajaxurl, true);
    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
    request.onload = function () {
        if (this.status >= 200 && this.status < 400) {
            if (document.querySelector('.supernova-map-search')) {
                document.querySelector('.supernova-map-search').innerHTML = this.response;
            }
        } else {
            // Response error
        }
    };
    request.onerror = function() {
        // Connection error
    };
    request.send('action=get_properties_by_id&ids=' + features.join(', '));

    //console.log(features);
    //document.querySelector('.supernova-map-search').innerHTML = features.join(', ');
}










/**
 * Loads an HTML document from a URL and retuns an element selected using
 * the 'selector' parameter
 *
 * Example usage: loadPageSection('./myUri', '#container', callbackFunction());
 *
 * @method loadPageSection
 * @param  {String} url
 * @param  {String} selector - A valid CSS selector
 * @param  {Function} callback - To be called with two parameters (response, error)
 * @return {void} - The Element collected from the loaded page.
 */
window.loadPageSection = function loadPageSection(url, selector, callback) {
    if (typeof url !== 'string') {
        throw new Error('Invalid URL: ', url);
    } else if (typeof selector !== 'string') {
        throw new Error('Invalid selector selector: ', selector);
    } else if (typeof callback !== 'function') {
        throw new Error('Callback provided is not a function: ', callback);
    }

    let xhr = new XMLHttpRequest();

    var finished = false;
    xhr.onabort = xhr.onerror = function xhrError() {
        finished = true;
        callback(null, xhr.statusText);
    };

    xhr.onreadystatechange = function xhrStateChange() {
        if (xhr.readyState === 4 && !finished) {
            finished = true;
            var section;
            try {
                section = xhr.responseXML.querySelector(selector);
                callback(section);
            } catch (e) {
                callback(null, e);
            }
        }
    };

    xhr.open('GET', url);
    xhr.responseType = 'document';
    xhr.send();
};







/**
 * onDOMLoaded routines
 */
document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('.supernova-view--map--ajax')) {
        document.querySelector('.supernova-view--map--ajax a').addEventListener('click', function (e) {
            e.preventDefault();

            let post_link = document.querySelector('.supernova-view--map--ajax a').getAttribute('href');

            document.getElementById('map-ajax-container').classList.add('on');
            document.getElementById('map-ajax-container').innerHTML = '<div class="map-ajax-close">&#10005;</div><div class="map-ajax-loader"><img src="/wp-content/plugins/property-drive/assets/images/map-loader.gif"><br><br>Loading map navigator...</div>';

            loadPageSection(post_link, '.wrap-content-inner', function (r, err) {
                document.getElementById('map-ajax-container').innerHTML = '<div class="map-ajax-close">&#10005;</div>' + r.innerHTML;
                document.querySelector('#map-ajax-container .parent-flex').classList.remove('supernova-fullwidth');

                getMapMarkers();
            });
        });
    }

    if (document.getElementById('map-ajax-container')) {
        document.getElementById('map-ajax-container').addEventListener('click', function (e) {
            if (e.target.className === 'map-ajax-close') {
                document.getElementById('map-ajax-container').classList.remove('on');
                document.getElementById('map-ajax-container').innerHTML = '';
            }
        });
    }




    tail.select('#location-multi', {
        multiple: true,
        multiSelectAll: true,
        placeholder: 'Select Location(s)...',
        search: true,
        classNames: 'wp4pm-flex-item',
    });

    tail.select('#property_multitype_select', {
        multiple: true,
        multiSelectAll: true,
        placeholder: 'Select Property Type(s)...',
        search: true,
        classNames: 'wp4pm-flex-item',
        descriptions: true,
    });

    tail.select('#property_features_select', {
        multiple: true,
        multiSelectAll: true,
        placeholder: 'Select Property Feature(s)...',
        search: true,
        classNames: 'wp4pm-flex-item',
        descriptions: true,
    });

    if (document.querySelector('.properties-osm-map')) {
        window.addEventListener("load", function () {
            getMapMarkers();
        }, false);
    }

    if (document.querySelector('.supernova-grid-view-type')) {
        document.getElementById('supernova-view--grid').addEventListener('click', function () {
            document.querySelector('.parent-flex').classList.add('grid--grid');
            document.querySelector('.parent-flex').classList.remove('grid--split-map');
            document.querySelector('.parent-flex').classList.remove('grid--summary');

            document.querySelector('.parent-flex').classList.remove('supernova-fullwidth');

            localStorage.setItem('supernovaGridViewType', 'grid');

            document.cookie = 'supernovaGridViewType=grid;path=/;max-age=2592000;Secure';
        });
        document.getElementById('supernova-view--list').addEventListener('click', function () {
            document.querySelector('.parent-flex').classList.add('grid--summary');
            document.querySelector('.parent-flex').classList.remove('grid--split-map');
            document.querySelector('.parent-flex').classList.remove('grid--grid');

            document.querySelector('.parent-flex').classList.remove('supernova-fullwidth');

            localStorage.setItem('supernovaGridViewType', 'summary');

            document.cookie = 'supernovaGridViewType=summary;path=/;max-age=2592000;Secure';
        });
    }



    // Increment property views
    if (document.querySelector('.single-property article.property')) {
        var propertyId = document.querySelector('.single-property article.property').dataset.pid;

        apiPostXhr(wp4pmAjaxVar.ajaxurl, 'pd_property_view_increment', '&propertyId=' + propertyId);
    }
    //

    if (document.querySelector('.listing-video')) {
        document.querySelector('.listing-video').addEventListener('click', function () {
            document.documentElement.scrollTop = document.querySelector('iframe').offsetTop;
        });
    }



    if (document.querySelector('.single-property-carousel-main')) {
        var flkty = new Flickity(document.querySelector('.single-property-carousel-main'), {
            contain: true,
            wrapAround: true,
            imagesLoaded: true,
            adaptiveHeight: false,
            lazyLoad: false,
            pageDots: false,
            fullscreen: true
        });

        flkty.on('staticClick', function () {
            flkty.toggleFullscreen();
        });
    }



    /**
     * Search action and attached events
     */
    if (document.querySelector('#wp4pm-search .wp4pm-btn-primary')) {
        /**
         * Detect search action and perform validation and form persistence events
         */
        document.querySelector('#wp4pm-search .wp4pm-btn-primary').addEventListener('click', function () {
            document.querySelector('.wp4pm-search-progress-wrap').classList.add('on');
        });

        /**
         * Detect property status change and replace min/max price
         */
        if (document.getElementById('property_status')) {
            document.getElementById('property_status').addEventListener('change', function () {
                if (this.value === 'to-let' || this.value === 'To Let') {
                    document.getElementById('min_price').innerHTML = '<option value="">Min Price</option><option value="100">100</option><option value="250">250</option><option value="500">500</option><option value="1000">1000</option>';
                    document.getElementById('max_price').innerHTML = '<option value="">Max Price</option><option value="1000">1,000</option><option value="1500">1,500</option><option value="2000">2,000</option><option value="3000">3,000</option><option value="4000">4,000</option><option value="5000">5,000</option><option value="6000">6,000</option><option value="000">7,000</option><option value="8000">8,000</option><option value="9000">9,000</option><option value="10000">10,000</option>';
                } else if (this.value === 'for-sale' || this.value === 'For Sale') {
                    document.getElementById('min_price').innerHTML = '<option value="" selected disabled>Min Price</option><option value="10000">10,000</option><option value="20000">20,000</option><option value="50000">50,000</option><option value="75000">75,000</option><option value="100000">100,000</option><option value="200000">200,000</option><option value="300000">300,000</option>';
                    document.getElementById('max_price').innerHTML = '<option value="" selected disabled>Max Price</option><option value="10000">10,000</option><option value="20000">20,000</option><option value="50000">50,000</option><option value="75000">75,000</option><option value="100000">100,000</option><option value="200000">200,000</option><option value="300000">300,000</option><option value="400000">400,000</option><option value="500000">500,000</option><option value="600000">600,000</option><option value="700000">700,000</option><option value="800000">800,000</option><option value="900000">900,000</option><option value="1000000">1,000,000</option>';
                }
            });
        }
    }

    /**
     * Detect sorting change and rerun query
     */
    if (document.getElementById('pd_order')) {
        if (localStorage.getItem('propertySort')) {
            document.getElementById('pd_order').value = localStorage.getItem('propertySort');
        }

        document.getElementById('pd_order').addEventListener('change', function () {
            var params = getQueryParameters(),
                selectedParameters = document.getElementById('pd_order').value.split('|'),
                orderBy = selectedParameters[0],
                orderDirection = selectedParameters[1];

            params.orderby = orderBy;
            params.order_direction = orderDirection;
            setQueryParameters(params);

            localStorage.setItem('propertySort', document.getElementById('pd_order').value);
        });
    }



    /**
     * Basic favourites engine
     *
     * Local storage stores strings so we use JSON to stringify for storage and parse to get out of storage
     */
    if (document.querySelector('.pd-box-favourite')) {
        get_favourites();
    }



    if (document.getElementById('pd-favourites')) {
        var favouritesFetch = JSON.parse(localStorage.getItem('favourites')) || [],
            favouritesArray = [];

        document.getElementById('pd-favourites').innerHTML = 'Loading your favourite properties...';
        favouritesFetch.forEach(function (favouriteFetch) {
            favouritesArray.push(favouriteFetch);
        });

        // Fetch favourites
        var request = new XMLHttpRequest(),
            favourites = favouritesArray;

        request.open('POST', wp4pmAjaxVar.ajaxurl, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.onload = function () {
            if (this.status >= 200 && this.status < 400) {
                document.getElementById('pd-favourites').innerHTML = this.response;

                get_favourites();
            }
        };
        request.send('action=pd_favourites_fetch_public&favourites=' + favourites);
    }


    if (document.querySelector('.remove-user-favourite')) {
        [].forEach.call(document.querySelectorAll('.remove-user-favourite'), function (favourite) {
            favourite.addEventListener('click', function (event) {
                event.preventDefault();

                var request = new XMLHttpRequest(),
                    favouriteId = favourite.dataset.favouriteId;

                request.open('POST', wp4pmAjaxVar.ajaxurl, true);
                request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                request.onload = function () {
                    if (this.status >= 200 && this.status < 400) {
                        document.getElementById('favourite-row-' + favouriteId).remove();
                    }
                };
                request.send('action=remove_user_favourite&favourite_id=' + favouriteId);
            });
        });
    }



    document.querySelector('body').addEventListener('click', function (e) {
        /**
         * Contact form popup
         */
        if ((e.target.tagName.toLowerCase() === 'a' && e.target.href.indexOf('mailto:') > -1) || e.target.className === 'button-enquire') {
            e.preventDefault();

            var emailAddress = e.target.href.replace('mailto:', ''),
                contactHtml = '<p>' +
                    '<input type="email" id="contact-to" value="' + emailAddress + '" readonly><br>' +
                    '<input type="text" id="contact-name" placeholder="Full Name"><br>' +
                    '<input type="email" id="contact-email" placeholder="Email"><br>' +
                    '<input type="text" id="contact-phone" placeholder="Phone"><br>' +
                    '<textarea id="contact-message" rows="3"></textarea>' +
                '</p>';

            var options = {
                cancel: true,
                cancelText: 'Cancel',
                cancelCallBack: function () {
                    //console.log('options.cancelCallBack');
                },
                confirm: true,
                confirmText: 'Send',
                confirmCallBack: function () {
                    var request = new XMLHttpRequest(),
                        pId = 0;

                    if (document.querySelector('article.property')) {
                        pId = document.querySelector('article.property').dataset.pid;
                    }

                    var pTo = document.getElementById('contact-to').value,
                        pName = document.getElementById('contact-name').value,
                        pEmail = document.getElementById('contact-email').value,
                        pPhone = document.getElementById('contact-phone').value,
                        pMessage = document.getElementById('contact-message').value;

                    request.open('POST', wp4pmAjaxVar.ajaxurl, true);
                    request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
                    request.onload = function () {
                        if (this.status >= 200 && this.status < 400) {
                            roar('Quick Contact', 'Thank you for your request! We will contact you shortly.', {confirmText: 'Close'});
                        }
                    };
                    request.send('action=pd_request_contact&id=' + pId + '&to=' + pTo + '&name=' + pName + '&email=' + pEmail + '&phone=' + pPhone + '&message=' + pMessage);
                }
            }

            roar('Quick Contact', 'Fill in the fields below and click send.' + contactHtml + '.', options);
        } else if (e.target.className === 'contact-action') {
            e.preventDefault();

            var request = new XMLHttpRequest();

            var pId = document.querySelector('article.property').dataset.pid,
                pTo = document.getElementById('contact-to').value,
                pName = document.getElementById('contact-name').value,
                pEmail = document.getElementById('contact-email').value,
                pPhone = document.getElementById('contact-phone').value,
                pMessage = document.getElementById('contact-message').value;

            request.open('POST', wp4pmAjaxVar.ajaxurl, true);
            request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
            request.onload = function () {
                if (this.status >= 200 && this.status < 400) {
                    roar('Quick Contact', 'Thank you for your request! We will contact you shortly.', {confirmText: 'Close'});

                    // Reset fields on success
                    document.getElementById('contact-name').value = '';
                    document.getElementById('contact-email').value = '';
                    document.getElementById('contact-phone').value = '';
                    document.getElementById('contact-message').value = '';
                }
            };
            request.send('action=pd_request_contact&id=' + pId + '&to=' + pTo + '&name=' + pName + '&email=' + pEmail + '&phone=' + pPhone + '&message=' + pMessage);
        }
    });



    /**
     * Map & Street View
     */
    if (document.querySelector('.single-property') && document.getElementById('map-modal')) {
        var modal = document.getElementById('map-modal'),
            modalTabTriggers = document.querySelectorAll('.map-modal-tab');

        document.querySelector('.map-modal-button').addEventListener('click', function (event) {
            event.preventDefault();

            modal.style.display = 'block';
        });
        document.querySelector('.map-modal-close').addEventListener('click', function () {
            modal.style.display = 'none';
        });

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });

        [].forEach.call(modalTabTriggers, function (tab) {
            tab.addEventListener('click', function (event) {
                event.preventDefault();

                supernovaSwitchTab(tab.id, tab.dataset.open)
            });
        });

        function supernovaSwitchTab(modal_tab_id, modal_tab_content) {
            var x = document.getElementsByClassName("map-modal-tab-content"),
                i;

            for (i = 0; i < x.length; i++) {
                x[i].style.display = 'none';
            }
            document.getElementById(modal_tab_content).style.display = 'block';

            x = document.getElementsByClassName("map-modal-tab");

            for (i = 0; i < x.length; i++) {
                x[i].className = 'map-modal-tab';
            }
            document.getElementById(modal_tab_id).className = 'map-modal-tab active';
        }
    }



    /**
     * Details/summary HTML element
     * Only open one element at a time
     */
    if (document.querySelector('details')) {
        // Fetch all the details elements
        const details = document.querySelectorAll('details');

        // Add onclick listeners
        details.forEach((targetDetail) => {
            targetDetail.addEventListener("click", () => {
                // Close all details that are not targetDetail
                details.forEach((detail) => {
                    if (detail !== targetDetail) {
                        detail.removeAttribute("open");
                    }
                });
            });
        });
    }
});


window.addEventListener('scroll', function () {
    if (document.querySelector('.pd-section-breakdown')) {
        if (window.scrollY > (600 - document.querySelector('header nav').offsetHeight)) {
            document.querySelector('.pd-section-breakdown').classList.add('fixed');
        } else {
            document.querySelector('.pd-section-breakdown').classList.remove('fixed');
        }
    }
});
