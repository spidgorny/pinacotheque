import React from "react";
import { AppContext, context } from "./context";
// @ts-ignore
import axios from "redaxios";
import ScaleLoader from "react-spinners/ScaleLoader";
import "./test.object.assign";
import { Header } from "./widgets/header";
import "./app.css";
import { Route, Switch } from "wouter";
import BrowsePage from "./browse/browse-page";
import OneSourcePage from "./browse/one-source-page";
import { QueryClient, QueryClientProvider } from "react-query";
import { ReactQueryDevtools } from "react-query/devtools";
import { VisibilityTest } from "./test/visibility-test";
import StreamPage from "./stream/stream-page";
import { OneImageTest } from "./test/one-image-test";
import TimelineTest from "./test/timeline-test";
import FoldersPage from "./folders/folders-page";
import SingleImage from "./single/single-image";
import SearchPage from "./search/search-page";

interface IAppProps {}

export interface Source {
	id: number;
	name: string;
	path: string;
	thumbRoot: string;
	files: number;
	folders: number;
	md5: string;
	_missingProperties: [];
}

export interface IAppState {
	status?: "ok";
	min?: string;
	max?: string;
	sources: Source[];
	query?: string;
	duration?: number;
	sourceID?: number;
}

const queryClient = new QueryClient();

export default class App extends React.Component<IAppProps, IAppState> {
	static contextType = context;
	// @ts-ignore
	context: AppContext;
	baseUrl: URL | undefined;

	state: IAppState = {
		sources: [],
		sourceID: undefined,
	};

	componentDidMount() {
		this.baseUrl = this.context.baseUrl;
		// noinspection JSIgnoredPromiseFromCall
		this.fetchRange();
	}

	async fetchRange() {
		const urlInfo = new URL("Info", this.baseUrl);
		const res = await axios.get(urlInfo.toString());
		// console.log(res.data);
		const resData = res.data;
		if (resData.status !== "ok") {
			throw new Error(resData.error);
		}
		// console.log(resData);
		this.setState(resData);
		this.context.setSources(resData.sources);
	}

	setSource(id?: number) {
		console.log("setSource", id);
		this.setState({ sourceID: id });
	}

	render() {
		return (
			<QueryClientProvider client={queryClient}>
				<div className="container" style={{ width: "100%", maxWidth: "100%" }}>
					<Header />
					<Switch>
						<Route path="/">
							<StreamPage
								sources={this.state.sources}
								sourceID={this.state.sourceID}
								setSource={this.setSource.bind(this)}
							/>
						</Route>
						<Route path="/browse">
							{this.state.sources ? (
								<BrowsePage
									sources={this.state.sources || []}
									reloadSources={this.fetchRange.bind(this)}
								/>
							) : (
								<ScaleLoader />
							)}
						</Route>
						<Route path="/browse/:slug">
							{(params) => (
								<OneSourcePage
									name={params.slug}
									sources={this.state.sources || []}
									reloadSources={this.fetchRange.bind(this)}
								/>
							)}
						</Route>
						<Route path="/visibility" component={VisibilityTest} />
						<Route path="/one-image" component={OneImageTest} />
						<Route path="/timeline-test" component={TimelineTest} />
						<Route path="/folders/:slug*">
							{(params) => (
								<FoldersPage
									sources={this.state.sources}
									sourceID={this.state.sourceID}
									setSource={this.setSource.bind(this)}
									slug={decodeURIComponent(params.slug)}
								/>
							)}
						</Route>
						<Route path="/image/:id">
							{(params) => <SingleImage id={params.id} />}
						</Route>
						<Route path="/search/:term">
							{(params) => <SearchPage term={params.term} />}
						</Route>
					</Switch>
				</div>
				<ReactQueryDevtools initialIsOpen={true} />
			</QueryClientProvider>
		);
	}
}
