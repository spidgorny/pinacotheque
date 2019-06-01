declare class PhotoSwipe {
	constructor(el: Element, def, items: Array<string>, options);
	init();
}
declare var PhotoSwipeUI_Default;
declare var items;

document.addEventListener("DOMContentLoaded", async () => {
	var pswpElement = document.querySelector('.pswp');

	// define options (if needed)
	var options = {
		// optionName: 'option value'
		// for example:
		index: 0 // start at first slide
	};

	// Initializes and opens PhotoSwipe
	Array.prototype.slice.call(document.querySelectorAll('.tile figure'))
		.filter(figure => {
			figure.addEventListener('click', (e) => {
				// console.log(e);
				var imgClicked = e.target;
				console.log(imgClicked);
				var dataIndex = imgClicked.closest('figure').getAttribute('data-index');
				options.index = parseInt(dataIndex, 10);
				console.log(dataIndex, options.index);
				var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);
				gallery.init();
			});
		});
});
