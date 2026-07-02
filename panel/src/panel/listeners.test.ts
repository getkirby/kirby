import { describe, expect, it, vi } from "vitest";
import Listeners from "./listeners";

describe("panel.listeners", () => {
	describe("addEventListener()", () => {
		it("should register a listener", () => {
			const listeners = Listeners();
			const callback = vi.fn();
			listeners.addEventListener("submit", callback);
			expect(listeners.hasEventListener("submit")).toStrictEqual(true);
		});

		it("should ignore non-function callbacks", () => {
			const listeners = Listeners();
			listeners.addEventListener("submit", "not a function" as never);
			expect(listeners.hasEventListener("submit")).toStrictEqual(false);
		});

		it("should overwrite an existing listener", () => {
			const listeners = Listeners();
			const warn = vi.spyOn(console, "warn").mockImplementation(() => {});
			const first = vi.fn();
			const second = vi.fn();
			listeners.addEventListener("submit", first);
			listeners.addEventListener("submit", second);
			listeners.emit("submit");
			expect(first).not.toHaveBeenCalled();
			expect(second).toHaveBeenCalledOnce();

			expect(warn).toHaveBeenCalledWith(
				'Listener for "submit" already exists and will be overwritten'
			);
			warn.mockRestore();
		});
	});

	describe("addEventListeners()", () => {
		it("should register multiple listeners at once", () => {
			const listeners = Listeners();
			listeners.addEventListeners({
				submit: vi.fn(),
				cancel: vi.fn()
			});
			expect(listeners.hasEventListener("submit")).toStrictEqual(true);
			expect(listeners.hasEventListener("cancel")).toStrictEqual(true);
		});

		it("should ignore non-object input", () => {
			const listeners = Listeners();
			listeners.addEventListeners("invalid" as never);
			expect(listeners.listeners()).toStrictEqual({});
		});

		it("should ignore undefined input", () => {
			const listeners = Listeners();
			listeners.addEventListeners(undefined);
			expect(listeners.listeners()).toStrictEqual({});
		});

		it("should ignore null input", () => {
			const listeners = Listeners();
			listeners.addEventListeners(null as never);
			expect(listeners.listeners()).toStrictEqual({});
		});
	});

	describe("emit()", () => {
		it("should call the listener with the given args", () => {
			const listeners = Listeners();
			const callback = vi.fn();
			listeners.addEventListener("submit", callback);
			listeners.emit("submit", { foo: "bar" });
			expect(callback).toHaveBeenCalledWith({ foo: "bar" });
		});

		it("should return the listener's return value", () => {
			const listeners = Listeners();
			listeners.addEventListener("submit", () => "result");
			expect(listeners.emit("submit")).toStrictEqual("result");
		});

		it("should return undefined when no listener is registered", () => {
			const listeners = Listeners();
			expect(listeners.emit("unknown")).toStrictEqual(undefined);
		});

		it("should not call other listeners", () => {
			const listeners = Listeners();
			const submit = vi.fn();
			const cancel = vi.fn();
			listeners.addEventListener("submit", submit);
			listeners.addEventListener("cancel", cancel);
			listeners.emit("submit");
			expect(submit).toHaveBeenCalledOnce();
			expect(cancel).not.toHaveBeenCalled();
		});
	});

	describe("hasEventListener()", () => {
		it("should return true when listener exists", () => {
			const listeners = Listeners();
			listeners.addEventListener("submit", vi.fn());
			expect(listeners.hasEventListener("submit")).toStrictEqual(true);
		});

		it("should return false when listener does not exist", () => {
			const listeners = Listeners();
			expect(listeners.hasEventListener("submit")).toStrictEqual(false);
		});
	});

	describe("listeners()", () => {
		it("should return all registered listeners", () => {
			const listeners = Listeners();
			const submit = vi.fn();
			const cancel = vi.fn();
			listeners.addEventListener("submit", submit);
			listeners.addEventListener("cancel", cancel);
			expect(listeners.listeners()).toStrictEqual({ submit, cancel });
		});

		it("should return an empty object when no listeners are registered", () => {
			const listeners = Listeners();
			expect(listeners.listeners()).toStrictEqual({});
		});
	});

	describe("removeEventListener()", () => {
		it("should remove a single listener", () => {
			const listeners = Listeners();
			listeners.addEventListener("submit", vi.fn());
			listeners.removeEventListener("submit");
			expect(listeners.hasEventListener("submit")).toStrictEqual(false);
		});

		it("should not affect other listeners", () => {
			const listeners = Listeners();
			listeners.addEventListener("submit", vi.fn());
			listeners.addEventListener("cancel", vi.fn());
			listeners.removeEventListener("submit");
			expect(listeners.hasEventListener("submit")).toStrictEqual(false);
			expect(listeners.hasEventListener("cancel")).toStrictEqual(true);
		});

		it("should not throw when removing a non-existent listener", () => {
			const listeners = Listeners();
			expect(() => listeners.removeEventListener("unknown")).not.toThrow();
		});
	});

	describe("removeEventListeners()", () => {
		it("should remove all listeners", () => {
			const listeners = Listeners();
			listeners.addEventListener("submit", vi.fn());
			listeners.addEventListener("cancel", vi.fn());
			listeners.removeEventListeners();
			expect(listeners.listeners()).toStrictEqual({});
		});

		it("should not throw when no listeners are registered", () => {
			const listeners = Listeners();
			expect(() => listeners.removeEventListeners()).not.toThrow();
		});
	});
});
