/**
 * Company: KSS services/solutions
 * User: Alexander Kappler
 * Date: 13.12.14
 */

jQuery.fn.visible = function() {
	return this.css('visibility', 'visible');
};

$(document).ready(function(){

	var _filterButtonWraperId = '#vimeo-filters',
		_isotopeItemId = '.item',
		_containerId = '.vimeo-container',
		startDefaultFilter = '*',
		addFilterClickEvent = function(){
			$(_filterButtonWraperId).on( 'click', 'button', function() {
				var filterValue = $(this).attr('data-filter');
				$(_containerId).isotope({filter: filterValue});
			});
		},
		getStartDefaultFilter = function(){

			if($(_filterButtonWraperId).attr('data-default-start-filter') !== '*' && $(_filterButtonWraperId).attr('data-default-start-filter') !== '') {
				startDefaultFilter = $(_filterButtonWraperId).attr('data-default-start-filter');
			}

		},
		initializeIsotope = function(){
			getStartDefaultFilter();

			// show all items after they are loaded - looks nicer
			$(_containerId + ' ' +_isotopeItemId).visible();

			$(_containerId).isotope({
				itemSelector: _isotopeItemId,
				filter: startDefaultFilter,
				masonry: {
					columnWidth: _isotopeItemId
				}
			});
		};

	// wait till images are loaded then initialize isotope
	$(_containerId).imagesLoaded( function() {
		initializeIsotope();
		addFilterClickEvent();
	});


	// fancybox2 - lightbox
	$('.fancybox-media').fancybox({
		padding: 0,
		width: 1900,
		height: 1080,
		margin: 100,
		openEffect  : 'none',
		closeEffect : 'none',
		autoWidth: true,
		aspectRatio : true,
		helpers : {
			title	: {
				type: 'outside'
			},
			media : {}
		},
		beforeLoad: function() {
			var title = $(this.element).find('.item-overlay').html();

			if (title.length) {
				this.title = title;
			}
		}
	});

	if (typeof document.addEventListener === 'function') {
		document.addEventListener('Neos.ContentModuleLoaded', function () {
			setTimeout(function(){
				$(_containerId).isotope('layout');
			},1000);
		}, false);

		document.addEventListener('Neos.PageLoaded', function() {
			$(_containerId).imagesLoaded( function() {
				initializeIsotope();
				addFilterClickEvent();
			});

		}, false);

		document.addEventListener('Neos.LayoutChanged', function() {
			$(_containerId).isotope('layout');
		}, false);
	}

});
