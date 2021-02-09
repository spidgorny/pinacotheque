export class Image {
	// @ts-ignore
	id: string;
	// @ts-ignore
	source: string;
	// @ts-ignore
	type: string;
	// @ts-ignore
	path: string;
	// @ts-ignore
	timestamp: string;
	// @ts-ignore
	DateTime: string;
	colors?: any;
	// @ts-ignore
	ext: string;
	ym?: any;
	meta_timestamp?: any;
	meta_error?: any;
	// @ts-ignore
	thumb: string;
	// @ts-ignore
	source_path: string;
	// @ts-ignore
	meta: Meta;
	width?: number;
	height?: number;

	// @ts-ignore
	baseUrl: string;

	constructor(props: any) {
		Object.assign(this, props);
	}

	get thumbURL() {
		const url = new URL("ShowThumb", this.baseUrl);
		url.searchParams.set("file", this.id);
		return url.toString();
	}

	get originalURL() {
		const url = new URL("ShowOriginal", this.baseUrl);
		url.searchParams.set("file", this.id);
		return url.toString();
	}

	getWidth() {
		return this.width || 1024;
	}

	getHeight() {
		return this.height || 768;
	}

	// convert 2019:11:17 13:18:57
	/// @deprecated
	getTimestamp() {
		return new Date(this.DateTime);
	}

	basename(str: string, sep: string = "/") {
		return str.substr(str.lastIndexOf(sep) + 1);
	}

	get title() {
		return this.basename(this.path);
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
	"Thumbnail.FileType": number;
	"Thumbnail.MimeType": string;
}
