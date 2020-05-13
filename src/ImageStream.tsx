import React from "react";
import Gallery, {PhotoProps, RenderImageProps} from 'react-photo-gallery';
import InfiniteScroll from "react-infinite-scroll-component";
import axios from 'redaxios';
import {Image} from "./model/Image";
import moment from 'moment';
import {CustomPhotoProps, MyPhoto} from "./MyPhoto";
import {AppContext, context} from "./context";
import Carousel, {Modal, ModalGateway} from "react-images";
import IntersectionVisible from 'react-intersection-visible';

interface IAppProps {
}

interface IAppState {
	start: Date;
	items: Image[];
	end?: Date;
	currentImage?: number;
	viewerIsOpen: boolean;
}

interface PhotoSetItem {
	src: string;
	width: number;
	height: number;
	image: Image;
}

export default class ImageStream extends React.Component<IAppProps, IAppState> {

	state = {
		start: new Date(),
		items: [],
		end: undefined,
		currentImage: 0,
		viewerIsOpen: false,
	}

	static contextType = context;
	context: AppContext;

	baseUrl;
	private static readonly VIEWPORT_TIMESTAMP: string = 'viewportTimestamp';

	componentDidMount() {
		this.baseUrl = this.context.baseUrl;
		const lastTopTimestamp = this.getStorage(ImageStream.VIEWPORT_TIMESTAMP);
		console.log('lastTopTimestamp', lastTopTimestamp);
		if (lastTopTimestamp) {
			this.state.start = new Date(lastTopTimestamp);
		}
		this.fetchData();
	}

	async fetchData() {
		const urlImages = new URL('Images', this.baseUrl);
		urlImages.searchParams.set('since', moment(this.state.end || this.state.start).format('YYYY-MM-DD HH:mm:ss'));
		//console.log(urlImages);
		const res = await axios.get(urlImages.toString(), {
			cors: 'no-cors',
		});
		// console.log(res.data);
		const resData = JSON.parse(res.data);
		if (resData.status !== 'ok') {
			throw new Error(resData.error);
		}

		const images: Image[] = resData.data.map(el => new Image(el));

		const lastImage = images[images.length - 1];
		const lastDate = lastImage.getTimestamp();
		// console.log(lastImage, lastDate);

		this.setState(({items}) => {
			// append if not id exists
			images.map(nnew => {
				if (!items.some(el => el.id === nnew.id)) {
					nnew.baseUrl = this.baseUrl;
					return items.push(nnew);
				}
			});
			return ({
				items,
				end: lastDate,
			});
		}, () => {
			// console.log(this.state.items.map((el: Image) => el.id));
		});
	}

	render() {
		const photoSet = this.state.items.map(img => {
			return {
				src: img.thumbURL,
				width: img.width,
				height: img.height,
				image: img,
			} as PhotoSetItem;
		});

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

		return (
			<div>
				<InfiniteScroll
					dataLength={this.state.items.length} //This is important field to render the next data
					children={<Gallery photos={photoSet}
									   renderImage={imageRenderer}/>}
					next={this.fetchData.bind(this)}
					hasMore={true}
					hasChildren={false}
					loader={<h4>Loading...</h4>}
					endMessage={
						<p style={{textAlign: 'center'}}>
							<b>Yay! You have seen it all</b>
						</p>
					}
					// below props only if you need pull down functionality
					refreshFunction={this.refresh.bind(this)}
					pullDownToRefresh
					pullDownToRefreshContent={
						<h3 style={{textAlign: 'center'}}>&#8595; Pull down to refresh</h3>
					}
					releaseToRefreshContent={
						<h3 style={{textAlign: 'center'}}>&#8593; Release to refresh</h3>
					}
				/>
				<ModalGateway>
					{this.state.viewerIsOpen ? (
						<Modal onClose={this.closeLightbox.bind(this)}>
							<Carousel
								currentIndex={this.state.currentImage}
								views={this.state.items.map((x: Image) => ({
									...x,
									source: x.thumbURL,
									caption: x.title,
								}))}
							/>
						</Modal>
					) : null}
				</ModalGateway>
			</div>
		);
	}

	refresh() {
		console.log('refresh');
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
		this.setStorage(ImageStream.VIEWPORT_TIMESTAMP, image.date);
	}

	private onImageHide(e: IntersectionObserverEntry, image: Image) {
		// console.log('hide', e);
	}

	setStorage(name: string, val: any) {
		window.localStorage.setItem(name, JSON.stringify(val));
	}

	getStorage(name: string) {
		return JSON.parse(window.localStorage.getItem(name));
	}

}
