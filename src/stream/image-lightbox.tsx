import { Image } from "../model/Image";
import Carousel, { Modal, ModalGateway } from "react-images";
// @ts-ignore
import { FooterCaption } from "react-images/lib/components/Footer";
import React, { PropsWithChildren, useState } from "react";
import { ShortcutHandler } from "./shortcut-handler";
import "reactjs-popup/dist/index.css";
import { useLocation } from "wouter";

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
	const [, setLocation] = useLocation();

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
				keyToPress={"Enter"}
				handler={() => {
					let img = props.images[props.currentIndex];
					setLocation("/image/" + img.id);
				}}
			/>
		</div>
	);
}

export function ShortcutToggle(
	props: PropsWithChildren<{ keyToPress: string }>
) {
	const [onOff, setOnOff] = useState(false);

	const handle = () => {
		setOnOff(!onOff);
	};

	return (
		<div>
			<ShortcutHandler keyToPress={props.keyToPress} handler={handle} />
			{onOff && props.children}
		</div>
	);
}
