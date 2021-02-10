import React from "react";
import Gallery, { PhotoProps, RenderImageProps } from "react-photo-gallery";
import { CustomPhotoProps, MyPhoto } from "./MyPhoto";
// @ts-ignore
import IntersectionVisible from "react-intersection-visible";
import { Image } from "../model/Image";
import { AppContext } from "../context";
import { PhotoSetItem } from "./GalleryInScroll";

interface Props {
	photos: PhotoSetItem[];
	openLightbox: (index: number) => void;
}

export class MyGallery extends React.Component<Props, any> {
	/**
	 * @deprecated temporary not used
	 * @param props
	 */
	imageRenderer(props: RenderImageProps<PhotoProps<CustomPhotoProps>>) {
		return (
			<IntersectionVisible
				key={props.photo.key}
				// onIntersect={ e => this.onIntersect( e ) }
				onHide={(e: any) => this.onImageHide(e, props.photo.image)}
				onShow={(e: any) => this.onImageShow(e, props.photo.image)}
			>
				<MyPhoto
					key={props.photo.key}
					margin={"2px"}
					index={props.index}
					photo={props.photo}
					left={props.left}
					top={props.top}
					direction={"row"}
					onClick={() => {
						this.props.openLightbox(props.index);
					}}
				/>
			</IntersectionVisible>
		);
	}

	render() {
		// @ts-ignore
		return (
			<Gallery
				photos={this.props.photos}
				/*								renderImage={this.imageRenderer.bind(this)}*/
			/>
		);
	}

	private onImageShow(e: IntersectionObserverEntry, image: Image) {
		// console.log('show', e);
		this.context.setState({
			[AppContext.VIEWPORT_TIMESTAMP]: image.date,
		});
	}

	private onImageHide(e: IntersectionObserverEntry, image: Image) {
		// console.log('hide', e);
	}
}
