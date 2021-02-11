import { Image } from "../model/Image";
import React from "react";

export default function StreamRenderSimple(props: {
	photos: Image[];
	next: () => void;
	refresh: () => void;
}) {
	return (
		<div>
			{props.photos.map((el: Image) => (
				<div style={{ clear: "both" }} key={el.src}>
					<img src={el.src} style={{ float: "left" }} alt={el.src} />
					{el.src}
					<br />
					{el.width}x{el.height}
					<br />
					{/*{JSON.stringify(el.image, null, 2)}*/}
				</div>
			))}
		</div>
	);
}
