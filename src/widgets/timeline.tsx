import React, { useState } from "react";
// @ts-ignore
import { Sparklines } from "react-sparklines";
import ClickableSparklineBars from "./clickable-sparkline-bars";
import { ymd } from "../functions";
import moment from "moment";

export default function Timeline(props: {
	dailyImages: number[];
	minDate: Date;
	maxDate: Date;
	onChange: (date: Date) => void;
}) {
	if (!Object.keys(props).length) {
		props = {
			...props,
		};
	}
	const [date, setDateState] = useState(props.minDate);

	const setDate = (copy: Date) => {
		if (copy.getTime() > props.maxDate.getTime()) {
			copy = new Date(props.maxDate);
		}
		setDateState(copy);
		props.onChange(copy);
	};

	const changeYear = (year: number) => {
		let copy = new Date(date);
		copy?.setFullYear(year);
		setDate(copy);
	};

	const changeMonth = (month: number) => {
		let copy = new Date(date);
		copy?.setMonth(month - 1);
		setDate(copy);
	};

	const changeDay = (day: number) => {
		let copy = new Date(date);
		copy?.setDate(day);
		setDate(copy);
	};

	const setDayOfYear = (day: number) => {
		const move = moment(date).dayOfYear(day);
		setDate(move.toDate());
	};

	console.log(date);
	const minMonth =
		date.getFullYear() === props.minDate.getFullYear()
			? props.minDate.getMonth() + 1
			: 1;
	const maxMonth =
		date.getFullYear() === props.maxDate.getFullYear()
			? props.maxDate.getMonth() + 1
			: 12;
	const minDay =
		date.getFullYear() === props.minDate.getFullYear() &&
		date.getMonth() === props.minDate.getMonth()
			? props.minDate.getDate()
			: 1;
	const maxDay =
		date.getFullYear() === props.maxDate.getFullYear() &&
		date.getMonth() === props.maxDate.getMonth()
			? props.maxDate.getDate()
			: moment(new Date(date.getFullYear(), date.getMonth())).daysInMonth();

	const mom = moment(date);

	return (
		<div>
			<div className="year">
				<TimelineFor
					min={props.minDate.getFullYear()}
					value={date.getFullYear()}
					max={props.maxDate?.getFullYear()}
					onChange={changeYear}
				/>
			</div>
			<div className="month">
				<TimelineFor
					min={minMonth}
					value={date.getMonth() + 1}
					max={maxMonth}
					onChange={changeMonth}
				/>
			</div>
			<div className="day">
				<TimelineFor
					min={minDay}
					value={date.getDate()}
					max={maxDay}
					onChange={changeDay}
				/>
			</div>
			<Sparklines data={props.dailyImages}>
				<ClickableSparklineBars
					height={100}
					style={{ fill: "#41c3f9" }}
					calcStyle={(i) => ({
						fill: i === mom.dayOfYear() ? "#a1d300" : "#41c3f9",
					})}
					onClick={(m, i) => setDayOfYear(i)}
					margin={0}
				/>
			</Sparklines>
			<div className="flex flex-row justify-between">
				<div>{ymd(props.minDate)}</div>
				<div>{ymd(date)}</div>
				<div>{ymd(props.maxDate)}</div>
			</div>
		</div>
	);
}

function TimelineFor(props: {
	min: number;
	value: number;
	max: number;
	onChange: (i: number) => void;
}) {
	return (
		<div>
			<div className="flex flex-row w-full justify-between">
				<span>{props.min}</span>
				<span>{props.value}</span>
				<span>{props.max}</span>
			</div>
			<input
				type="range"
				style={{ width: "100%" }}
				min={props.min}
				value={props.value}
				max={props.max}
				onChange={(el) => {
					const year = (el.target as HTMLInputElement).value;
					// console.log(year);
					props.onChange(parseInt(year, 10));
				}}
			/>
		</div>
	);
}
