import React from "react";
import { PhotoProps, RenderImageProps } from "react-photo-gallery";
import moment from "moment";
import { PhotoSetItem } from "./GalleryInScroll";

interface Props {
	index: number;
	direction?: "row" | "column";
	left?: number;
	top?: number;
	photo: PhotoProps<PhotoSetItem>;
	onClick: (e: React.MouseEvent, photo: PhotoProps, index: number) => void;
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
			border: "solid 1px red",
		};
		if (this.props.direction === "column") {
			cont.position = "absolute";
			cont.left = this.props.left;
			cont.top = this.props.top;
		}
		return (
			<div
				// key={this.props.key}
				style={cont}
				onClick={(e) =>
					this.props.onClick
						? this.props.onClick(e, this.props.photo, this.props.index)
						: null
				}
				ref={this.ref}
			>
				<img
					src={this.props.photo.src}
					width={this.props.photo.width}
					height={this.props.photo.height}
					onMouseEnter={this.showOverlay.bind(this)}
					onMouseOut={this.hideOverlay.bind(this)}
					alt={this.props.photo.src}
					title={
						this.props.photo.image?.getWidth() +
						"x" +
						this.props.photo.image?.getHeight()
					}
				/>
				{(this.state.showOverlay || this.props.forceInfo) && (
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
				{this.bounds?.top} - {this.state.mouseY} - {this.bounds?.bottom}
			</div>
		);
	}

	showOverlay() {
		this.setState({
			showOverlay: true,
		});
	}

	hideOverlay(e: React.MouseEvent) {
		this.bounds = this.ref?.current?.getBoundingClientRect();
		this.setState({
			mouseX: e.pageX,
			mouseY: e.pageY,
		});
		// e.pageX += window.pageXOffset;
		// e.pageY += window.pageYOffset;
		// console.log(e.pageX, e.pageY, this.bounds);
		if (this.bounds) {
			const insideX = this.bounds.left < e.pageX && e.pageX < this.bounds.right;
			const insideY = this.bounds.top < e.pageY && e.pageY < this.bounds.bottom;
			console.log(
				insideX,
				insideY,
				this.bounds.top,
				"<",
				e.pageY,
				"<",
				this.bounds.bottom
			);
			if (!insideX || !insideY) {
				this.setState({
					showOverlay: false,
				});
			}
		}
	}
}
