const path = require('path');

export class Image {
	id: string;
	source: string;
	type: string;
	path: string;
	timestamp: string;
	DateTime: string;
	colors?: any;
	ext: string;
	ym?: any;
	meta_timestamp?: any;
	meta_error?: any;
	thumb: string;
	source_path: string;
	meta: Meta;

	baseUrl: string;

	constructor(props) {
		Object.assign(this, props);
	}

	get thumbURL() {
		const url = new URL('ShowThumb', this.baseUrl);
		url.searchParams.set('file', this.id);
		return url.toString();
	}

	get width() {
		if ('COMPUTED' in this.meta) {
			return this.meta.COMPUTED.Width;
		}
		if ('streams' in this.meta/* && this.meta.streams.length*/) {
			// @ts-ignore
			return this.meta.streams[0].width;
		}
		if (Object.keys(this.meta).length) {
			console.error('Find width in', this.meta);
		}
		return 1024;
	}

	get height() {
		if ('COMPUTED' in this.meta) {
			return this.meta.COMPUTED.Height;
		}
		if ('streams' in this.meta/* && this.meta.streams.length*/) {
			// @ts-ignore
			return this.meta.streams[0].height;
		}
		if (Object.keys(this.meta)) {
			console.error('Find width in', this.meta);
		}
		return 768;
	}

	// convert 2019:11:17 13:18:57
	/// @deprecated
	getTimestamp() {
		return new Date(this.DateTime.split(' ').map((part, index) => {
			if (index) return part;
			return part.replace(/:/g, '-');
		}).join(' '));
	}

	get title() {
		return path.basename(this.path);
	}

	get date() {
		return new Date(this.DateTime);
	}

}

interface Meta {
	ApertureValue: string;
	BrightnessValue: string;
	ColorSpace: string;
	ComponentsConfiguration: string;
	COMPUTED: COMPUTED;
	DateTime: string;
	DateTimeDigitized: string;
	DateTimeOriginal: string;
	ExifImageLength: string;
	ExifImageWidth: string;
	ExifVersion: string;
	Exif_IFD_Pointer: string;
	ExposureMode: string;
	ExposureProgram: string;
	ExposureTime: string;
	FileDateTime: string;
	FileName: string;
	FileSize: string;
	FileType: string;
	Flash: string;
	FlashPixVersion: string;
	FNumber: string;
	FocalLength: string;
	FocalLengthIn35mmFilm: string;
	GPSAltitude: string;
	GPSAltitudeRef: string;
	GPSDateStamp: string;
	GPSLatitude: string[];
	GPSLatitudeRef: string;
	GPSLongitude: string[];
	GPSLongitudeRef: string;
	GPSTimeStamp: string[];
	GPS_IFD_Pointer: string;
	ImageLength: string;
	ImageWidth: string;
	InterOperabilityIndex: string;
	InteroperabilityOffset: string;
	ISOSpeedRatings: string;
	LightSource: string;
	Make: string;
	MakerNote: string;
	MeteringMode: string;
	MimeType: string;
	Model: string;
	Orientation: string;
	ResolutionUnit: string;
	SceneType: string;
	SectionsFound: string;
	SensingMethod: string;
	ShutterSpeedValue: string;
	Software: string;
	SubSecTime: string;
	SubSecTimeDigitized: string;
	SubSecTimeOriginal: string;
	THUMBNAIL: THUMBNAIL;
	WhiteBalance: string;
	XResolution: string;
	YCbCrPositioning: string;
	YResolution: string;
	format?: any;
	streams?: any[];
}

interface THUMBNAIL {
	YResolution: string;
	Compression: number;
	JPEGInterchangeFormat: number;
	JPEGInterchangeFormatLength: number;
	XResolution: string;
	Model: string;
	Make: string;
	Orientation: number;
	ResolutionUnit: number;
	DateTime: string;
}

interface COMPUTED {
	html: string;
	Height: number;
	Width: number;
	IsColor: number;
	ByteOrderMotorola: number;
	ApertureFNumber: string;
	'Thumbnail.FileType': number;
	'Thumbnail.MimeType': string;
}
