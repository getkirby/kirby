import { describe, expect, it, beforeEach, afterEach, vi } from "vitest";
import Timer from "./timer";

describe("Timer", () => {
	beforeEach(() => {
		vi.useFakeTimers();
	});

	afterEach(() => {
		vi.useRealTimers();
	});

	describe("start", () => {
		it("should call callback repeatedly on interval", () => {
			const timer = new Timer();
			const callback = vi.fn();

			timer.start(100, callback);
			vi.advanceTimersByTime(350);

			expect(callback).toHaveBeenCalledTimes(3);
		});

		it("should replace a running timer on start", () => {
			const timer = new Timer();
			const first = vi.fn();
			const second = vi.fn();

			timer.start(100, first);
			vi.advanceTimersByTime(150);
			timer.start(100, second);
			vi.advanceTimersByTime(200);

			expect(first).toHaveBeenCalledTimes(1);
			expect(second).toHaveBeenCalledTimes(2);
		});

		it("should not start with a zero timeout", () => {
			const timer = new Timer();
			const callback = vi.fn();

			timer.start(0, callback);
			vi.advanceTimersByTime(500);

			expect(callback).not.toHaveBeenCalled();
		});
	});

	describe("stop", () => {
		it("should stop a running timer", () => {
			const timer = new Timer();
			const callback = vi.fn();

			timer.start(100, callback);
			vi.advanceTimersByTime(250);
			timer.stop();
			vi.advanceTimersByTime(300);

			expect(callback).toHaveBeenCalledTimes(2);
		});

		it("should not throw when called without a running timer", () => {
			const timer = new Timer();

			expect(() => timer.stop()).not.toThrow();
		});
	});

	describe("isRunning", () => {
		it("should be false initially", () => {
			const timer = new Timer();

			expect(timer.isRunning).toStrictEqual(false);
		});

		it("should be true after start", () => {
			const timer = new Timer();

			timer.start(100, vi.fn());

			expect(timer.isRunning).toStrictEqual(true);
		});

		it("should be false after stop", () => {
			const timer = new Timer();

			timer.start(100, vi.fn());
			timer.stop();

			expect(timer.isRunning).toStrictEqual(false);
		});

		it("should be false after start with invalid timeout", () => {
			const timer = new Timer();

			timer.start(0, vi.fn());

			expect(timer.isRunning).toStrictEqual(false);
		});
	});
});
