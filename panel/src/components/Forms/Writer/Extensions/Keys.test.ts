import { describe, expect, it, vi } from "vitest";
import Keys from "./Keys";

const keys = new Keys();

describe("Keys", () => {
	describe("keys", () => {
		it("returns an empty object when no options are provided", () => {
			expect(keys.keys()).toStrictEqual({});
		});

		it("creates a key entry for each option", () => {
			const ext = new Keys({ "Mod-k": vi.fn(), "Mod-j": vi.fn() });
			const keys = ext.keys();
			expect(Object.keys(keys)).toStrictEqual(["Mod-k", "Mod-j"]);
		});

		it("calls the corresponding option callback when a key handler is invoked", () => {
			const callback = vi.fn();
			const ext = new Keys({ "Mod-k": callback });
			ext.keys()["Mod-k"]();
			expect(callback).toHaveBeenCalledOnce();
		});

		it("returns true from each key handler to suppress the default event", () => {
			const ext = new Keys({ "Mod-k": vi.fn() });
			expect(ext.keys()["Mod-k"]()).toBe(true);
		});

		it("returns false from a key handler that returns false to pass the key on", () => {
			const ext = new Keys({ "Mod-k": () => false });
			expect(ext.keys()["Mod-k"]()).toBe(false);
		});
	});

	describe("name", () => {
		it("returns 'keys'", () => {
			expect(keys.name).toBe("keys");
		});
	});

	describe("type", () => {
		it("returns 'extension'", () => {
			expect(keys.type).toBe("extension");
		});
	});
});
