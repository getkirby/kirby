/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import Module from "./module.js";

describe.concurrent("module", () => {
	it("should set & get a key", async () => {
		const mod = Module("$test");
		expect(mod.key()).toStrictEqual("$test");
	});

	it("should set & get defaults", async () => {
		const defaults = {
			message: null
		};

		const mod = Module("$test", defaults);

		expect(mod.defaults()).toStrictEqual(defaults);
		expect(mod.state()).toStrictEqual(defaults);
		expect(mod.message).toStrictEqual(null);
	});

	it("should set new state", async () => {
		const defaults = {
			message: null,
			isOpen: false
		};

		const mod = Module("$test", defaults);

		mod.set({ message: "Hello" });

		expect(mod.state()).toStrictEqual({ message: "Hello", isOpen: false });
		expect(mod.message).toStrictEqual("Hello");
		expect(mod.isOpen).toStrictEqual(false);
	});

	it("should set default state", async () => {
		const defaults = {
			message: null
		};

		const mod = Module("$test", defaults);

		mod.set({ message: "Hello" });
		mod.reset();

		expect(mod.message).toStrictEqual(null);
	});

	it("should validate state", async () => {
		const mod = Module("$test");

		// invalid state
		try {
			mod.validateState("foo");
		} catch (error) {
			expect(error.message).toStrictEqual("Invalid $test state");
		}

		// valid state
		const validation = mod.validateState({ message: "Yay" });
		expect(validation).toStrictEqual(true);
	});
});
