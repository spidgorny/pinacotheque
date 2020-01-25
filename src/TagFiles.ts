class TagFiles {

	id: number;

	constructor() {
		const file = new URLSearchParams(document.location.search).get('file');
		console.log('file', file);
		this.id = parseInt(file, 10);
		document.addEventListener('keyup', this.onKeyUp.bind(this));
	}

	public onKeyUp(e) {
		// console.log(e);
		if (e.key === 't') {
			const tag = prompt('New Tag?');
			if (tag) {
				this.sendTag(tag);
			}
		}
	}

	public async sendTag(tag: string) {
		const SendTag = new URL(document.location.href);
		SendTag.pathname = 'SendTag';
		SendTag.searchParams.set('tag', tag);
		const res = await fetch(SendTag.toString());
		console.log(res);
	}

}
