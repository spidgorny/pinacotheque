import { Image } from "../model/Image";
import Carousel, { Modal, ModalGateway } from "react-images";
// @ts-ignore
import { FooterCaption } from "react-images/lib/components/Footer";
import React, { useCallback, useEffect, useState } from "react";

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
		<div>
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
			<ShortcutHandler
				keyToPress="t"
				handler={() => {
					const img = props.images[props.currentIndex];
					console.log(img);
				}}
			/>
		</div>
	);
}

export function ShortcutHandler(props: {
	keyToPress: string;
	handler: () => void;
}) {
	const escFunction = useCallback(
		(event: KeyboardEvent) => {
			let eventKey = event.key?.toLowerCase();
			let propsKey = props.keyToPress?.toLowerCase();
			if (eventKey === propsKey && event.altKey) {
				console.log("Alt-" + props.keyToPress);
				props.handler();
			}
		},
		[props.keyToPress, props.handler]
	);

	useEffect(() => {
		console.log("keydown init", props.keyToPress);
		document.addEventListener("keydown", escFunction, false);
		return () => {
			console.log("keydown remove", props.keyToPress);
			document.removeEventListener("keydown", escFunction, false);
		};
	}, [props.keyToPress, props.handler, escFunction]);

	return <></>;
}
