import { describe, expect, it } from "vitest";
import State from "./state";

describe("panel.state", () => {
	describe("defaults()", () => {
		it("returns the defaults object", () => {
			const defaults = { message: "hello world" };
			const state = State("test", defaults);
			expect(state.defaults()).toStrictEqual(defaults);
		});

		it("returns empty object when no defaults given", () => {
			const state = State("test");
			expect(state.defaults()).toStrictEqual({});
		});
	});

	describe("key()", () => {
		it("returns the key", () => {
			const state = State("test");
			expect(state.key()).toStrictEqual("test");
		});
	});

	describe("reset()", () => {
		it("restores all default values", () => {
			const defaults = {
				message: null as string | null,
				isOpen: false
			};

			const state = State("test", defaults);

			state.set({ message: "Hello", isOpen: true });
			state.reset();

			expect(state.state()).toStrictEqual(defaults);
		});
	});

	describe("set()", () => {
		it("applies state, uses defaults for missing keys", () => {
			const state = State("test", {
				message: null as string | null,
				isOpen: false
			});

			state.set({ message: "Hello" });

			expect(state.state()).toStrictEqual({ message: "Hello", isOpen: false });
			expect(state.message).toStrictEqual("Hello");
			expect(state.isOpen).toStrictEqual(false);
		});

		it("falls back to default when value is null", () => {
			const state = State("test", { message: "default" as string | null });

			state.set({ message: "Hello" });
			state.set({ message: null });

			expect(state.message).toStrictEqual("default");
		});

		it("throws when state is not a plain object", () => {
			const state = State("test");
			expect(() => state.set("foo" as never)).toThrow("Invalid test state");
			expect(() => state.set(42 as never)).toThrow("Invalid test state");
			expect(() => state.set(null as never)).toThrow("Invalid test state");
		});
	});

	describe("state()", () => {
		it("returns current values for all default keys", () => {
			const defaults = { message: "foo" };
			const state = State("test", defaults);
			expect(state.state()).toStrictEqual({ message: "foo" });
		});

		it("excludes keys not defined in defaults", () => {
			const state = State("test", { message: "foo" });
			state.set({ message: "bar", extra: "ignored" } as never);
			const result = state.state();
			expect(Object.keys(result)).toStrictEqual(["message"]);
		});
	});
});
