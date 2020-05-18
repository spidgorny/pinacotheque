import {Image} from './Image';

export class ImageFromFilter extends Image {

	width: number;
	height: number;

	constructor(props: object) {
		super(props);
		Object.assign(this, props);	// again, needed for react
	}

	getWidth() {
		return this.width;
	}

	getHeight() {
		return this.height;
	}

}
