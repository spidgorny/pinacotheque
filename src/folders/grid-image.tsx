import { Image } from "../model/Image";
import React from "react";
import { PhotoProps } from "react-photo-gallery";
import { InView } from "react-intersection-observer";
import { MyPhoto } from "../stream/my-photo";

export function GridImage(props: {
	img: Image;
	index: number;
	setVisible: (img: Image, visible: boolean, index: number) => void;
	onClick: (
		e: React.MouseEvent,
		photo: PhotoProps,
		index: number,
		image?: Image
	) => void;
}) {
	// const { ref, inView, entry } = useInView({});

	return (
		<InView
			as="div"
			onChange={(inView, entry) =>
				props.setVisible(props.img, inView, props.index)
			}
		>
			<div style={{ border: "solid 1px silver", width: 256, height: 256 }}>
				<MyPhoto
					index={props.index}
					photo={{
						width: props.img.getThumbWidth(),
						height: props.img.getThumbHeight(),
						src: props.img.src,
						image: props.img,
					}}
					forceInfo={props.img.isDir()}
					onClick={props.onClick}
				/>
			</div>
		</InView>
	);
}
