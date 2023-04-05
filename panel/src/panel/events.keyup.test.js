/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import Events from "./events.js";

describe.concurrent("panel.events.keyup", () => {
	const events = Events();

	it("should fire keyup event with modifiers", async () => {
		let fired = false;

		events.on("keyup.cmd.s", () => {
			fired = true;
		});

		events.keyup({
			metaKey: true,
			key: "s"
		});

		expect(fired).toStrictEqual(true);
	});
});
