import React from "react";
import Gallery, { PhotoProps, RenderImageProps } from "react-photo-gallery";
import { MyPhoto } from "./my-photo";
// @ts-ignore
import IntersectionVisible from "react-intersection-visible";
import { Image } from "../model/Image";
import { PhotoSetItem } from "./gallery-in-scroll";
import TrackVisibility from "react-on-screen";
const VisibilitySensor = require("react-visibility-sensor");

/**
 * @deprecated temporary not used
 * @param props
 */
export class ImageRenderIntersection extends React.Component<
	RenderImageProps<PhotoProps<PhotoSetItem>>
> {
	render() {
		return (
			<IntersectionVisible
				key={this.props.photo.key}
				// onIntersect={ e => this.onIntersect( e ) }
				onHide={(e: any) =>
					this.props.photo.image && this.onImageHide(e, this.props.photo.image)
				}
				onShow={(e: any) =>
					this.props.photo.image && this.onImageShow(e, this.props.photo.image)
				}
			>
				<MyPhoto
					key={this.props.photo.key}
					index={this.props.index}
					direction={this.props.direction}
					left={this.props.left}
					top={this.props.top}
					photo={this.props.photo}
					onClick={(index: any) => {
						console.log("click", index);
						// this.props.openLightbox(this.props.index);
					}}
				/>
			</IntersectionVisible>
		);
	}

	private onImageShow(e: IntersectionObserverEntry, image: Image) {
		// console.log('show', e);
		// this.context.setState({
		// 	[AppContext.VIEWPORT_TIMESTAMP]: image.date,
		// });
	}

	private onImageHide(e: IntersectionObserverEntry, image: Image) {
		// console.log('hide', e);
	}
}

interface Props {
	photos: PhotoSetItem[];
	openLightbox: (index: number) => void;
}

export class MyGallery extends React.Component<Props, any> {
	imageRender(props: RenderImageProps<PhotoProps<PhotoSetItem>>) {
		return (
			<MyPhoto
				key={props.photo.key}
				index={props.index}
				photo={props.photo}
				direction={props.direction}
				left={props.left}
				top={props.top}
				onClick={() => {
					this.props.openLightbox(props.index);
				}}
			/>
		);
	}

	imageRenderVisibilitySensor(
		props: RenderImageProps<PhotoProps<PhotoSetItem>>
	) {
		return (
			<VisibilitySensor>
				{({ isVisible }: { isVisible: boolean }) =>
					isVisible ? (
						<MyPhoto
							key={props.photo.key}
							index={props.index}
							photo={props.photo}
							direction={props.direction}
							left={props.left}
							top={props.top}
							onClick={() => {
								this.props.openLightbox(props.index);
							}}
						/>
					) : (
						<div
							style={{ width: props.photo.width, height: props.photo.height }}
						/>
					)
				}
			</VisibilitySensor>
		);
	}

	imageRenderOnScreen(props: RenderImageProps<PhotoProps<PhotoSetItem>>) {
		return (
			<TrackVisibility partialVisibility={true} key={props.photo.key}>
				{({ isVisible }: { isVisible: boolean }) =>
					isVisible ? (
						<MyPhoto
							key={props.photo.key}
							index={props.index}
							photo={props.photo}
							direction={props.direction}
							left={props.left}
							top={props.top}
							onClick={() => {
								this.props.openLightbox(props.index);
							}}
						/>
					) : (
						<div
							style={{ width: props.photo.width, height: props.photo.height }}
						/>
					)
				}
			</TrackVisibility>
		);
	}

	render() {
		// @ts-ignore
		return (
			<Gallery
				direction="row"
				columns={4}
				targetRowHeight={256}
				margin={5}
				photos={this.props.photos}
				renderImage={this.imageRender.bind(this)}
				// renderImage={this.imageRenderVisibilitySensor.bind(this)}
				// renderImage={this.imageRenderOnScreen.bind(this)}
				// renderImage={(props: RenderImageProps<any>) => (
				// 	<ImageRenderIntersection {...props} />
				// )}
			/>
		);
	}
}
