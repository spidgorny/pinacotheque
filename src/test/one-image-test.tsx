import React from "react";
import { RouteComponentProps } from "wouter";
import { MyPhoto } from "../stream/my-photo";
import { Image } from "../model/Image";

export class OneImageTest extends React.Component<RouteComponentProps<{}>, {}> {
	image = new Image({
		baseUrl: "http://192.168.1.109/",
		id: "70389",
		source: "2",
		type: "file",
		path: "OnePlus5T/2019-11/IMG_20191118_200001.jpg",
		timestamp: "1574112608",
		DateTime: "2019-11-18T20:00:01+01:00",
		colors: null,
		ext: ".jpg",
		ym: null,
		meta_timestamp: null,
		meta_error: null,
		mtime: "2020-05-11 01:41:56",
		width: 2304,
		height: 4608,
		thumb:
			"/media/nas/photo/pinacotheque/data/BurnCD/OnePlus5T/2019-11/IMG_20191118_200001.jpg",
		source_path: "/media/slawa/Elements/PhotosNSA/BurnCD",
		meta: {
			ApertureValue: "153/100",
			BrightnessValue: "300/100",
			ColorSpace: "1",
			ComponentsConfiguration: "\u0001\u0002\u0003\u0000",
			COMPUTED: {
				html: 'width="2304" height="4608"',
				Height: 4608,
				Width: 2304,
				IsColor: 1,
				ByteOrderMotorola: 1,
				ApertureFNumber: "f/1.7",
				"Thumbnail.FileType": 2,
				"Thumbnail.MimeType": "image/jpeg",
			},
			DateTime: "2019:11:18 20:00:01",
			DateTimeDigitized: "2019:11:18 20:00:01",
			DateTimeOriginal: "2019:11:18 20:00:01",
			ExifImageLength: "4608",
			ExifImageWidth: "2304",
			ExifVersion: "0220",
			Exif_IFD_Pointer: "285",
			ExposureMode: "0",
			ExposureProgram: "0",
			ExposureTime: "1/17",
			FileDateTime: "1574112608",
			FileName: "IMG_20191118_200001.jpg",
			FileSize: "3367669",
			FileType: "2",
			Flash: "16",
			FlashPixVersion: "0100",
			FNumber: "17000/10000",
			FocalLength: "4103/1000",
			FocalLengthIn35mmFilm: "24",
			GPSAltitude: "303590/1000",
			GPSAltitudeRef: "\u0000",
			GPSDateStamp: "2019:11:18",
			GPSLatitude: ["50/1", "9/1", "185845/10000"],
			GPSLatitudeRef: "N",
			GPSLongitude: ["8/1", "42/1", "61444/10000"],
			GPSLongitudeRef: "E",
			GPSTimeStamp: ["19/1", "0/1", "1/1"],
			GPS_IFD_Pointer: "783",
			ImageLength: "4608",
			ImageWidth: "2304",
			InterOperabilityIndex: "R98",
			InteroperabilityOffset: "976",
			ISOSpeedRatings: "5000",
			LightSource: "0",
			Make: "OnePlus",
			MakerNote: "MM",
			MeteringMode: "6",
			MimeType: "image/jpeg",
			Model: "ONEPLUS A5010",
			Orientation: "1",
			ResolutionUnit: "2",
			SceneType: "\u0001",
			SectionsFound: "ANY_TAG, IFD0, THUMBNAIL, EXIF, GPS, INTEROP",
			SensingMethod: "2",
			ShutterSpeedValue: "4058/1000",
			Software: "OnePlus5T-user 9 PKQ1.180716.001 1907311828 release-keys",
			SubSecTime: "741364",
			SubSecTimeDigitized: "741364",
			SubSecTimeOriginal: "741364",
			THUMBNAIL: {
				YResolution: "72/1",
				Compression: 6,
				JPEGInterchangeFormat: 1274,
				JPEGInterchangeFormatLength: 17221,
				XResolution: "72/1",
				Model: "ONEPLUS A5010",
				Make: "OnePlus",
				Orientation: 1,
				ResolutionUnit: 2,
				DateTime: "2019:11:18 20:00:01",
			},
			WhiteBalance: "0",
			XResolution: "72/1",
			YCbCrPositioning: "1",
			YResolution: "72/1",
		},
	});

	photo = {
		index: 0,
		src: this.image.thumbURL,
		width: 256,
		height: this.image.resize(256),
		image: this.image,
	};

	render() {
		return (
			<div className="flex flex-row">
				<MyPhoto
					index={0}
					direction={"row"}
					photo={this.photo}
					onClick={() => {}}
				/>
				<MyPhoto
					index={0}
					direction={"row"}
					photo={this.photo}
					onClick={() => {}}
					forceInfo={true}
				/>
			</div>
		);
	}
}
