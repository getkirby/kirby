/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import Events from "./events.js";

describe.concurrent("panel.events drag & drop", () => {
	const events = Events();

	it("should keep track of target", async () => {
		let fired = false;

		const eventA = {
			target: "targetA",
			preventDefault: () => {},
			stopPropagation: () => {}
		};

		const eventB = {
			...eventA,
			target: "targetB"
		};

		events.on("dragleave", () => {
			fired = true;
		});

		events.dragenter(eventA);
		expect(events.entered).toStrictEqual(eventA.target);

		// should not fire because it's a different target
		events.dragleave(eventB);
		expect(fired).toStrictEqual(false);

		// should fire because it's the same target
		events.dragleave(eventA);
		expect(fired).toStrictEqual(true);
	});
});
