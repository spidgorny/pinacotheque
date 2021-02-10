import React from "react";
import { RouteComponentProps } from "wouter";
// const VisibilitySensor = require("react-visibility-sensor");
import TrackVisibility from "react-on-screen";

const TrackElement = (props: { isVisible?: boolean }) => {
	const style = {
		width: 256,
		height: 256,
		border: "solid 1px silver",
		background: props.isVisible ? "red" : "blue",
	};

	return <div style={style}>Hello</div>;
};

export class VisibilityTest extends React.Component<
	RouteComponentProps<{}>,
	{ isVisible: boolean }
> {
	state = { isVisible: true };

	// render1() {
	// 	return (
	// 		<VisibilitySensor>
	// 			{({ isVisible }: { isVisible: boolean }) =>
	// 				isVisible ? (
	// 					<div>Visible</div>
	// 				) : (
	// 					<div style={{ width: 256, height: 256 }} />
	// 				)
	// 			}
	// 		</VisibilitySensor>
	// 	);
	// }

	// onChange(visible: boolean) {
	// 	this.setState({ isVisible: visible });
	// }

	// render2() {
	// 	return (
	// 		<VisibilitySensor onChange={this.onChange.bind(this)}>
	// 			<div>
	// 				... {this.state.isVisible ? "visible" : "hidden"} content goes here...
	// 			</div>
	// 		</VisibilitySensor>
	// 	);
	// }

	render() {
		return (
			<>
				<TrackVisibility>
					<TrackElement />
				</TrackVisibility>
				<TrackVisibility>
					<TrackElement />
				</TrackVisibility>
				<TrackVisibility>
					<TrackElement />
				</TrackVisibility>
				<TrackVisibility>
					<TrackElement />
				</TrackVisibility>
				<TrackVisibility partialVisibility={true}>
					<TrackElement />
				</TrackVisibility>
			</>
		);
	}
}
