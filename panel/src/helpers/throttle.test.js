import { describe, expect, it, vi, beforeEach, afterEach } from "vitest";
import throttle from "./throttle.js";

describe.concurrent("$helper.throttle()", () => {
	beforeEach(() => {
		vi.useFakeTimers();
	});

	afterEach(() => {
		vi.useRealTimers();
	});

	it("should call the function only once within the delay", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000);

		throttled();
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalledTimes(1);
	});

	it("should call the function with the correct arguments", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000);

		throttled(1, 2, 3);
		vi.advanceTimersByTime(1000);

		expect(callback).toHaveBeenCalledWith(1, 2, 3);
	});

	it("should reset the timer when called again after the delay", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000);

		throttled();
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalledTimes(1);

		throttled();
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalledTimes(1);

		throttled();
		expect(callback).toHaveBeenCalledTimes(2);
	});

	it("should call the function after the end when trailing", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000, {
			leading: true,
			trailing: true
		});

		throttled();
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalledTimes(1);

		throttled();
		throttled();
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalledTimes(2);
	});
});
