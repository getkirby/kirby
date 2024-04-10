/**
 * @vitest-environment node
 */

import { describe, expect, it } from "vitest";
import History from "./history.js";

describe.concurrent("panel.drawer.history", () => {
	it("should add and get items", async () => {
		const history = History();

		// empty
		expect(history.get()).toStrictEqual([]);

		history.add({
			id: "test"
		});

		expect(history.get()).toStrictEqual([{ id: "test" }]);
		expect(history.get("test")).toStrictEqual({ id: "test" });
	});

	it("should add and remove items", async () => {
		const history = History();

		history.add({
			id: "a"
		});

		history.add({
			id: "b"
		});

		expect(history.has("a")).toStrictEqual(true);
		expect(history.has("b")).toStrictEqual(true);
		expect(history.remove("a")).toStrictEqual([{ id: "b" }]);
		expect(history.has("a")).toStrictEqual(false);
		expect(history.has("b")).toStrictEqual(true);
		expect(history.remove("b")).toStrictEqual([]);
		expect(history.has("a")).toStrictEqual(false);
		expect(history.has("b")).toStrictEqual(false);
		expect(history.get()).toStrictEqual([]);
	});

	it("should remove last", async () => {
		const history = History();

		history.add({
			id: "a"
		});

		history.add({
			id: "b"
		});

		expect(history.removeLast()).toStrictEqual([{ id: "a" }]);
		expect(history.has("b")).toStrictEqual(false);
	});

	it("should clear items", async () => {
		const history = History();

		history.add({
			id: "a"
		});

		history.add({
			id: "b"
		});

		expect(history.get()).toStrictEqual([{ id: "a" }, { id: "b" }]);

		history.clear();

		expect(history.get()).toStrictEqual([]);
	});

	it("should go to milestone", async () => {
		const history = History();

		history.add({
			id: "a"
		});

		history.add({
			id: "b"
		});

		history.add({
			id: "c"
		});

		const b = history.goto("b");

		expect(b).toStrictEqual({ id: "b" });
		expect(history.get()).toStrictEqual([{ id: "a" }, { id: "b" }]);

		const a = history.goto("a");

		expect(a).toStrictEqual({ id: "a" });
		expect(history.get()).toStrictEqual([{ id: "a" }]);

		const c = history.goto("c");
		expect(c).toStrictEqual(undefined);
	});
});
