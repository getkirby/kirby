/**
 * @vitest-environment jsdom
 */

import { describe, expect, it } from "vitest";
import User from "./user.js";

describe.concurrent("panel.user", () => {
	it("should have a default state", async () => {
		const user = User();

		const state = {
			email: null,
			id: null,
			language: null,
			role: null,
			username: null
		};

		expect(user.key()).toStrictEqual("user");
		expect(user.state()).toStrictEqual(state);
	});

	it("should set and reset", async () => {
		const user = User();

		const state = {
			...user.defaults(),
			id: "abc",
			username: "Kirby"
		};

		user.set({
			id: "abc",
			username: "Kirby"
		});

		expect(user.state()).toStrictEqual(state);

		user.reset();

		expect(user.state()).toStrictEqual(user.defaults());
	});
});
