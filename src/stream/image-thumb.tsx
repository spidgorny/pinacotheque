import { PhotoProps } from "react-photo-gallery";
import { PhotoSetItem } from "./gallery-in-scroll";
import React from "react";

export function ImageThumb(props: { photo: PhotoProps<PhotoSetItem> }) {
	return (
		<img
			src={
				props.photo.image?.isDir()
					? "https://camo.githubusercontent.com/8fc860423cb8edf1e787428fba028d39626cbee558f079361a958fe83ce363d1/68747470733a2f2f776963672e6769746875622e696f2f656e74726965732d6170692f6c6f676f2d666f6c6465722e737667"
					: props.photo.src
			}
			width={props.photo.width ?? 256}
			height={props.photo.height}
			alt={props.photo.src}
			title={
				props.photo.image?.getWidth() + "x" + props.photo.image?.getHeight()
			}
		/>
	);
}
