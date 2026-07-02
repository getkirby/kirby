import { describe, expect, it, vi, beforeEach, afterEach } from "vitest";
import debounce from "./debounce";

describe("$helper.debounce()", () => {
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

	it("should only fire leading and not trail when trailing is false", () => {
		const callback = vi.fn();
		const debounced = debounce(callback, 1000, {
			leading: true,
			trailing: false
		});

		debounced("first");
		expect(callback).toHaveBeenCalledTimes(1);
		expect(callback).toHaveBeenCalledWith("first");

		debounced("second");
		debounced("third");

		vi.advanceTimersByTime(1000);
		expect(callback).toHaveBeenCalledTimes(1);
	});

	it("should fire leading again after the delay has passed", () => {
		const callback = vi.fn();
		const debounced = debounce(callback, 1000, {
			leading: true,
			trailing: false
		});

		debounced("first");
		expect(callback).toHaveBeenCalledTimes(1);

		vi.advanceTimersByTime(1000);

		debounced("second");
		expect(callback).toHaveBeenCalledTimes(2);
		expect(callback).toHaveBeenLastCalledWith("second");
	});

	it("should never call the function when both leading and trailing are false", () => {
		const callback = vi.fn();
		const debounced = debounce(callback, 1000, {
			leading: false,
			trailing: false
		});

		debounced();
		debounced();
		vi.advanceTimersByTime(1000);

		expect(callback).not.toHaveBeenCalled();
	});

	it("should fire trailing with the last arguments when called multiple times", () => {
		const callback = vi.fn();
		const debounced = debounce(callback, 1000);

		debounced("first");
		debounced("second");
		debounced("third");

		vi.advanceTimersByTime(1000);
		expect(callback).toHaveBeenCalledTimes(1);
		expect(callback).toHaveBeenCalledWith("third");
	});
});
