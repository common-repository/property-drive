/**
 * Flickity fade v1.0.0
 * Fade between Flickity slides
 *
 */
!function(e,t){"function"==typeof define&&define.amd?define(["flickity/js/index","fizzy-ui-utils/utils"],t):"object"==typeof module&&module.exports?module.exports=t(require("flickity"),require("fizzy-ui-utils")):t(e.Flickity,e.fizzyUIUtils)}(this,function(e,t){var i=e.Slide,s=i.prototype.updateTarget;i.prototype.updateTarget=function(){if(s.apply(this,arguments),this.parent.options.fade){var e=this.target-this.x,t=this.cells[0].x;this.cells.forEach(function(i){var s=i.x-t-e;i.renderPosition(s)})}},i.prototype.setOpacity=function(e){this.cells.forEach(function(t){t.element.style.opacity=e})};var a=e.prototype;e.createMethods.push("_createFade"),a._createFade=function(){this.fadeIndex=this.selectedIndex,this.prevSelectedIndex=this.selectedIndex,this.on("select",this.onSelectFade),this.on("dragEnd",this.onDragEndFade),this.on("settle",this.onSettleFade),this.on("activate",this.onActivateFade),this.on("deactivate",this.onDeactivateFade)};var d=a.updateSlides;a.updateSlides=function(){d.apply(this,arguments),this.options.fade&&this.slides.forEach(function(e,t){var i=t==this.selectedIndex?1:0;e.setOpacity(i)},this)},a.onSelectFade=function(){this.fadeIndex=Math.min(this.prevSelectedIndex,this.slides.length-1),this.prevSelectedIndex=this.selectedIndex},a.onSettleFade=function(){if(delete this.didDragEnd,this.options.fade){this.selectedSlide.setOpacity(1);var e=this.slides[this.fadeIndex];e&&this.fadeIndex!=this.selectedIndex&&this.slides[this.fadeIndex].setOpacity(0)}},a.onDragEndFade=function(){this.didDragEnd=!0},a.onActivateFade=function(){this.options.fade&&this.element.classList.add("is-fade")},a.onDeactivateFade=function(){this.options.fade&&(this.element.classList.remove("is-fade"),this.slides.forEach(function(e){e.setOpacity("")}))};var n=a.positionSlider;a.positionSlider=function(){return this.options.fade?(this.fadeSlides(),void this.dispatchScrollEvent()):void n.apply(this,arguments)};var h=a.positionSliderAtSelected;a.positionSliderAtSelected=function(){this.options.fade&&this.setTranslateX(0),h.apply(this,arguments)},a.fadeSlides=function(){if(!(this.slides.length<2)){var e=this.getFadeIndexes(),t=this.slides[e.a],i=this.slides[e.b],s=this.wrapDifference(t.target,i.target),a=this.wrapDifference(t.target,-this.x);a/=s,t.setOpacity(1-a),i.setOpacity(a);var d=e.a;this.isDragging&&(d=a>.5?e.a:e.b);var n=void 0!=this.fadeHideIndex&&this.fadeHideIndex!=d&&this.fadeHideIndex!=e.a&&this.fadeHideIndex!=e.b;n&&this.slides[this.fadeHideIndex].setOpacity(0),this.fadeHideIndex=d}},a.getFadeIndexes=function(){return this.isDragging||this.didDragEnd?this.options.wrapAround?this.getFadeDragWrapIndexes():this.getFadeDragLimitIndexes():{a:this.fadeIndex,b:this.selectedIndex}},a.getFadeDragWrapIndexes=function(){var e=this.slides.map(function(e,t){return this.getSlideDistance(-this.x,t)},this),i=e.map(function(e){return Math.abs(e)}),s=Math.min.apply(Math,i),a=i.indexOf(s),d=e[a],n=this.slides.length,h=d>=0?1:-1;return{a:a,b:t.modulo(a+h,n)}},a.getFadeDragLimitIndexes=function(){for(var e=0,t=0;t<this.slides.length-1;t++){var i=this.slides[t];if(-this.x<i.target)break;e=t}return{a:e,b:e+1}},a.wrapDifference=function(e,t){var i=t-e;if(!this.options.wrapAround)return i;var s=i+this.slideableWidth,a=i-this.slideableWidth;return Math.abs(s)<Math.abs(i)&&(i=s),Math.abs(a)<Math.abs(i)&&(i=a),i};var o=a._getWrapShiftCells;a._getWrapShiftCells=function(){this.options.fade||o.apply(this,arguments)};var r=a.shiftWrapCells;return a.shiftWrapCells=function(){this.options.fade||r.apply(this,arguments)},e});

/**
 * Flickity fullscreen v1.1.1
 * Enable fullscreen view for Flickity
 *
 */
