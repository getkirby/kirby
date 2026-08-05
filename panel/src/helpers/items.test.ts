import { afterEach, describe, expect, it, vi } from "vitest";
import items from "./items";

function mockPanel(
	responder: (query: Record<string, unknown>) => Record<string, unknown>
) {
	const get = vi.fn(
		async (_endpoint: string, options: { query: Record<string, unknown> }) =>
			responder(options.query)
	);

	window.panel = { get, error: vi.fn() } as unknown as typeof window.panel;

	return get;
}

function echo(query: Record<string, unknown>) {
	const ids = String(query.items).split(",");
	return { items: ids.map((id) => (id === "missing" ? null : { id })) };
}

afterEach(() => {
	window.panel = undefined as unknown as typeof window.panel;
});

describe("$helper.items()", () => {
	it("should collect calls from the same tick into one request", async () => {
		const get = mockPanel(echo);

		const results = await Promise.all([
			items("items/files", "a"),
			items("items/files", "b"),
			items("items/files", "c")
		]);

		expect(get).toHaveBeenCalledTimes(1);
		expect(get.mock.calls[0][1].query.items).toBe("a,b,c");
		expect(results).toStrictEqual([{ id: "a" }, { id: "b" }, { id: "c" }]);
	});

	it("should keep calls with different options apart", async () => {
		const get = mockPanel(echo);

		await Promise.all([
			items("items/files", "a", { layout: "cards" }),
			items("items/files", "b", { layout: "list" }),
			items("items/files", "c", { layout: "cards" })
		]);

		expect(get).toHaveBeenCalledTimes(2);

		const batched = get.mock.calls.map((call) => call[1].query.items).sort();
		expect(batched).toStrictEqual(["a,c", "b"]);
	});

	it("should keep calls for different endpoints apart", async () => {
		const get = mockPanel(echo);

		await Promise.all([items("items/files", "a"), items("items/pages", "b")]);

		expect(get).toHaveBeenCalledTimes(2);
	});

	it("should start a new queue after the previous request resolved", async () => {
		const get = mockPanel(echo);

		await items("items/files", "a");
		await items("items/files", "b");

		expect(get).toHaveBeenCalledTimes(2);
	});

	it("should send a new request for an id queued while one is in flight", async () => {
		const get = mockPanel(echo);

		const first = items("items/files", "a");

		// let the queue send, so that the request is on its way
		await Promise.resolve();

		const second = items("items/files", "b");

		expect(await first).toStrictEqual({ id: "a" });
		expect(await second).toStrictEqual({ id: "b" });
		expect(get).toHaveBeenCalledTimes(2);
		expect(get.mock.calls[0][1].query.items).toBe("a");
		expect(get.mock.calls[1][1].query.items).toBe("b");
	});

	it("should pass the query along to the endpoint", async () => {
		const get = mockPanel(echo);

		await items("items/files", "a", { layout: "auto", image: "{}" });

		expect(get.mock.calls[0][1].query).toStrictEqual({
			layout: "auto",
			image: "{}",
			items: "a"
		});
	});

	it("should resolve to undefined for an unknown id", async () => {
		mockPanel(echo);

		const [found, missing] = await Promise.all([
			items("items/files", "a"),
			items("items/files", "missing")
		]);

		expect(found).toStrictEqual({ id: "a" });
		expect(missing).toBeUndefined();
	});

	it("should resolve every id of a failed batch to undefined", async () => {
		mockPanel(() => {
			throw new Error("nope");
		});

		const results = await Promise.all([
			items("items/files", "a"),
			items("items/files", "b")
		]);

		expect(results).toStrictEqual([undefined, undefined]);
	});

	it("should hand the error of a failed batch to the Panel", async () => {
		const error = new Error("nope");

		mockPanel(() => {
			throw error;
		});

		await items("items/files", "a");

		expect(window.panel.error).toHaveBeenCalledWith(error);
	});

	it("should resolve a list of ids in order", async () => {
		const get = mockPanel(echo);

		const results = await items("items/files", ["a", "missing", "c"]);

		expect(get).toHaveBeenCalledTimes(1);
		expect(get.mock.calls[0][1].query.items).toBe("a,missing,c");
		expect(results).toStrictEqual([{ id: "a" }, undefined, { id: "c" }]);
	});

	it("should not request anything for an empty list", async () => {
		const get = mockPanel(echo);

		expect(await items("items/files", [])).toStrictEqual([]);
		expect(get).not.toHaveBeenCalled();
	});

	it("should never send a blank id, as it would shift the response", async () => {
		const get = mockPanel(echo);

		const results = await items("items/files", ["a", "", "  ", "b"]);

		expect(get.mock.calls[0][1].query.items).toBe("a,b");
		expect(results).toStrictEqual([
			{ id: "a" },
			undefined,
			undefined,
			{ id: "b" }
		]);
	});

	it("should only send an id once, no matter how many callers wait", async () => {
		const get = mockPanel(echo);

		const results = await Promise.all([
			items("items/files", "a"),
			items("items/files", ["a", "b"]),
			items("items/files", "b")
		]);

		expect(get).toHaveBeenCalledTimes(1);
		expect(get.mock.calls[0][1].query.items).toBe("a,b");
		expect(results).toStrictEqual([
			{ id: "a" },
			[{ id: "a" }, { id: "b" }],
			{ id: "b" }
		]);
	});

	it("should split a batch that exceeds the request limit", async () => {
		const get = mockPanel(echo);

		const ids = Array.from({ length: 250 }, (_, index) => "id-" + index);
		const results = await items("items/files", ids);

		expect(get).toHaveBeenCalledTimes(3);

		const chunks = get.mock.calls.map(
			(call) => String(call[1].query.items).split(",").length
		);
		expect(chunks).toStrictEqual([100, 100, 50]);
		expect(results).toStrictEqual(ids.map((id) => ({ id })));
	});

	it("should share a batch when the query only differs in key order", async () => {
		const get = mockPanel(echo);

		const results = await Promise.all([
			items("items/files", "a", { image: "{}", layout: "cards" }),
			items("items/files", "b", { layout: "cards", image: "{}" })
		]);

		expect(get).toHaveBeenCalledTimes(1);
		expect(results).toStrictEqual([{ id: "a" }, { id: "b" }]);
	});

	it("should join a call that is already in flight", async () => {
		const { promise, resolve } = Promise.withResolvers<unknown>();
		const get = vi.fn(async () => await promise);

		window.panel = { get } as unknown as typeof window.panel;

		const first = items("items/files", "a");

		// let the queue send, so that the request is on its way
		await Promise.resolve();

		const second = items("items/files", "a");

		resolve({ items: [{ id: "a" }] });

		expect(await first).toStrictEqual({ id: "a" });
		expect(await second).toStrictEqual({ id: "a" });
		expect(get).toHaveBeenCalledTimes(1);
	});

	it("should not keep the item once the request resolved", async () => {
		const get = mockPanel(echo);

		await items("items/files", "a");
		await items("items/files", "a");

		expect(get).toHaveBeenCalledTimes(2);
	});

	it("should only resolve the ids of a failed chunk to undefined", async () => {
		const get = mockPanel((query) => {
			const ids = String(query.items).split(",");

			if (ids.includes("id-100") === true) {
				throw new Error("nope");
			}

			return echo(query);
		});

		const ids = Array.from({ length: 150 }, (_, index) => "id-" + index);
		const results = await items("items/files", ids);

		expect(get).toHaveBeenCalledTimes(2);
		expect(results[99]).toStrictEqual({ id: "id-99" });
		expect(results[100]).toBeUndefined();
	});
});
