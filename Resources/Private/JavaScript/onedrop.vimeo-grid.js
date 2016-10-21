var Shuffle = window.shuffle;

// ES7 will have Array.prototype.includes.
function arrayIncludes(array, value) {
    return array.indexOf(value) !== -1;
}

// Convert an array-like object to a real array.
function toArray(thing) {
    return Array.prototype.slice.call(thing);
}

function sortByDate(element) {
    return element.getAttribute('data-release');
}

var Demo = function (element) {
    this.albums = toArray(document.querySelectorAll('#vimeo-filters button'));

    this.shuffle = new Shuffle(element, {
        easing: 'cubic-bezier(0.165, 0.840, 0.440, 1.000)', // easeOutQuart
        itemSelector: '.item'
    });

    this.shuffle.sort({
        reverse: true,
        by: sortByDate
    });

    this.filters = {
        albums: []
    };

    this._bindEventListeners();
};

/**
 * Bind event listeners for when the filters change.
 */
Demo.prototype._bindEventListeners = function () {
    this._onAlbumChange = this._handleAlbumChange.bind(this);

    this.albums.forEach(function (button) {
        button.addEventListener('click', this._onAlbumChange);
    }, this);
};

/**
 * Get the values of each `active` button.
 * @return {Array.<string>}
 */
Demo.prototype._getCurrentAlbumFilters = function () {
    return this.albums.filter(function (button) {
        return button.classList.contains('active');
    }).map(function (button) {
        return button.getAttribute('data-value');
    });
};

/**
 * A album button was clicked. Update filters and display.
 * @param {Event} evt Click event object.
 */
Demo.prototype._handleAlbumChange = function (evt) {
    var button = evt.currentTarget;

    // Treat these buttons like radio buttons where only 1 can be selected.
    if (button.classList.contains('active')) {
        button.classList.remove('active');
    } else {
        this.albums.forEach(function (btn) {
            btn.classList.remove('active');
        });

        button.classList.add('active');
    }

    this.filters.albums = this._getCurrentAlbumFilters();
    this.filter();
};

/**
 * Filter shuffle based on the current state of filters.
 */
Demo.prototype.filter = function () {
    if (this.hasActiveFilters()) {
        this.shuffle.filter(this.itemPassesFilters.bind(this));
    } else {
        this.shuffle.filter(Shuffle.ALL_ITEMS);
    }
};

/**
 * If any of the arrays in the `filters` property have a length of more than zero,
 * that means there is an active filter.
 * @return {boolean}
 */
Demo.prototype.hasActiveFilters = function () {
    return Object.keys(this.filters).some(function (key) {
        return this.filters[key].length > 0;
    }, this);
};

/**
 * Determine whether an element passes the current filters.
 * @param {Element} element Element to test.
 * @return {boolean} Whether it satisfies all current filters.
 */
Demo.prototype.itemPassesFilters = function (element) {
    var albums = this.filters.albums;
    var album = element.getAttribute('data-album');

    // If there are active album filters and this album is not in that array.
    return !(albums[0] != null && albums.length > 0 && !arrayIncludes(albums, album));
};

window.demo = new Demo(document.querySelector('#vimeo-grid'));
