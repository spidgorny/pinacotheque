import React, { useState } from "react";
import Timeline from "../widgets/timeline";
import moment from "moment";
import Histogram from "../widgets/histogram";
import { ymd } from "../functions";

export function generateRandomHistogram(
	minDate: Date,
	date: Date,
	maxDate: Date
) {
	const daily = [];
	for (let i = 0; i < 365; i++) {
		const running = moment(date)
			.dayOfYear(i + 1)
			.toDate();
		let random = Math.floor(Math.random() * 50);
		let moreMin =
			minDate.getFullYear() !== running.getFullYear() ||
			minDate.getTime() < running.getTime();
		let lessMax =
			maxDate.getFullYear() !== running.getFullYear() ||
			running.getTime() < maxDate.getTime();
		let between = moreMin && lessMax;
		daily.push(between ? random : 0);
	}

	return daily;
}

export default function TimelineTest(props: {}) {
	// let minDate = new Date(2019, 1, 21);
	// let maxDate = new Date();
	const [date, setDate] = useState(new Date());

	return (
		<div className="container mx-auto p-2" style={{ maxWidth: "100%" }}>
			<Histogram>
				{(data: Record<string, number>) => {
					const minDate = new Date(Object.keys(data)[0]);
					const maxDate = new Date(
						Object.keys(data)[Object.keys(data).length - 1]
					);
					console.log(ymd(date), data[ymd(date)]);
					return (
						<Timeline
							dailyImages={Object.values(data)}
							minDate={minDate}
							maxDate={maxDate}
							onChange={(date: Date) => {
								setDate(date);
							}}
						/>
					);
				}}
			</Histogram>
		</div>
	);
}
