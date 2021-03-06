import React from "react";
import { PhotoProps } from "react-photo-gallery";
import moment from "moment";
import { PhotoSetItem } from "./gallery-in-scroll";
// @ts-ignore
import ReactHoverObserver from "react-hover-observer";
import { Image } from "../model/Image";
import { ImageThumb } from "./image-thumb";

interface Props {
	index: number;
	direction?: "row" | "column";
	left?: number;
	top?: number;
	photo: PhotoProps<PhotoSetItem>;
	onClick: (
		e: React.MouseEvent,
		photo: PhotoProps,
		index: number,
		image?: Image
	) => void;
	forceInfo?: boolean;
}

interface MyPhotoState {
	showOverlay: boolean;
	mouseX?: number;
	mouseY?: number;
}

function PhotoOverlay(props: { photo: PhotoProps<PhotoSetItem> }) {
	return (
		<div
			style={{
				position: "absolute",
				top: 0,
				width: "100%",
				color: "white",
			}}
		>
			<div
				style={{
					background: "black",
					opacity: 0.5,
					padding: "0.5em",
				}}
			>
				<div>Name: {props.photo.image?.basename}</div>
				<div>Path: {props.photo.image?.pathEnd}</div>
				<div>
					Size:{" "}
					{props.photo.image?.getWidth() + "x" + props.photo.image?.getHeight()}
				</div>
				<div>
					Date:{" "}
					{moment(props.photo.image?.getTimestamp()).format(
						"YYYY-MM-DD HH:mm:ss"
					)}
				</div>
			</div>
		</div>
	);
}

export class MyPhoto extends React.Component<Props, MyPhotoState> {
	margin = 2;
	ref = React.createRef<HTMLDivElement>();
	bounds?: ClientRect & { x: number; y: number };
	state: MyPhotoState = {
		showOverlay: false,
	};

	render() {
		//console.log(this.props);
		let cont: any = {
			margin: this.margin,
			position: "relative",
			width: this.props.photo.width,
			height: this.props.photo.height,
		};
		if (this.props.direction === "column") {
			cont.position = "absolute";
			cont.left = this.props.left;
			cont.top = this.props.top;
		}
		return (
			<ReactHoverObserver>
				{({ isHovering }: { isHovering: boolean }) => {
					return (
						<div
							key={this.props.photo.key}
							style={{ ...cont }}
							onClick={(e) =>
								this.props.onClick
									? this.props.onClick(
											e,
											this.props.photo,
											this.props.index,
											this.props.photo.image
									  )
									: null
							}
							ref={this.ref}
						>
							<ImageThumb photo={this.props.photo} />
							{(this.props.forceInfo || isHovering) && (
								<PhotoOverlay photo={this.props.photo} />
							)}
						</div>
					);
				}}
			</ReactHoverObserver>
		);
	}
}