!function(e,t){"function"==typeof define&&define.amd?define(["flickity/js/index"],t):"object"==typeof module&&module.exports?module.exports=t(require("flickity")):t(e.Flickity)}(window,function(e){"use strict";function t(e,t){this.name=e,this.createButton(),this.createIcon(),this.onClick=function(){t[e+"Fullscreen"]()},this.clickHandler=this.onClick.bind(this)}function i(e){return e[0].toUpperCase()+e.slice(1)}e.createMethods.push("_createFullscreen");var n=e.prototype;n._createFullscreen=function(){this.isFullscreen=!1,this.options.fullscreen&&(this.viewFullscreenButton=new t("view",this),this.exitFullscreenButton=new t("exit",this),this.on("activate",this._changeFullscreenActive),this.on("deactivate",this._changeFullscreenActive))},n._changeFullscreenActive=function(){var e=this.isActive?"appendChild":"removeChild";this.element[e](this.viewFullscreenButton.element),this.element[e](this.exitFullscreenButton.element);var t=this.isActive?"activate":"deactivate";this.viewFullscreenButton[t](),this.exitFullscreenButton[t]()},n.viewFullscreen=function(){this._changeFullscreen(!0),this.focus()},n.exitFullscreen=function(){this._changeFullscreen(!1)},n._changeFullscreen=function(e){if(this.isFullscreen!=e){this.isFullscreen=e;var t=e?"add":"remove";document.documentElement.classList[t]("is-flickity-fullscreen"),this.element.classList[t]("is-fullscreen"),this.resize(),this.isFullscreen&&this.reposition(),this.dispatchEvent("fullscreenChange",null,[e])}},n.toggleFullscreen=function(){this._changeFullscreen(!this.isFullscreen)};var s=n.setGallerySize;n.setGallerySize=function(){this.options.setGallerySize&&(this.isFullscreen?this.viewport.style.height="":s.call(this))},e.keyboardHandlers[27]=function(){this.exitFullscreen()},t.prototype.createButton=function(){var e=this.element=document.createElement("button");e.className="flickity-button flickity-fullscreen-button flickity-fullscreen-button-"+this.name,e.setAttribute("type","button");var t=i(this.name+" full-screen");e.setAttribute("aria-label",t),e.title=t};var l="http://www.w3.org/2000/svg",c={view:"M15,20,7,28h5v4H0V20H4v5l8-8Zm5-5,8-8v5h4V0H20V4h5l-8,8Z",exit:"M32,3l-7,7h5v4H18V2h4V7l7-7ZM3,32l7-7v5h4V18H2v4H7L0,29Z"};return t.prototype.createIcon=function(){var e=document.createElementNS(l,"svg");e.setAttribute("class","flickity-button-icon"),e.setAttribute("viewBox","0 0 32 32");var t=document.createElementNS(l,"path"),i=c[this.name];t.setAttribute("d",i),e.appendChild(t),this.element.appendChild(e)},t.prototype.activate=function(){this.element.addEventListener("click",this.clickHandler)},t.prototype.deactivate=function(){this.element.removeEventListener("click",this.clickHandler)},e.FullscreenButton=t,e});

document.addEventListener('DOMContentLoaded', function () {
    /**
     * Initialize Flickity carousel for landing pages
     */
    if (document.querySelector('.slider-wrap')) {
        var request = new XMLHttpRequest(),
            mq = window.matchMedia('(min-width: 768px)'),
            ids,
            type = document.querySelector('.slider-wrap').dataset.type,
            zoom = document.querySelector('.slider-wrap').dataset.zoom,
            mobile = document.querySelector('.slider-wrap').dataset.mobile,
            controls = document.querySelector('.slider-wrap').dataset.controls,
            interval = document.querySelector('.slider-wrap').dataset.interval,
            fullwidth = document.querySelector('.slider-wrap').dataset.fullwidth,
            fullheight = document.querySelector('.slider-wrap').dataset.fullheight;

        if (document.querySelector('.slider-wrap').hasAttribute('data-ids')) {
            ids = document.querySelector('.slider-wrap').dataset.ids;
        }

        var prevNextButtonsBool = (controls === 'yes') ? true : false;

        // Initiate request
        request.open('POST', ajaxVar.ajaxurl, true);
        request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8');
        request.onload = function () {
            if (this.status >= 200 && this.status < 400) {
                document.querySelector('.slider-wrap').innerHTML = this.response;

                var elem = document.querySelector('.homepage-hero-slider');
                var flkty = new Flickity(elem, {
                    contain: true,
                    wrapAround: true,
                    imagesLoaded: true,
                    adaptiveHeight: false,
                    lazyLoad: true,
                    pageDots: false,
                    prevNextButtons: prevNextButtonsBool,
                    fade: true,
                    autoPlay: parseInt(interval, 10),
                    pauseAutoPlayOnHover: false
                });
            } else {
                // Response error
            }
        };
        request.onerror = function() {
            // Connection error
        };

        // Check device
        if (mq.matches === true || parseInt(mobile, 10) === 0) {
            // Document width is higher than 768px. Loading the slider.
            request.send('action=simple_slider_helper&type=' + type + '&fullwidth=' + fullwidth + '&fullheight=' + fullheight + '&zoom=' + zoom + '&ids=' + ids);
        } else {
            // Document width is less than 768px. Not loading the slider.
            request.send('action=simple_slider_helper&type=' + type + '&fullwidth=' + fullwidth + '&fullheight=' + fullheight + '&zoom=' + zoom + '&ids=' + mobile);
        }
    }



    /**
     * Flickity (Parsley) carousel fullscreen toggle
     */
    if (document.getElementById('flickity-view-fullscreen')) {
        document.getElementById('flickity-view-fullscreen').addEventListener('click', function (event) {
            event.preventDefault();

            let flkty = new Flickity('.flickity-carousel-parsley', {
                fullscreen: true
            });
            flkty.viewFullscreen();
        });
    }
}, false);
