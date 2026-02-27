import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import throttle from "./throttle";

describe.concurrent("$helper.throttle()", () => {
	beforeEach(() => {
		vi.useFakeTimers();
	});

	afterEach(() => {
		vi.useRealTimers();
	});

	it("should call the function immediately by default (leading)", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000);

		throttled();
		expect(callback).toHaveBeenCalledTimes(1);
	});

	it("should call the function with the correct arguments", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000);

		throttled(1, 2, 3);
		expect(callback).toHaveBeenCalledWith(1, 2, 3);
	});

	it("should ignore calls during the cooldown period", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000);

		throttled();
		throttled();
		throttled();

		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(1000);
		expect(callback).toHaveBeenCalledTimes(1);
	});

	it("should allow calls again after the delay", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000);

		throttled();
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(1000);

		throttled();
		expect(callback).toHaveBeenCalledTimes(2);
	});

	it("should fire the trailing call with the last arguments", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000, {
			leading: true,
			trailing: true
		});

		throttled("first");
		throttled("second");
		throttled("third");

		expect(callback).toHaveBeenCalledTimes(1);
		expect(callback).toHaveBeenCalledWith("first");

		vi.advanceTimersByTime(1000);
		expect(callback).toHaveBeenCalledTimes(2);
		expect(callback).toHaveBeenLastCalledWith("third");
	});

	it("should only fire trailing call when leading is false", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000, {
			leading: false,
			trailing: true
		});

		throttled("first");
		expect(callback).not.toHaveBeenCalled();

		vi.advanceTimersByTime(1000);
		expect(callback).toHaveBeenCalledTimes(1);
		expect(callback).toHaveBeenCalledWith("first");
	});

	it("should cancel the pending trailing call", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000, {
			leading: true,
			trailing: true
		});

		throttled("first");
		throttled("second");

		throttled.cancel();

		vi.advanceTimersByTime(1000);
		expect(callback).toHaveBeenCalledTimes(1);
		expect(callback).toHaveBeenCalledWith("first");
	});

	it("should handle multiple trailing bursts correctly", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000, {
			leading: true,
			trailing: true
		});

		// First burst
		throttled("first");
		throttled("second");
		throttled("third");

		expect(callback).toHaveBeenCalledTimes(1);
		expect(callback).toHaveBeenLastCalledWith("first");

		// Trailing fires, restarts timer
		vi.advanceTimersByTime(1000);
		expect(callback).toHaveBeenCalledTimes(2);
		expect(callback).toHaveBeenLastCalledWith("third");

		// Second burst during the restarted timer
		throttled("fourth");
		throttled("fifth");

		vi.advanceTimersByTime(1000);
		expect(callback).toHaveBeenCalledTimes(3);
		expect(callback).toHaveBeenLastCalledWith("fifth");

		// No more pending calls
		vi.advanceTimersByTime(1000);
		expect(callback).toHaveBeenCalledTimes(3);
	});

	it("should never call the function when both leading and trailing are false", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000, {
			leading: false,
			trailing: false
		});

		throttled();
		throttled();
		vi.advanceTimersByTime(1000);

		expect(callback).not.toHaveBeenCalled();
	});

	it("should not throw when cancel is called with no active timer", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000);

		expect(() => throttled.cancel()).not.toThrow();
	});

	it("should cancel a pending leading=false trailing call before it fires", () => {
		const callback = vi.fn();
		const throttled = throttle(callback, 1000, {
			leading: false,
			trailing: true
		});

		throttled("first");
		expect(callback).not.toHaveBeenCalled();

		throttled.cancel();

		vi.advanceTimersByTime(1000);
		expect(callback).not.toHaveBeenCalled();
	});
});
