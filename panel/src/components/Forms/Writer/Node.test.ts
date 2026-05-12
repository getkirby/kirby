import { describe, expect, it } from "vitest";
import type { NodeSpec } from "prosemirror-model";
import type { BaseContext } from "./Extension";
import Node from "./Node";

class TestNode extends Node {
	get name() {
		return "paragraph";
	}
	get schema(): NodeSpec {
		return { content: "inline*" };
	}
}

const node = new TestNode();
const context = {} as BaseContext;

describe("Node", () => {
	describe("commands", () => {
		it("returns an empty object by default", () => {
			const commands = node.commands(context);
			expect(commands).toStrictEqual({});
		});
	});

	describe("keys", () => {
		it("returns an empty object by default", () => {
			const keys = node.keys(context);
			expect(keys).toStrictEqual({});
		});
	});

	describe("inputRules", () => {
		it("returns an empty array by default", () => {
			const rules = node.inputRules(context);
			expect(rules).toStrictEqual([]);
		});
	});

	describe("pasteRules", () => {
		it("returns an empty array by default", () => {
			const rules = node.pasteRules(context);
			expect(rules).toStrictEqual([]);
		});
	});

	describe("type", () => {
		it("returns 'node'", () => {
			expect(node.type).toBe("node");
		});
	});

	describe("view", () => {
		it("returns undefined by default", () => {
			expect(node.view).toBeUndefined();
		});
	});
});
