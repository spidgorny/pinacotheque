import React from "react";
import ImageStream from "./stream/ImageStream";
import { AppContext, context } from "./context";
// @ts-ignore
import axios from "redaxios";

import ScaleLoader from "react-spinners/ScaleLoader";
import { Sidebar } from "./stream/Sidebar";
import "./test.object.assign";
import { Header } from "./widgets/header";
import "./app.css";
import { Route, Switch } from "wouter";
import BrowsePage from "./browse/browse-page";
import OneSourcePage from "./browse/one-source-page";
import { QueryClient, QueryClientProvider } from "react-query";
import { ReactQueryDevtools } from "react-query/devtools";

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

function StreamPage(props: {
	sources: Source[];
	sourceID?: number;
	setSource: (id?: number) => void;
}) {
	return (
		<div className="flex flex-row p-2">
			<div className="w-2/12">
				<Sidebar
					sources={props.sources}
					sourceID={props.sourceID}
					setSource={props.setSource}
				/>
			</div>
			<div className="flex-grow">
				{props.sources === null ? (
					<ScaleLoader loading={true} color="#4DAF7C" />
				) : (
					<ImageStream sourceID={props.sourceID} />
				)}
			</div>
		</div>
	);
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
					</Switch>
				</div>
				<ReactQueryDevtools initialIsOpen={true} />
			</QueryClientProvider>
		);
	}
}
