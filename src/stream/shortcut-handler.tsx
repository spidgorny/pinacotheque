import React, { useCallback, useEffect } from "react";

export function ShortcutHandler(props: {
	keyToPress: string;
	handler: () => void;
}) {
	const escFunction = useCallback(
		(event: KeyboardEvent) => {
			let eventKey = event.key?.toLowerCase();
			let propsKey = props.keyToPress?.toLowerCase();
			if (eventKey === propsKey && event.altKey) {
				console.log("Alt-" + props.keyToPress);
				props.handler();
			}
		},
		[props.keyToPress, props.handler]
	);

	useEffect(() => {
		// console.log("keydown init", props.keyToPress);
		document.addEventListener("keydown", escFunction, false);
		return () => {
			// console.log("keydown remove", props.keyToPress);
			document.removeEventListener("keydown", escFunction, false);
		};
	}, [props.keyToPress, props.handler, escFunction]);

	return <></>;
}
