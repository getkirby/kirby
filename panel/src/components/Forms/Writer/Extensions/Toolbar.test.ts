import { describe, expect, it, vi } from "vitest";
import type { ComponentPublicInstance } from "vue";
import type Editor from "../Editor";
import Toolbar from "./Toolbar";

const mockToolbar = () => ({
	close: vi.fn(),
	open: vi.fn()
});

const mockWriter = (toolbar = mockToolbar()) =>
	({ $refs: { toolbar } }) as unknown as ComponentPublicInstance;

const mockEditor = () => {
	const handlers: Record<string, (arg: unknown) => void> = {};
	return {
		editor: {
			on: vi.fn((event: string, fn: (arg: unknown) => void) => {
				handlers[event] = fn;
			})
		},
		trigger: (event: string, arg: unknown) => handlers[event]?.(arg)
	};
};

const createToolbar = () => {
	const toolbar = mockToolbar();
	const writer = mockWriter(toolbar);
	const { editor, trigger } = mockEditor();
	const ext = new Toolbar(writer);
	ext.bindEditor(editor as unknown as Editor);
	ext.init();
	return { ext, toolbar, trigger };
};

describe("Toolbar", () => {
	describe("init", () => {
		it("registers deselect and select listeners on the editor", () => {
			const { editor } = mockEditor();
			const ext = new Toolbar(mockWriter());
			ext.bindEditor(editor as unknown as Editor);
			ext.init();
			expect(editor.on).toHaveBeenCalledWith("deselect", expect.any(Function));
			expect(editor.on).toHaveBeenCalledWith("select", expect.any(Function));
		});
	});

	describe("deselect", () => {
		it("calls component.close with the event", () => {
			const { toolbar, trigger } = createToolbar();
			const event = new Event("mousedown");
			trigger("deselect", { event });
			expect(toolbar.close).toHaveBeenCalledWith(event);
		});
	});

	describe("name", () => {
		it("returns 'toolbar'", () => {
			expect(new Toolbar(mockWriter()).name).toBe("toolbar");
		});
	});

	describe("select", () => {
		it("calls component.open when hasChanged is true", () => {
			const { toolbar, trigger } = createToolbar();
			trigger("select", { hasChanged: true });
			expect(toolbar.open).toHaveBeenCalledOnce();
		});

		it("does not call component.open when hasChanged is not true", () => {
			const { toolbar, trigger } = createToolbar();
			trigger("select", { hasChanged: false });
			expect(toolbar.open).not.toHaveBeenCalled();
		});
	});

	describe("type", () => {
		it("returns 'toolbar'", () => {
			expect(new Toolbar(mockWriter()).type).toBe("toolbar");
		});
	});
});
