//import Tooltip from "tooltip.js";
// declare class Tooltip {
// 	constructor(reference, options: any);
// }

document.addEventListener("DOMContentLoaded", () => {
	const refDom = document.querySelectorAll('a.meta');
	const refArray = Array.from(refDom);
	for (let reference of refArray) {
		reference.addEventListener('click', onMetaClick);
	}
});

async function onMetaClick(e: MouseEvent) {
	e.preventDefault();
	const reference: HTMLLinkElement = e.target as HTMLLinkElement;
	const link = reference.closest('a');
	const href = link.getAttribute('href');
	const html = await (await fetch(href)).text();
	const sidebar = document.querySelector('#sidebar');
	sidebar.innerHTML = html;
}
