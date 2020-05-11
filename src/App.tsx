import React from "react";
import Gallery from 'react-photo-gallery';
import InfiniteScroll from "react-infinite-scroll-component";
import axios from 'redaxios';
import {Image} from "./model/Image";
import moment from 'moment';
import {MyPhoto} from "./MyPhoto";

interface IAppProps {
}

interface IAppState {
	start: Date;
	items: Image[];
	end?: Date;
}

export default class App extends React.Component<IAppProps, IAppState> {

	state = {
		start: new Date(),
		items: [],
		end: undefined,
	}

	baseUrl = new URL('http://192.168.1.120/');

	componentDidMount() {
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
					return items.push(nnew);
				}
			});
			return ({
				items,
				end: lastDate,
			});
		}, () => {
			console.log(this.state.items.map((el: Image) => el.id));
		});
	}

	render() {
		const photoSet = this.state.items.map(img => {
			const url = new URL('ShowThumb', this.baseUrl);
			url.searchParams.set('file', img.id);
			return {
				src: url.toString(),
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
					onClick={() => {}}
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
					}/>
			</div>
		);
	}

	refresh() {

	}

}
