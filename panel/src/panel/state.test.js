/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import State from "./state.js";

describe.concurrent("state", () => {
	it("should set & get a key", async () => {
		const state = State("test");
		expect(state.key()).toStrictEqual("test");
	});

	it("should set & get defaults", async () => {
		const defaults = {
			message: null
		};

		const state = State("test", defaults);

		expect(state.defaults()).toStrictEqual(defaults);
		expect(state.state()).toStrictEqual(defaults);
		expect(state.message).toStrictEqual(null);
	});

	it("should set new state", async () => {
		const defaults = {
			message: null,
			isOpen: false
		};

		const state = State("test", defaults);

		state.set({ message: "Hello" });

		expect(state.state()).toStrictEqual({ message: "Hello", isOpen: false });
		expect(state.message).toStrictEqual("Hello");
		expect(state.isOpen).toStrictEqual(false);
	});

	it("should set default state", async () => {
		const defaults = {
			message: null
		};

		const state = State("test", defaults);

		state.set({ message: "Hello" });
		state.reset();

		expect(state.message).toStrictEqual(null);
	});

	it("should validate state", async () => {
		const state = State("test");

		// invalid state
		try {
			state.validateState("foo");
		} catch (error) {
			expect(error.message).toStrictEqual("Invalid test state");
		}

		// valid state
		const validation = state.validateState({ message: "Yay" });
		expect(validation).toStrictEqual(true);
	});
});
