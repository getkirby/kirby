import { describe, expect, it, vi } from "vitest";
import Emitter from "./Emitter";

describe("Emitter", () => {
	describe("emit", () => {
		it("should call registered listener with arguments", () => {
			const emitter = new Emitter();
			const fn = vi.fn();

			emitter.on("test", fn);
			emitter.emit("test", 1, 2);

			expect(fn).toHaveBeenCalledWith(1, 2);
		});

		it("should call multiple listeners for the same event", () => {
			const emitter = new Emitter();
			const fn1 = vi.fn();
			const fn2 = vi.fn();

			emitter.on("test", fn1);
			emitter.on("test", fn2);
			emitter.emit("test");

			expect(fn1).toHaveBeenCalledOnce();
			expect(fn2).toHaveBeenCalledOnce();
		});

		it("should not throw when emitting an event with no listeners", () => {
			const emitter = new Emitter();
			expect(() => emitter.emit("test")).not.toThrow();
		});

		it("should return this for chaining", () => {
			const emitter = new Emitter();
			expect(emitter.emit("test")).toBe(emitter);
		});
	});

	describe("on", () => {
		it("should register a listener", () => {
			const emitter = new Emitter();
			const fn = vi.fn();

			emitter.on("test", fn);
			emitter.emit("test");

			expect(fn).toHaveBeenCalledOnce();
		});

		it("should return this for chaining", () => {
			const emitter = new Emitter();
			expect(emitter.on("test", vi.fn())).toBe(emitter);
		});
	});

	describe("off", () => {
		it("should remove a specific listener", () => {
			const emitter = new Emitter();
			const fn = vi.fn();

			emitter.on("test", fn);
			emitter.off("test", fn);
			emitter.emit("test");

			expect(fn).not.toHaveBeenCalled();
		});

		it("should only remove the given listener, not others", () => {
			const emitter = new Emitter();
			const fn1 = vi.fn();
			const fn2 = vi.fn();

			emitter.on("test", fn1);
			emitter.on("test", fn2);
			emitter.off("test", fn1);
			emitter.emit("test");

			expect(fn1).not.toHaveBeenCalled();
			expect(fn2).toHaveBeenCalledOnce();
		});

		it("should remove all listeners for an event when fn is omitted", () => {
			const emitter = new Emitter();
			const fn1 = vi.fn();
			const fn2 = vi.fn();

			emitter.on("test", fn1);
			emitter.on("test", fn2);
			emitter.off("test");
			emitter.emit("test");

			expect(fn1).not.toHaveBeenCalled();
			expect(fn2).not.toHaveBeenCalled();
		});

		it("should remove all listeners for all events when called without arguments", () => {
			const emitter = new Emitter();
			const fn1 = vi.fn();
			const fn2 = vi.fn();

			emitter.on("a", fn1);
			emitter.on("b", fn2);
			emitter.off();
			emitter.emit("a");
			emitter.emit("b");

			expect(fn1).not.toHaveBeenCalled();
			expect(fn2).not.toHaveBeenCalled();
		});

		it("should not throw when removing a listener from an event with no listeners", () => {
			const emitter = new Emitter();
			expect(() => emitter.off("test", vi.fn())).not.toThrow();
		});

		it("should return this for chaining", () => {
			const emitter = new Emitter();
			expect(emitter.off()).toBe(emitter);
		});
	});
});
