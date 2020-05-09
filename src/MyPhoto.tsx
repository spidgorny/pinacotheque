import React from "react";
import {PhotoProps, RenderImageProps} from "react-photo-gallery";
import {Image} from "./model/Image";
import moment from "moment";

interface MyPhotoState {
	showOverlay: boolean;
}

interface CustomPhotoProps {
	image: Image;
}

export class MyPhoto extends React.Component<RenderImageProps<PhotoProps<CustomPhotoProps>>, MyPhotoState> {

	state: MyPhotoState = {
		showOverlay: false,
	};

	render() {
		//console.log(this.props);
		return <div
			style={{
				margin: this.props.margin,
				position: 'relative'
			}}
		>
			<img
				src={this.props.photo.src}
				width={this.props.photo.width}
				height={this.props.photo.height}
				onMouseEnter={this.showOverlay.bind(this)}
				onMouseLeave={this.hideOverlay.bind(this)}
			/>
			{this.state.showOverlay &&
			<div style={{
				position: 'absolute',
				top: 0,
				width: '100%',
				textAlign: 'center'
			}}>
				<div style={{
					background: 'black',
					opacity: 0.5,
					padding: '0.5em',
				}}>
					<div style={{opacity: 1}}>
						{this.props.photo.image.path}
					</div>
					<div>
						{moment(this.props.photo.image.getTimestamp()).format('YYYY-MM-DD HH:mm:ss')}
					</div>
				</div>
			</div>
			}
		</div>;
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
