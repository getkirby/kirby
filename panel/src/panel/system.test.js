/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import System from "./system";

describe.concurrent("panel.system", () => {
	it("should have a default state", async () => {
		const system = System();

		const state = {
			ascii: {},
			csrf: null,
			isLocal: null,
			locales: {},
			slugs: [],
			title: null
		};

		expect(system.key()).toStrictEqual("system");
		expect(system.state()).toStrictEqual(state);
	});

	it("should set and reset", async () => {
		const system = System();

		const state = {
			...system.defaults(),
			csrf: "dev",
			title: "Kirby"
		};

		system.set({
			csrf: "dev",
			title: "Kirby"
		});

		expect(system.state()).toStrictEqual(state);

		system.reset();

		expect(system.state()).toStrictEqual(system.defaults());
	});
});
