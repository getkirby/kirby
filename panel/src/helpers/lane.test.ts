import { describe, expect, it, vi } from "vitest";
import lane from "./lane";

/**
 * Creates a task that finishes on demand
 */
function deferred<T>() {
	let resolve!: (value: T) => void;
	let reject!: (reason: unknown) => void;

	const promise = new Promise<T>((res, rej) => {
		resolve = res;
		reject = rej;
	});

	return { reject, resolve, run: vi.fn(() => promise) };
}

describe("lane()", () => {
	it("runs the first task immediately", () => {
		const run = lane();
		const first = deferred();

		run("save", first.run);

		expect(first.run).toHaveBeenCalledOnce();
	});

	it("resolves with the result of the task", async () => {
		const run = lane();

		await expect(run("save", () => Promise.resolve("done"))).resolves.toBe(
			"done"
		);
	});

	it("runs a task after the running one, even with the same key", async () => {
		const run = lane();
		const first = deferred<string>();
		const second = deferred<string>();

		const a = run("save", first.run);
		const b = run("save", second.run);

		expect(second.run).not.toHaveBeenCalled();

		first.resolve("first");
		await expect(a).resolves.toBe("first");

		expect(second.run).toHaveBeenCalledOnce();

		second.resolve("second");
		await expect(b).resolves.toBe("second");
	});

	it("replaces a waiting task with the same key", async () => {
		const run = lane();
		const first = deferred<string>();
		const second = deferred<string>();
		const third = deferred<string>();

		const a = run("save", first.run);
		const b = run("save", second.run);
		const c = run("save", third.run);

		first.resolve("first");
		await a;

		expect(second.run).not.toHaveBeenCalled();
		expect(third.run).toHaveBeenCalledOnce();

		third.resolve("third");

		// the replaced task's callers receive the replacement's result
		await expect(b).resolves.toBe("third");
		await expect(c).resolves.toBe("third");
	});

	it("moves a replaced task behind the tasks added in the meantime", async () => {
		const run = lane();
		const first = deferred();
		const calls: string[] = [];

		const a = run("save", first.run);
		const b = run("save", async () => {
			calls.push("old save");
		});
		const c = run("publish", async () => {
			calls.push("publish");
		});
		const d = run("save", async () => {
			calls.push("new save");
		});

		first.resolve(undefined);
		await Promise.all([a, b, c, d]);

		expect(calls).toStrictEqual(["publish", "new save"]);
	});

	it("keeps the order of tasks with different keys", async () => {
		const run = lane();
		const first = deferred();
		const calls: string[] = [];

		const a = run("save", first.run);
		const b = run("publish", async () => {
			calls.push("publish");
		});
		const c = run("save", async () => {
			calls.push("save");
		});

		first.resolve(undefined);
		await Promise.all([a, b, c]);

		expect(calls).toStrictEqual(["publish", "save"]);
	});

	it("rejects the callers of a failed task and runs the next one", async () => {
		const run = lane();

		const a = run("save", () => Promise.reject(new Error("failed")));
		const b = run("publish", () => Promise.resolve("next"));

		await expect(a).rejects.toThrowError("failed");
		await expect(b).resolves.toBe("next");
	});

	it("starts again after the lane ran empty", async () => {
		const run = lane();
		const task = vi.fn(() => Promise.resolve());

		await run("save", () => Promise.resolve());
		run("save", task);

		expect(task).toHaveBeenCalledOnce();
	});
});
