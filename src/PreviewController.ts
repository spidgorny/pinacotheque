class PreviewController {

	protected images: any[];

	protected index;

	constructor(images: any[]) {
		this.images = images;
		this.detectIndex();
		this.attachEvents();
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
			}
			this.render();
		}
		if (e.key === 'ArrowLeft') {
			if (this.index > 0) {
				this.index -= 1;
			}
			this.render();
		}
		if (e.key === 'ArrowUp') {
			if (this.index > 0) {
				this.index += 1;
			}
			this.render();
		}
		if (e.key === 'ArrowDown') {
			if (this.index < this.images.length - 1) {
				this.index += 1;
			}
			this.render();
		}
	}

	public render() {
		console.log(this.images[this.index]);
		if ('videosrc' in this.images[this.index]) {
			const app = document.querySelector('#app');
			app.innerHTML = this.images[this.index].html;
		} else {
			const img = document.querySelector('img');
			if (!img) {
				const img = document.createElement('img');
				const app = document.querySelector('#app');
				app.innerHTML = img.toString();
			}
			// console.log(index, images[index]);
			img.style.backgroundImage = 'url(ShowThumb?file=' + this.images[this.index].id;
			img.src = this.images[this.index].src;
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
				// const img = document.createElement('img');
				// img.src = this.images[i].src;
			}
		}
	}

	public updateURL() {
		const params = new URLSearchParams(document.location.search);
		params.set('file', this.images[this.index].id);
		const newURL = new URL(document.location.href);
		newURL.search = params.toString();
		window.history.replaceState({}, document.title, newURL.toString());
	}

}
