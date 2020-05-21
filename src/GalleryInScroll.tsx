import {Image} from "./model/Image";
import React from "react";
import Gallery, {PhotoProps, RenderImageProps} from "react-photo-gallery";
import {CustomPhotoProps, MyPhoto} from "./MyPhoto";
import Carousel, {Modal, ModalGateway} from "react-images";
import IntersectionVisible from 'react-intersection-visible';
import InfiniteScroll from "react-infinite-scroll-component";
import {PhotoSetItem} from "./ImageStream";
import {AppContext, context} from "./context";

interface IGalleryInScrollProps {
	items: any[];
	photos: PhotoSetItem[];
	next: any;
	refreshFunction: () => void;
}

export class GalleryInScroll extends React.Component<IGalleryInScrollProps, {
	currentImage?: number;
	viewerIsOpen: boolean;
}> {

	static contextType = context;
	context: AppContext;

	state = {
		currentImage: 0,
		viewerIsOpen: false,
	};

	render() {
		const imageRenderer =
			(props: RenderImageProps<PhotoProps<CustomPhotoProps>>) => (
				<IntersectionVisible
					key={props.photo.key}
					// onIntersect={ e => this.onIntersect( e ) }
					onHide={e => this.onImageHide(e, props.photo.image)}
					onShow={e => this.onImageShow(e, props.photo.image)}>
					<MyPhoto
						key={props.photo.key}
						margin={"2px"}
						index={props.index}
						photo={props.photo}
						left={props.left}
						top={props.top}
						direction={'row'}
						onClick={() => {
							this.openLightbox(props.index)
						}}
					/>
				</IntersectionVisible>
			);

		return <div>
			<InfiniteScroll
				dataLength={this.props.items.length} //This is important field to render the next data
				children={<Gallery photos={this.props.photos}
								   renderImage={imageRenderer}/>}
				next={this.props.next}
				hasMore={true}
				hasChildren={false}
				loader={<h4>Loading...</h4>}
				endMessage={
					<p style={{textAlign: "center"}}>
						<b>Yay! You have seen it all</b>
					</p>
				}
				// below props only if you need pull down functionality
				refreshFunction={this.props.refreshFunction}
				pullDownToRefresh
				pullDownToRefreshContent={
					<h3 style={{textAlign: "center"}}>&#8595; Pull down to refresh</h3>
				}
				releaseToRefreshContent={
					<h3 style={{textAlign: "center"}}>&#8593; Release to refresh</h3>
				}
			/>
			<ModalGateway>
				{this.state.viewerIsOpen ? (
					<Modal onClose={this.closeLightbox.bind(this)}>
						<Carousel
							currentIndex={this.state.currentImage}
							views={this.props.items.map((x: Image) => ({
								...x,
								source: {
									thumbnail: x.thumbURL,
									regular: x.originalURL,
								},
								caption: x.title,
							}))}
						/>
					</Modal>
				) : null}
			</ModalGateway>
		</div>;
	}

	openLightbox(index: number) {
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

	private onImageShow(e: IntersectionObserverEntry, image: Image) {
		// console.log('show', e);
		this.context.setState({
			[AppContext.VIEWPORT_TIMESTAMP]: image.date
		});
	}

	private onImageHide(e: IntersectionObserverEntry, image: Image) {
		// console.log('hide', e);
	}

}

