var Shuffle = window.shuffle;
var Viewport = window.Viewport;
var shuffleItemsContainer = document.querySelector('#vimeo-grid');
var shuffleLoadMoreButton = document.querySelector('#loadmore');
var currentVideoItemCount = 0;

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

var OnedropVimeoGrid = function (element) {
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
OnedropVimeoGrid.prototype._bindEventListeners = function () {
    this._onAlbumChange = this._handleAlbumChange.bind(this);

    this.albums.forEach(function (button) {
        button.addEventListener('click', this._onAlbumChange);
    }, this);
};

/**
 * Get the values of each `active` button.
 * @return {Array.<string>}
 */
OnedropVimeoGrid.prototype._getCurrentAlbumFilters = function () {
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
OnedropVimeoGrid.prototype._handleAlbumChange = function (evt) {
    var button = evt.currentTarget;

    // Treat these buttons like radio buttons where only 1 can be selected.
    if (button.classList.contains('active')) {
        button.classList.remove('active');
    } else {
        this.albums.forEach(function (btn) {
            btn.classList.remove('active');
        });

        if (button.getAttribute('data-filter') !== '*') {
            button.classList.add('active');
        }
    }

    this.filters.albums = this._getCurrentAlbumFilters();
    this.filter();

    // load all videos upon activating a filter
    this.shuffle.add(addVideoItems('all'));
};

/**
 * Filter shuffle based on the current state of filters.
 */
OnedropVimeoGrid.prototype.filter = function () {
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
OnedropVimeoGrid.prototype.hasActiveFilters = function () {
    return Object.keys(this.filters).some(function (key) {
        return this.filters[key].length > 0;
    }, this);
};

/**
 * Determine whether an element passes the current filters.
 * @param {Element} element Element to test.
 * @return {boolean} Whether it satisfies all current filters.
 */
OnedropVimeoGrid.prototype.itemPassesFilters = function (element) {
    var filteredAlbums = this.filters.albums;
    var itemAlbums = JSON.parse(element.getAttribute('data-album'));

    if (filteredAlbums[0] != null && filteredAlbums.length > 0) {
        var hasFilteredAlbum = false;
        for (var i = 0; i < itemAlbums.length; i++) {
            if (arrayIncludes(filteredAlbums, itemAlbums[i].ID)) {
                hasFilteredAlbum = true;
            }
        }
        return hasFilteredAlbum;
    }
    return true;
};

function sortVideoItemsByDate(array) {
    array.sort(function (a, b) {
        // Turn your strings into dates, and then subtract them
        // to get a value that is either negative, positive, or zero.
        return new Date(b.releaseTime) - new Date(a.releaseTime);
    });
    return array;
}

function closeLightBox(popup, overlay) {
    overlay.removeEventListener('click', this);

    popup.classList.remove('open');

    setTimeout(function () {
        document.body.removeChild(popup);
    }, 333);
}

function openVideoInLightbox(url) {
    var popup = document.createElement("div");
    var overlay = document.createElement("div");
    var videoContainer = document.createElement("div");
    var videoFrame = document.createElement("iframe");

    popup.setAttribute("id", "vimeo-video-popup");
    overlay.setAttribute("id", "vimeo-video-popup__overlay");
    videoContainer.setAttribute("class", "embed-responsive embed-responsive-16by9");
    videoFrame.setAttribute("src", url);
    videoFrame.setAttribute("frameborder", 0);
    videoFrame.setAttribute("webkitallowfullscreen", '');
    videoFrame.setAttribute("mozallowfullscreen", '');
    videoFrame.setAttribute("allowfullscreen", '');

    // handle overlay closing via js and ESC key
    overlay.addEventListener('click', function () {
        closeLightBox(popup, overlay);
    });
    document.onkeydown = function (evt) {
        evt = evt || window.event;
        var isEscape = false;
        if ("key" in evt) {
            isEscape = (evt.key == "Escape" || evt.key == "Esc");
        } else {
            isEscape = (evt.keyCode == 27);
        }
        if (isEscape) {
            closeLightBox(popup, overlay);
        }
    };

    videoContainer.appendChild(videoFrame);
    popup.appendChild(overlay);
    popup.appendChild(videoContainer);
    document.body.appendChild(popup);

    setTimeout(function () {
        popup.classList.add('open');
    }, 50);
}

function createVideoElementFromObject(obj) {
    var el = document.createElement("div");
    var a = document.createElement("a");
    var img = document.createElement("img");
    var img2 = document.createElement("img");
    var overlay = document.createElement("div");
    var title = document.createElement("span");

    el.setAttribute("class", window.window.onedropVimeoGridData.itemClasses);
    el.setAttribute("data-album", JSON.stringify(obj.albums));
    el.setAttribute("data-release", obj.releaseTime);
    el.setAttribute("data-title", obj.title);
    a.setAttribute("href", obj.link);
    a.setAttribute("class", "item__inner embed-responsive embed-responsive-16by9");
    img.setAttribute("src", obj.thumbnail.link);
    img.setAttribute("width", obj.thumbnail.width);
    img.setAttribute("height", obj.thumbnail.height);
    img.setAttribute("alt", obj.title);
    img.setAttribute("class", "embed-responsive-item");
    img2.setAttribute("src", obj.thumbnail.link);
    img2.setAttribute("width", obj.thumbnail.width);
    img2.setAttribute("height", obj.thumbnail.height);
    img2.setAttribute("alt", obj.title);
    img2.setAttribute("class", "embed-responsive-item hover-effect");
    overlay.setAttribute("class", "item-overlay");
    title.setAttribute("class", "title");
    title.textContent = obj.title;

    a.addEventListener('click', function (e) {
        e.preventDefault();
        openVideoInLightbox('//player.vimeo.com/video/' + obj.link.substr(this.href.lastIndexOf('/') + 1) + '?autoplay=1');
    });

    overlay.appendChild(title);
    a.appendChild(img);
    a.appendChild(img2);
    a.appendChild(overlay);
    el.appendChild(a);

    return el;
}

function addVideoItems(count) {
    var itemsToAdd = [];

    // exit early if all videos are loaded
    if (currentVideoItemCount >= window.window.onedropVimeoGridData.items.length) {
        return [];
    }

    if (count === 'all') {
        count = window.window.onedropVimeoGridData.items.length;
    }

    for (var i = 0; i < count; i++) {
        if (window.window.onedropVimeoGridData.items.length > currentVideoItemCount + i) {
            var el = createVideoElementFromObject(window.window.onedropVimeoGridData.items[currentVideoItemCount + i]);
            shuffleItemsContainer.appendChild(el);
            itemsToAdd.push(el);
        } else {
            shuffleLoadMoreButton.classList.add('disabled');
        }
    }
    currentVideoItemCount += itemsToAdd.length;
    return itemsToAdd;
}

// sort items by date
window.window.onedropVimeoGridData.items = sortVideoItemsByDate(window.window.onedropVimeoGridData.items);

// add 9 items to dom
addVideoItems(9);

// initialize shufflejs
window.onedropVimeoGrid = new OnedropVimeoGrid(shuffleItemsContainer);

// load more items on click
shuffleLoadMoreButton.addEventListener('click', function (e) {
    e.preventDefault();
    var newElements = addVideoItems(9);
    window.onedropVimeoGrid.shuffle.add(newElements);
});
