import { describe, expect, it } from "vitest";
import History from "./history";

describe("History", () => {
	describe("add()", () => {
		it("should add items", () => {
			const history = new History();

			history.add({ id: "a" });
			history.add({ id: "b" });

			expect(history.get()).toStrictEqual([{ id: "a" }, { id: "b" }]);
		});

		it("should skip duplicate ids", () => {
			const history = new History();

			history.add({ id: "a" });
			history.add({ id: "a" });

			expect(history.get()).toStrictEqual([{ id: "a" }]);
		});

		it("should replace the last item", () => {
			const history = new History();

			history.add({ id: "a" });
			history.add({ id: "b" });
			history.add({ id: "c" }, true);

			expect(history.get()).toStrictEqual([{ id: "a" }, { id: "c" }]);
		});

		it("should throw when adding an item without id", () => {
			const history = new History();

			expect(() => history.add({ id: "" })).toThrow("The state needs an ID");
		});
	});

	describe("at()", () => {
		it("should return the item at the given index", () => {
			const history = new History();

			history.add({ id: "a" });
			history.add({ id: "b" });

			expect(history.at(0)).toStrictEqual({ id: "a" });
			expect(history.at(1)).toStrictEqual({ id: "b" });
			expect(history.at(-1)).toStrictEqual({ id: "b" });
		});

		it("should return undefined for out-of-bounds index", () => {
			const history = new History();

			expect(history.at(0)).toStrictEqual(undefined);
		});
	});

	describe("clear()", () => {
		it("should clear all items", () => {
			const history = new History();

			history.add({ id: "a" });
			history.add({ id: "b" });
			history.clear();

			expect(history.get()).toStrictEqual([]);
		});
	});

	describe("get()", () => {
		it("should return all items when called without id", () => {
			const history = new History();

			expect(history.get()).toStrictEqual([]);

			history.add({ id: "a" });

			expect(history.get()).toStrictEqual([{ id: "a" }]);
		});

		it("should return a single item by id", () => {
			const history = new History();

			history.add({ id: "a" });

			expect(history.get("a")).toStrictEqual({ id: "a" });
			expect(history.get("b")).toStrictEqual(undefined);
		});
	});

	describe("goto()", () => {
		it("should go to a milestone and truncate history", () => {
			const history = new History();

			history.add({ id: "a" });
			history.add({ id: "b" });
			history.add({ id: "c" });

			expect(history.goto("b")).toStrictEqual({ id: "b" });
			expect(history.get()).toStrictEqual([{ id: "a" }, { id: "b" }]);

			expect(history.goto("a")).toStrictEqual({ id: "a" });
			expect(history.get()).toStrictEqual([{ id: "a" }]);
		});

		it("should return undefined for unknown id", () => {
			const history = new History();

			history.add({ id: "a" });

			expect(history.goto("b")).toStrictEqual(undefined);
		});
	});

	describe("has()", () => {
		it("should return true for existing items", () => {
			const history = new History();

			history.add({ id: "a" });

			expect(history.has("a")).toStrictEqual(true);
			expect(history.has("b")).toStrictEqual(false);
		});
	});

	describe("hasPrevious()", () => {
		it("should return true when there is more than one item", () => {
			const history = new History();

			expect(history.hasPrevious()).toStrictEqual(false);

			history.add({ id: "a" });

			expect(history.hasPrevious()).toStrictEqual(false);

			history.add({ id: "b" });

			expect(history.hasPrevious()).toStrictEqual(true);
		});
	});

	describe("isEmpty()", () => {
		it("should return true when empty", () => {
			const history = new History();

			expect(history.isEmpty()).toStrictEqual(true);

			history.add({ id: "a" });

			expect(history.isEmpty()).toStrictEqual(false);
		});
	});

	describe("last()", () => {
		it("should return the last item", () => {
			const history = new History();

			expect(history.last()).toStrictEqual(undefined);

			history.add({ id: "a" });
			history.add({ id: "b" });

			expect(history.last()).toStrictEqual({ id: "b" });
		});
	});

	describe("remove()", () => {
		it("should remove an item by id", () => {
			const history = new History();

			history.add({ id: "a" });
			history.add({ id: "b" });

			expect(history.remove("a")).toStrictEqual([{ id: "b" }]);
			expect(history.remove("b")).toStrictEqual([]);
		});

		it("should remove the last item when called without id", () => {
			const history = new History();

			history.add({ id: "a" });
			history.add({ id: "b" });

			expect(history.remove()).toStrictEqual([{ id: "a" }]);
		});
	});

	describe("removeLast()", () => {
		it("should remove the last item", () => {
			const history = new History();

			history.add({ id: "a" });
			history.add({ id: "b" });

			expect(history.removeLast()).toStrictEqual([{ id: "a" }]);
			expect(history.has("b")).toStrictEqual(false);
		});
	});
});
