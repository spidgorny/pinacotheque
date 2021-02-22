import React, { useCallback, useEffect } from "react";

export function ShortcutHandler(props: {
	keyToPress: string;
	handler: () => void;
	modifier?: "altKey" | "ctrlKey" | "metaKey" | "shiftKey" | "none";
}) {
	let modifier = props.modifier;
	if (!modifier) {
		modifier = "altKey";
	}
	const escFunction = useCallback(
		(event: KeyboardEvent) => {
			let eventKey = event.key;
			let propsKey = props.keyToPress;
			let modifierMatch = modifier === "altKey" && event.altKey;
			modifierMatch ||= modifier === "ctrlKey" && event.ctrlKey;
			modifierMatch ||= modifier === "shiftKey" && event.shiftKey;
			modifierMatch ||= modifier === "metaKey" && event.metaKey;
			modifierMatch ||=
				modifier === "none" &&
				!event.altKey &&
				!event.shiftKey &&
				!event.ctrlKey &&
				!event.metaKey;
			0 &&
				console.log(
					event.key,
					props.keyToPress,
					modifier,
					event.altKey,
					event.ctrlKey
				);
			if (eventKey === propsKey && modifierMatch) {
				console.log(modifier + "-" + props.keyToPress);
				props.handler();
			}
		},
		[props.keyToPress, props.handler, modifier]
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
