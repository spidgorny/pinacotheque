import React from "react";
import axios from "redaxios";
import { Image } from "../model/Image";
import moment from "moment";
import { AppContext, context } from "../context";
import { GalleryInScroll } from "./GalleryInScroll";

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
			this.setState({
				end: this.context.lastTopTimestamp,
			});
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

	async fetchDataFromPHP() {
		const urlImages = new URL("Images", this.baseUrl);
		this.appendSearchParams(urlImages);
		//console.log(urlImages);
		const res = await axios.get(urlImages.toString(), {
			// cors: 'no-cors',
		});
		// console.log(res.data);
		const resData = JSON.parse(res.data);
		if (resData.status !== "ok") {
			throw new Error(resData.error);
		}

		const images: Image[] = resData.data.map((el: Image) => new Image(el));
		this.appendImages(images);
	}

	get queryParams() {
		const url = new URL(document.location.href);
		return url.searchParams;
	}

	render() {
		if (this.queryParams.has("simple")) {
			return (
				<div>
					<div
						className=""
						style={{ background: "#33C3F0", position: "sticky" }}
					>
						Images: {this.state.items.length}
					</div>
					<div>
						{this.state.items.map((el: Image) => (
							<div style={{ clear: "both" }} key={el.src}>
								<img src={el.src} style={{ float: "left" }} alt={el.src} />
								{el.src}
								<br />
								{el.width}x{el.height}
								<br />
								{/*{JSON.stringify(el.image, null, 2)}*/}
							</div>
						))}
					</div>
				</div>
			);
		}

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
				<GalleryInScroll
					photos={this.state.items}
					next={this.fetchData.bind(this)}
					refreshFunction={this.refresh.bind(this)}
				/>
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
