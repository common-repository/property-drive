function toggleDiv(div) {
    var obj = document.getElementById(div);

    obj.style.display = obj.style.display == 'none' ? 'block' : 'none'
}

document.addEventListener('DOMContentLoaded', function () {
    // Collapse all listings by default, except for the first one
    var defaultHandles = document.querySelectorAll('.listing-section-title');

    [].forEach.call(defaultHandles, function (defaultHandle) {
        if (defaultHandle.dataset.listing !== 'property-description') {
            toggleDiv(defaultHandle.dataset.listing);
        }
    });

    var handles = document.querySelectorAll('.listing-section-title');

    [].forEach.call(handles, function (el) {
        el.addEventListener('click', function () {
            toggleDiv(this.dataset.listing);
        });
    });
}, false);
