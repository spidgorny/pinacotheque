import React from "react";
import { Image } from "../model/Image";

export function BigImage(props: { image: Image }) {
	return (
		<LowSrcImg
			className="w-full"
			lowsrc={props.image.thumbURL}
			src={props.image.originalURL}
			width={props.image.getWidth()}
			height={props.image.getHeight()}
			alt={props.image.path}
			title={props.image.getWidth() + "x" + props.image.getHeight()}
		/>
	);
}

// copyright Joseph W Shelby. Released under the MIT License.
function LowSrcImg(
	props: React.DetailedHTMLProps<
		React.ImgHTMLAttributes<HTMLImageElement>,
		HTMLImageElement
	> & { lowsrc: string }
) {
	return (
		<div style={{ position: "relative", width: "100%" }}>
			<div style={{ position: "absolute", top: 0, left: 0, width: "100%" }}>
				<img {...props} src={props.lowsrc} width="100%" height="auto" />
			</div>
			<div style={{ position: "relative", top: 0, left: 0, width: "100%" }}>
				<img {...props} width="100%" height="auto" />
			</div>
		</div>
	);
}
