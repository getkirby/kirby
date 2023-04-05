/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import Events from "./events.js";

describe.concurrent("panel.events.keydown", () => {
	const events = Events();

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
