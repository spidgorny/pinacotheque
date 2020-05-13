import React from "react";
import Gallery, {PhotoProps} from 'react-photo-gallery';
import InfiniteScroll from "react-infinite-scroll-component";
import axios from 'redaxios';
import {Image} from "./model/Image";
import moment from 'moment';
import {CustomPhotoProps, MyPhoto} from "./MyPhoto";
import {AppContext, context} from "./context";
import Carousel, { Modal, ModalGateway } from "react-images";

interface IAppProps {
}

interface IAppState {
	start: Date;
	items: Image[];
	end?: Date;
	currentImage?: number;
	viewerIsOpen: boolean;
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

	componentDidMount() {
		this.baseUrl = this.context.baseUrl;
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

		const lastImage = images[images.length-1];
		const lastDate = lastImage.getTimestamp();
		// console.log(lastImage, lastDate);

		this.setState(({items}) => {
			// append if not id exists
			images.map(nnew => {
				if (!items.some(el => el.id == nnew.id)) {
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
			};
		});

		const imageRenderer =
			(props: any) => (
				<MyPhoto
					key={props.key}
					margin={"2px"}
					index={props.index}
					photo={props.photo}
					left={props.left}
					top={props.top}
					direction={'row'}
					onClick={() => {this.openLightbox(props.index)}}
				/>
			);

		return (
			<div>
				<InfiniteScroll
					dataLength={this.state.items.length} //This is important field to render the next data
					children={<Gallery photos={photoSet} renderImage={imageRenderer}/>}
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

}
