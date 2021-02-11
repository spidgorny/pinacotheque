import PropTypes from "prop-types";
import React from "react";

export interface point {
	x: number;
	y: number;
}

interface Props {
	points?: point[];
	height?: number;
	style?: any;
	barWidth?: number;
	margin?: number;
	onMouseMove?: (x: point, i: number) => void;
	onClick?: (x: point, i: number) => void;
	calcStyle?: (i: number) => object;
}

export default class ClickableSparklineBars extends React.Component<Props> {
	static propTypes = {
		points: PropTypes.arrayOf(PropTypes.object),
		height: PropTypes.number,
		style: PropTypes.object,
		barWidth: PropTypes.number,
		margin: PropTypes.number,
		onMouseMove: PropTypes.func,
		onClick: PropTypes.func,
	};

	static defaultProps = {
		style: { fill: "slategray" },
	};

	render() {
		const {
			points,
			height,
			style,
			barWidth,
			margin,
			onMouseMove,
			onClick,
			calcStyle,
		} = this.props;

		if (!points) {
			return;
		}

		if (!height) {
			return;
		}

		const strokeWidth = 1 * ((style && style.strokeWidth) || 0);
		const marginWidth = margin ? 2 * margin : 0;
		const width =
			barWidth ||
			(points && points.length >= 2
				? Math.max(0.9, points[1].x - points[0].x - strokeWidth - marginWidth)
				: 0.2);

		return (
			<g transform="scale(1,-1)">
				{points.map((p: { x: number; y: number }, i: number) => {
					const hereStyle = calcStyle ? { ...style, ...calcStyle(i) } : style;
					return (
						<rect
							key={i}
							x={p.x - (width + strokeWidth) / 2}
							y={-height}
							width={width}
							height={Math.max(0, height - p.y)}
							style={hereStyle}
							onMouseMove={onMouseMove && onMouseMove.bind(this, p, i)}
							onClick={onClick && onClick.bind(this, p, i)}
						/>
					);
				})}
			</g>
		);
	}
}
