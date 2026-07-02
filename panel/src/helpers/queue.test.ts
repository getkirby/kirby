import { describe, expect, it } from "vitest";
import queue from "./queue";

describe("queue()", () => {
	it("should resolve with results for all tasks", async () => {
		const results = await queue([
			() => Promise.resolve(1),
			() => Promise.resolve(2),
			() => Promise.resolve(3)
		]);

		expect(results).toEqual([1, 2, 3]);
	});

	it("should resolve with an empty array for no tasks", async () => {
		const results = await queue([]);

		expect(results).toEqual([]);
	});

	it("should preserve the order of results regardless of completion order", async () => {
		const results = await queue([
			() => new Promise<number>((resolve) => setTimeout(() => resolve(1), 30)),
			() => new Promise<number>((resolve) => setTimeout(() => resolve(2), 20)),
			() => new Promise<number>((resolve) => setTimeout(() => resolve(3), 10))
		]);

		expect(results).toEqual([1, 2, 3]);
	});

	it("should not exceed the concurrency limit", async () => {
		let active = 0;
		let maxActive = 0;
		const task = () =>
			new Promise<void>((resolve) => {
				active++;
				maxActive = Math.max(maxActive, active);
				setTimeout(() => {
					active--;
					resolve();
				}, 10);
			});

		await queue(
			Array.from({ length: 6 }, () => task),
			2
		);

		expect(maxActive).toBeLessThanOrEqual(2);
	});

	it("should capture rejected tasks as results", async () => {
		const error = new Error("task failed");
		const results = await queue([
			() => Promise.resolve(1),
			() => Promise.reject(error),
			() => Promise.resolve(3)
		]);

		expect(results).toEqual([1, error, 3]);
	});
});
