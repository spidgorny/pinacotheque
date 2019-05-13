//import Tooltip from "tooltip.js";
declare class Tooltip {
	constructor(reference, options: any);
}

document.addEventListener("DOMContentLoaded", () => {
	const refDom = document.querySelectorAll('img.meta');
	const refArray = Array.from(refDom);
	for (let reference of refArray) {
		const id = reference.getAttribute('data-id');
		//console.log(id);
		const popper = document.querySelector('#' + id);
		// console.log(id, popper);
		if (popper) {
			new Tooltip(reference, {
				title: popper.innerHTML,
				html: true,
			});
		}
	}
});
