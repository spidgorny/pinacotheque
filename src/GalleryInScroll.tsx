import {Image} from "./model/Image";
import React from "react";
import Carousel, {Modal, ModalGateway} from "react-images";
import InfiniteScroll from "react-infinite-scroll-component";
import {PhotoSetItem} from "./ImageStream";
import {AppContext, context} from "./context";
import {MyGallery} from "./my-gallery";

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
	// @ts-ignore
	context: AppContext;

	state = {
		currentImage: 0,
		viewerIsOpen: false,
	};

	render() {
		return <div>
			<InfiniteScroll
				dataLength={this.props.items.length} //This is important field to render the next data
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
			>
				<MyGallery photos={this.props.photos} openLightbox={this.openLightbox.bind(this)}/>
			</InfiniteScroll>

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

}

