import { describe, expect, it } from "vitest";
import ListDoc from "./ListDoc";

const node = new ListDoc({ nodes: ["bulletList", "orderedList"] });

describe("ListDoc node", () => {
	describe("name", () => {
		it("returns 'doc'", () => {
			expect(node.name).toBe("doc");
		});
	});

	describe("schema", () => {
		it("joins nodes with a pipe separator as content", () => {
			expect(node.schema.content).toBe("bulletList|orderedList");
		});

		it("uses a single node when only one is provided", () => {
			const single = new ListDoc({ nodes: ["bulletList"] });
			expect(single.schema.content).toBe("bulletList");
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(node.type).toBe("node");
		});
	});
});
