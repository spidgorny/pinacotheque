function clamp(num, min, max) {
	return num <= min ? min : num >= max ? max : num;
}

class PreviewController {

	protected images: any[];

	protected index;

	protected transparent = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';

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
	}

	public onKeyUp(e) {
		// console.log(e);
		if (e.key === 'ArrowRight') {
			if (this.index < this.images.length - 1) {
				this.index += 1;
				this.render();
			}
		}
		if (e.key === 'ArrowLeft') {
			if (this.index > 0) {
				this.index -= 1;
				this.render();
			}
		}
		if (e.key === 'ArrowUp') {
			if (this.index > 0) {
				this.index -= 1;
				this.render();
			}
		}
		if (e.key === 'ArrowDown') {
			if (this.index < this.images.length - 1) {
				this.index += 1;
				this.render();
			}
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
		if (this.current && 'videosrc' in this.current) {
			const app = document.querySelector('#app');
			app.innerHTML = this.current.html;
			document.title = this.current.title;
		} else {
			const img = document.querySelector('img');
			if (!img) {
				const img = document.createElement('img');
				const app = document.querySelector('#app');
				app.innerHTML = img.toString();
			}
			// console.log(index, images[index]);
			img.style.backgroundImage = 'url(ShowThumb?file=' + this.current.id;
			img.src = this.transparent;	// show bg
			setTimeout(() => {
				img.src = this.current.src;
			}, 1);
			img.addEventListener('click', this.onClick.bind(this));
			document.title = this.current.title;
		}

		this.preloadAround(this.index, 5);
		this.updateURL();
	}

	public preloadAround(index, range) {
		for (let i = index - range; i < index + range; i++) {
			if (i >= 0 && i < this.images.length) {
				const thumb = document.createElement('img');
				thumb.src = 'ShowThumb?file=' + this.images[i].id;
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

}
