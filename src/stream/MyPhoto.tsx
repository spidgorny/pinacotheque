import React from "react";
import { PhotoProps } from "react-photo-gallery";
import moment from "moment";
import { PhotoSetItem } from "./GalleryInScroll";
// @ts-ignore
import ReactHoverObserver from "react-hover-observer";
import { Image } from "../model/Image";

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
							// key={this.props.key}
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
							<img
								src={
									this.props.photo.image?.isDir()
										? "https://camo.githubusercontent.com/8fc860423cb8edf1e787428fba028d39626cbee558f079361a958fe83ce363d1/68747470733a2f2f776963672e6769746875622e696f2f656e74726965732d6170692f6c6f676f2d666f6c6465722e737667"
										: this.props.photo.src
								}
								width={this.props.photo.width ?? 256}
								height={this.props.photo.height}
								// onMouseEnter={this.showOverlay.bind(this)}
								// onMouseOut={this.hideOverlay.bind(this)}
								alt={this.props.photo.src}
								title={
									this.props.photo.image?.getWidth() +
									"x" +
									this.props.photo.image?.getHeight()
								}
							/>
							{(this.props.forceInfo || isHovering) && (
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
										<div>Name: {this.props.photo.image?.basename}</div>
										<div>Path: {this.props.photo.image?.pathEnd}</div>
										<div>
											Size:{" "}
											{this.props.photo.image?.getWidth() +
												"x" +
												this.props.photo.image?.getHeight()}
										</div>
										<div>
											Date:{" "}
											{moment(this.props.photo.image?.getTimestamp()).format(
												"YYYY-MM-DD HH:mm:ss"
											)}
										</div>
									</div>
								</div>
							)}
						</div>
					);
				}}
			</ReactHoverObserver>
		);
	}
}
