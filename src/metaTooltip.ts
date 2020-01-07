//import Tooltip from "tooltip.js";
// declare class Tooltip {
// 	constructor(reference, options: any);
// }

document.addEventListener("DOMContentLoaded", () => {
	const refDom = document.querySelectorAll('a.meta');
	const refArray = Array.from(refDom);
	if (refArray.length) {
		// for (let reference of refArray) {
		document.addEventListener('click', onMetaClick);
		// }
	}
});

async function onMetaClick(e: MouseEvent) {
	let closestA = e.target.closest('a');
	if (closestA && closestA.matches('a.meta')) {
		e.preventDefault();
		const reference: HTMLLinkElement = e.target as HTMLLinkElement;
		const link = reference.closest('a');
		const href = link.getAttribute('href');
		const html = await (await fetch(href)).text();
		const sidebar = document.querySelector('#sidebar');
		sidebar.innerHTML = html;
	}
}
