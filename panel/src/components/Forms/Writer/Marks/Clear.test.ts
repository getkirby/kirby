import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { mockEditor } from "@test/unit/editor";
import Clear from "./Clear";

const mark = new Clear();

describe("Clear mark", () => {
	beforeEach(() => {
		vi.stubGlobal("panel", { $t: (key: string) => key });
	});

	afterEach(() => {
		vi.unstubAllGlobals();
	});

	describe("button", () => {
		it("returns the button config", () => {
			const button = mark.button;
			expect(button.icon).toBe("clear");
			expect(button.label).toBeDefined();
		});
	});

	describe("clear", () => {
		it("dispatches removeMark for each active mark", () => {
			const mark = new Clear();
			const dispatch = vi.fn();
			const removeMark = vi.fn().mockReturnValue("transaction");

			const editor = mockEditor({
				activeMarks: ["bold", "italic"],
				state: {
					tr: {
						selection: { from: 1, to: 5 },
						removeMark
					},
					schema: {
						marks: { bold: "boldType", italic: "italicType" }
					}
				},
				view: { dispatch }
			});

			mark.bindEditor(editor);
			mark.clear();

			expect(removeMark).toHaveBeenCalledTimes(2);
			expect(removeMark).toHaveBeenCalledWith(1, 5, "boldType");
			expect(removeMark).toHaveBeenCalledWith(1, 5, "italicType");
			expect(dispatch).toHaveBeenCalledTimes(2);
			expect(dispatch).toHaveBeenCalledWith("transaction");
		});

		it("does nothing when state is not available", () => {
			const mark = new Clear();
			const dispatch = vi.fn();

			const editor = mockEditor({
				activeMarks: ["bold"],
				state: undefined,
				view: { dispatch }
			});

			mark.bindEditor(editor);
			mark.clear();

			expect(dispatch).not.toHaveBeenCalled();
		});

		it("does nothing when there are no active marks", () => {
			const mark = new Clear();
			const dispatch = vi.fn();

			const editor = mockEditor({
				activeMarks: [],
				state: {
					tr: { selection: { from: 1, to: 5 }, removeMark: vi.fn() },
					schema: { marks: {} }
				},
				view: { dispatch }
			});

			mark.bindEditor(editor);
			mark.clear();

			expect(dispatch).not.toHaveBeenCalled();
		});
	});

	describe("commands", () => {
		it("calls clear() when the command is invoked", () => {
			const mark = new Clear();
			const editor = mockEditor();
			mark.bindEditor(editor);

			vi.spyOn(mark, "clear").mockImplementation(() => {});

			const command = mark.commands();
			command();

			expect(mark.clear).toHaveBeenCalledOnce();
		});
	});

	describe("name", () => {
		it("returns 'clear'", () => {
			expect(mark.name).toBe("clear");
		});
	});
});
