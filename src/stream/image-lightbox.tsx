import { Image } from "../model/Image";
import Carousel, { Modal, ModalGateway } from "react-images";
// @ts-ignore
import { FooterCaption } from "react-images/lib/components/Footer";
import React from "react";

// function FooterCaption(props: any) {
// 	console.log(props);
// 	return <div />;
// }

export function ImageLightbox(props: {
	viewerIsOpen: boolean;
	onClose: OmitThisParameter<() => void>;
	currentIndex: number;
	images: Image[];
}) {
	const convertImageToCarousel = (x: Image) => ({
		// ...x,
		caption: x.title,
		source: {
			thumbnail: x.thumbURL,
			regular: x.originalURL,
		},
	});

	return (
		<ModalGateway>
			{props.viewerIsOpen ? (
				<Modal onClose={props.onClose}>
					<Carousel
						currentIndex={props.currentIndex}
						views={props.images.map(convertImageToCarousel)}
						components={{ FooterCaption }}
					/>
				</Modal>
			) : null}
		</ModalGateway>
	);
}
