import React from "react";
import axios from "redaxios";
import { Image } from "../model/Image";
import moment from "moment";
import { AppContext, context } from "../context";
import StreamRenderGallery from "./stream-render-gallery";
import StreamRenderSimple from "./stream-render-simple";

interface IAppProps {
	sourceID?: number;
}

interface IAppState {
	start: Date;
	items: Image[];
	end?: Date;
}

export default class ImageStream extends React.Component<IAppProps, IAppState> {
	state: IAppState = {
		start: new Date(),
		items: [],
		end: undefined,
	};

	static contextType = context;
	// @ts-ignore
	context: AppContext;

	baseUrl: URL = new URL(document.location.href);

	componentDidMount() {
		this.baseUrl = this.context.baseUrl;
		if (this.context.lastTopTimestamp) {
			// this.setState({
			// 	end: this.context.lastTopTimestamp,
			// });
			this.state.end = this.context.lastTopTimestamp;
		}
		this.fetchData().then();
	}

	async fetchData() {
		const images = await this.fetchDataFromFilterServer();
		this.appendImages(images);
	}

	async fetchDataFromFilterServer() {
		const urlImages = new URL(process.env.REACT_APP_API + "Images");
		this.appendSearchParams(urlImages);
		//console.log(urlImages);
		const res = await axios.get(urlImages.toString());
		// console.log(res.data);
		const resData = res.data;
		if (resData.status !== "ok") {
			throw new Error(resData.error);
		}

		const images: Image[] = resData.data.map((el: Image) => new Image(el));
		return images;
	}

	appendSearchParams(urlImages: URL) {
		if (this.props.sourceID) {
			urlImages.searchParams.set("source", this.props.sourceID.toString());
		}
		urlImages.searchParams.set(
			"since",
			moment(this.state.end || this.state.start).format("YYYY-MM-DD HH:mm:ss")
		);
		let minWidth = this.context.sidebar?.minWidth;
		if (minWidth) {
			urlImages.searchParams.set("minWidth", minWidth.toString());
		}
	}

	appendImages(images: any[]) {
		if (!images.length) {
			return;
		}
		const lastImage = images[images.length - 1];
		const lastDate = lastImage.getTimestamp();
		// console.log(lastImage, lastDate);
		this.setState(
			({ items }) => {
				// append if not id exists
				images.map((nnew: Image) => {
					if (!items.some((el) => el.id === nnew.id)) {
						nnew.baseUrl = this.baseUrl?.toString();
						return items.push(nnew);
					}
					return "x";
				});
				return {
					items,
					end: lastDate,
				};
			},
			() => {
				// console.log(this.state.items.map((el: Image) => el.id));
			}
		);
	}

	get queryParams() {
		const url = new URL(document.location.href);
		return url.searchParams;
	}

	render() {
		console.log(this.state.items.length);
		return (
			<div>
				<div
					className="p-2"
					style={{
						background: "#33C3F0",
						position: "sticky",
						top: 0,
						zIndex: 100,
					}}
				>
					Images: {this.state.items.length}
				</div>
				{this.queryParams.has("simple") ? (
					<StreamRenderSimple
						photos={this.state.items}
						next={this.fetchData.bind(this)}
						refresh={this.refresh.bind(this)}
					/>
				) : (
					<StreamRenderGallery
						photos={this.state.items}
						next={this.fetchData.bind(this)}
						refresh={this.refresh.bind(this)}
					/>
				)}
			</div>
		);
	}

	componentDidUpdate(
		prevProps: Readonly<IAppProps>,
		nextState: Readonly<IAppState>,
		nextContext: any
	): boolean {
		const yes = this.props.sourceID !== prevProps.sourceID;
		if (yes) {
			this.setState({ items: [], start: new Date(), end: undefined }, () =>
				this.fetchData().then()
			);
		}
		return yes;
	}

	refresh() {
		console.log("refresh");
	}
}
