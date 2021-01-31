import {Image} from './Image';

export class ImageFromFilter extends Image {

	// @ts-ignore
	width: number;
	// @ts-ignore
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
