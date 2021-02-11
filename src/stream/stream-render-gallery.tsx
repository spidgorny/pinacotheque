import { GalleryInScroll } from "./GalleryInScroll";
import React from "react";
import { Image } from "../model/Image";

export default function StreamRenderGallery(props: {
	photos: Image[];
	next: () => void;
	refresh: () => void;
}) {
	return (
		<GalleryInScroll
			photos={props.photos}
			next={props.next}
			refreshFunction={props.refresh}
		/>
	);
}
