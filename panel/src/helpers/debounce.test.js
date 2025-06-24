import { describe, expect, it, vi, beforeEach, afterEach } from "vitest";
import debounce from "./debounce.js";

describe.concurrent("$helper.debounce()", () => {
	beforeEach(() => {
		vi.useFakeTimers();
	});

	afterEach(() => {
		vi.useRealTimers();
	});

	it("should call the function after the delay", () => {
		const callback = vi.fn();
		const debounced = debounce(callback, 1000);

		debounced();
		expect(callback).not.toHaveBeenCalled();

		vi.advanceTimersByTime(500);
		expect(callback).not.toHaveBeenCalled();

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalled();
	});

	it("should call the function with the correct arguments", () => {
		const callback = vi.fn();
		const debounced = debounce(callback, 1000);

		debounced(1, 2, 3);
		vi.advanceTimersByTime(1000);

		expect(callback).toHaveBeenCalledWith(1, 2, 3);
	});

	it("should reset the timer when called again within the delay", () => {
		const callback = vi.fn();
		const debounced = debounce(callback, 1000);

		debounced();
		expect(callback).not.toHaveBeenCalled();

		vi.advanceTimersByTime(500);
		expect(callback).not.toHaveBeenCalled();

		debounced();
		expect(callback).not.toHaveBeenCalled();

		vi.advanceTimersByTime(500);
		expect(callback).not.toHaveBeenCalled();

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalled();
	});

	it("should call the function at the start when leading", () => {
		const callback = vi.fn();
		const debounced = debounce(callback, 1000, {
			leading: true,
			trailing: true
		});

		debounced();
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalledTimes(1);

		debounced();
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(500);
		expect(callback).toHaveBeenCalledTimes(2);
	});
});
