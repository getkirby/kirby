/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import Events from "./events.js";
import Panel from "./panel.js";

describe.concurrent("panel.events.keydown", () => {
	const panel = Panel.create(app);
	const events = Events(panel);

	it("should fire keydown event with modifiers", async () => {
		let fired = false;

		events.on("keydown.cmd.s", () => {
			fired = true;
		});

		events.keydown({
			metaKey: true,
			key: "s"
		});

		expect(fired).toStrictEqual(true);
	});
});
