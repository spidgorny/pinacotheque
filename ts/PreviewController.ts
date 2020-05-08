function clamp(num, min, max) {
	return num <= min ? min : num >= max ? max : num;
}

class PreviewController {

	protected images: any[];

	protected index;

	protected transparent = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

	protected carousel = {};

	constructor(images: any[]) {
		this.images = images;
		this.detectIndex();
		this.attachEvents();
		this.render();	// maybe it's video
	}

	public detectIndex() {
		const file = new URLSearchParams(document.location.search).get('file');
		console.log('file', file);
		for (let i in this.images) {
			if (this.images[i].id == file) {
				this.index = parseInt(i, 10);
				break;
			}
		}
		console.log('index', this.index);
	}

	public attachEvents() {
		document.addEventListener('keyup', this.onKeyUp.bind(this));
		document.addEventListener('swiped-left', (e) => {
			this.moveNext();
		});
		document.addEventListener('swiped-right', (e) => {
			this.movePrev();
		});
	}

	public moveNext() {
		if (this.index < this.images.length - 1) {
			this.index += 1;
			this.render();
		}
	}

	public movePrev() {
		if (this.index > 0) {
			this.index -= 1;
			this.render();
		}
	}

	public onKeyUp(e) {
		// console.log(e);
		if (e.key === 'ArrowRight') {
			this.moveNext();
		}
		if (e.key === 'ArrowLeft') {
			this.movePrev();
		}
		if (e.key === 'ArrowUp') {
			this.movePrev()
		}
		if (e.key === 'ArrowDown') {
			this.moveNext();
		}
		if (e.key === 'Escape') {
			this.onClick(new MouseEvent('click'));
		}
		if (e.key === 'PageUp') {
			this.index = clamp(this.index - 6, 0, this.images.length - 1);
			this.render();
		}
		if (e.key === 'PageDown') {
			this.index = clamp(this.index + 6, 0, this.images.length - 1);
			this.render();
		}
		if (e.key === 'Home') {
			this.index = 0;
			this.render();
		}
		if (e.key === 'End') {
			this.index = this.images.length - 1;
			this.render();
		}
		if (e.key === 'Enter') {
			this.onClick(new MouseEvent('click'));
		}
	}

	get current() {
		return this.images[this.index];
	}

	public render() {
		console.log(this.current);
		const app = document.querySelector('#app');
		let innerHTML = '';
		if (this.current && 'videosrc' in this.current) {
			innerHTML = this.current.html;
		} else {
			const img = document.querySelector('img');
			if (!img) {
				const img = document.createElement('img');
			}
			// console.log(index, images[index]);
			img.style.backgroundImage = 'url(ShowThumb?file=' + this.current.id;
			img.src = this.transparent;	// show bg
			img.addEventListener('click', this.onClick.bind(this));
			setTimeout(() => {
				img.src = this.current.src;
			}, 1);
			innerHTML = img;
		}
		app.innerHTML = '';
		app.appendChild(innerHTML);

		this.preloadAround(this.index, 5);	// before renderCarousel
		app.appendChild(this.renderCarousel());

		document.title = this.current.title;

		const headImage = document.head.querySelector('meta[property="og:image"]');
		const absShowThumb = new URL(document.location.href);
		absShowThumb.pathname = 'ShowThumb';
		headImage.setAttribute('content', absShowThumb.toString());

		this.updateURL();
	}

	public preloadAround(index, range) {
		for (let i = index - range; i <= index + range; i++) {
			if (i >= 0 && i < this.images.length) {
				const thumb = document.createElement('img');
				thumb.src = 'ShowThumb?file=' + this.images[i].id;
				// console.log(i - index, this.images[i].id);
				this.carousel[i - index] = thumb;	// save for renderCarousel
				// console.log('preloading', i);
				const img = document.createElement('img');
				img.src = this.images[i].src;
			}
		}
	}

	public updateURL() {
		const params = new URLSearchParams(document.location.search);
		params.set('file', this.current.id);
		const newURL = new URL(document.location.href);
		newURL.search = params.toString();
		window.history.replaceState({}, document.title, newURL.toString());
	}

	public onClick(e) {
		if (!document.referrer) {
			document.location.href = document.location.href.replace('Preview', 'MonthBrowserDB');
			return;
		}
		const url = new URL(document.referrer);
		url.hash = this.current.id;
		document.location.href = url.toString();
	}

	public renderCarousel() {
		// console.log(this.carousel);
		let cells = [];
		for (let i = -5; i <= 5; i++) {
			const img = this.carousel[i] as HTMLImageElement;
			if (img) {
				const src = img.getAttribute('src');
				cells.push(`<td><img src="${src}" /></td>`);
			}
		}
		const sCells = cells.join('\n');
		const html = `<table><tr>${sCells}</tr></table>`;
		const div: HTMLDivElement = document.createElement('div');
		div.innerHTML = html;
		div.setAttribute('style', "position: absolute; bottom: 0");
		setTimeout(() => {
			div.style.opacity = 0.5;
		}, 1000);
		setTimeout(() => {
			div.style.opacity = 0;
		}, 2000);
		return div;
	}

}
