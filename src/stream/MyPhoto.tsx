import React from "react";
import { PhotoProps, RenderImageProps } from "react-photo-gallery";
import moment from "moment";
import { PhotoSetItem } from "./GalleryInScroll";

interface Props {
	index: number;
	direction: "row" | "column";
	left?: number;
	top?: number;
	photo: PhotoProps<PhotoSetItem>;
	onClick: (e: React.MouseEvent, photo: PhotoProps, index: number) => void;
}

interface MyPhotoState {
	showOverlay: boolean;
}

export class MyPhoto extends React.Component<Props, MyPhotoState> {
	margin = 2;
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
			<div
				// key={this.props.key}
				style={cont}
				onClick={(e) =>
					this.props.onClick
						? this.props.onClick(e, this.props.photo, this.props.index)
						: null
				}
			>
				<img
					src={this.props.photo.src}
					width={this.props.photo.width}
					height={this.props.photo.height}
					onMouseEnter={this.showOverlay.bind(this)}
					onMouseLeave={this.hideOverlay.bind(this)}
					alt={this.props.photo.src}
					title={
						this.props.photo.image?.getWidth() +
						"x" +
						this.props.photo.image?.getHeight()
					}
				/>
				{this.state.showOverlay && (
					<div
						style={{
							position: "absolute",
							top: 0,
							width: "100%",
							textAlign: "center",
						}}
					>
						<div
							style={{
								background: "black",
								opacity: 0.5,
								padding: "0.5em",
							}}
						>
							<div style={{ opacity: 1 }}>
								Path: {this.props.photo.image?.path}
							</div>
							<div>
								{moment(this.props.photo.image?.getTimestamp()).format(
									"YYYY-MM-DD HH:mm:ss"
								)}
							</div>
						</div>
					</div>
				)}
			</div>
		);
	}

	showOverlay() {
		this.setState({
			showOverlay: true,
		});
	}

	hideOverlay() {
		this.setState({
			showOverlay: false,
		});
	}
}
