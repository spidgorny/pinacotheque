import React, { useState } from "react";
import Timeline from "../widgets/timeline";
import moment from "moment";

export default function TimelineTest(props: any) {
	let minDate = new Date(2019, 1, 21);
	let maxDate = new Date();
	const [date, setDate] = useState(minDate);

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

	return (
		<div className="container mx-auto p-2" style={{ maxWidth: "100%" }}>
			<Timeline
				dailyImages={daily}
				minDate={minDate}
				maxDate={maxDate}
				onChange={(date: Date) => {
					setDate(date);
				}}
			/>
		</div>
	);
}
