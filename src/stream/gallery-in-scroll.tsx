import { Image } from "../model/Image";
import React from "react";
import InfiniteScroll from "react-infinite-scroll-component";
import { AppContext, context } from "../context";
import { MyGallery } from "./my-gallery";
import { PhotoProps } from "react-photo-gallery";
import { ImageLightbox } from "./image-lightbox";

export interface PhotoSetItem extends PhotoProps {
	key?: string;
	src: string;
	width: number;
	height: number;
	image?: Image;
}

interface IGalleryInScrollProps {
	photos: Image[];
	next: any;
	refreshFunction: () => void;
}

export class GalleryInScroll extends React.Component<
	IGalleryInScrollProps,
	{
		currentImage?: number;
		viewerIsOpen: boolean;
	}
> {
	static contextType = context;
	// @ts-ignore
	context: AppContext;

	state = {
		currentImage: 0,
		viewerIsOpen: false,
	};

	componentDidMount() {
		// console.log(
		// 	this.props.photos.map(
		// 		(p: Image) => p.path + " [" + p.getWidth() + "x" + p.getHeight() + "]"
		// 	)
		// );
	}

	photoItems(): PhotoSetItem[] {
		const photoSet = this.props.photos.map((img: Image) => {
			// console.log(img);
			return {
				src: img.thumbURL,
				width: img.getWidth(),
				height: img.getHeight(),
				image: img,
			} as PhotoSetItem;
		});
		//console.log(photoSet.map(el => el.src + ' [' + el.width + 'x' + el.height + ']'));
		return photoSet;
	}

	render() {
		return (
			<div>
				<InfiniteScroll
					dataLength={this.photoItems().length} //This is important field to render the next data
					next={this.props.next}
					hasMore={true}
					hasChildren={false}
					loader={() => null}
					/*<h4 style={{ minHeight: 30 }}>*/
					/*	<ScaleLoader loading={true} color="#4DAF7C" /> Loading...*/
					/*</h4>*/
					/*}*/
					endMessage={
						<p style={{ textAlign: "center" }}>
							<b>Yay! You have seen it all</b>
						</p>
					}
					// below props only if you need pull down functionality
					refreshFunction={this.props.refreshFunction}
					pullDownToRefresh
					pullDownToRefreshContent={
						<h3 style={{ textAlign: "center" }}>
							&#8595; Pull down to refresh
						</h3>
					}
					releaseToRefreshContent={
						<h3 style={{ textAlign: "center" }}>&#8593; Release to refresh</h3>
					}
				>
					<MyGallery
						photos={this.photoItems()}
						openLightbox={this.openLightbox.bind(this)}
					/>
				</InfiniteScroll>

				<ImageLightbox
					viewerIsOpen={this.state.viewerIsOpen}
					onClose={this.closeLightbox.bind(this)}
					currentIndex={this.state.currentImage}
					images={this.props.photos}
				/>
			</div>
		);
	}

	openLightbox(index: number) {
		console.log("image", index);
		this.setState({
			currentImage: index,
			viewerIsOpen: true,
		});
	}

	closeLightbox() {
		this.setState({
			viewerIsOpen: false,
		});
	}
}
