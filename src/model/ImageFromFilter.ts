import {Image} from './Image';

export class ImageFromFilter extends Image {

	width: number;
	height: number;

	getWidth() {
		return this.width;
	}

	getHeight() {
		return this.height;
	}

}
