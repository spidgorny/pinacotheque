import React from "react";
import { Link } from "wouter";

export function Header(props: {}) {
	return (
		<div
			className="flex flex-row"
			style={{
				background: "#33C3F0",
				color: "white",
				padding: "0.5em",
			}}
		>
			<div className="px-2">
				<Link href="/" style={{ color: "white" }}>
					stream
				</Link>
			</div>
			<div className="px-2">
				<Link href="/folders" style={{ color: "white" }}>
					folders
				</Link>
			</div>
			<div className="px-2">
				<Link href="/browse" style={{ color: "white" }}>
					browse
				</Link>
			</div>
			<div className="px-2">
				<Link href="/visibility" style={{ color: "white" }}>
					visibility
				</Link>
			</div>
			<div className="px-2">
				<Link href="/one-image" style={{ color: "white" }}>
					one-image
				</Link>
			</div>{" "}
			<div className="px-2">
				<Link href="/timeline-test" style={{ color: "white" }}>
					timeline-test
				</Link>
			</div>
		</div>
	);
}
