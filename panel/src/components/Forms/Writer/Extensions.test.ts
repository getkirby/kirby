import { describe, expect, it, vi } from "vitest";
import { Schema } from "prosemirror-model";
import { Plugin } from "prosemirror-state";
import type { MarkSpec, NodeSpec } from "prosemirror-model";
import type {
	EditorView,
	MarkViewConstructor,
	NodeViewConstructor
} from "prosemirror-view";
import Extension from "./Extension";
import Mark from "./Mark";
import Node from "./Node";
import Extensions from "./Extensions";
import type Editor from "./Editor";

const mockEditor = {} as Editor;

const mockView = (editable = true) =>
	({
		editable,
		focus: vi.fn(),
		state: {},
		dispatch: vi.fn()
	}) as unknown as EditorView;

const schema = new Schema({
	nodes: { doc: { content: "text*" }, text: {} }
}) as unknown as Schema;

// Fixtures

class PlainExtension extends Extension {
	get name() {
		return "plain";
	}
}

class MarkExtension extends Mark {
	get name() {
		return "bold";
	}
	get schema(): MarkSpec {
		return { attrs: {} };
	}
}

class MarkExtensionWithView extends Mark {
	private readonly _view = vi.fn() as unknown as MarkViewConstructor;
	get name() {
		return "link";
	}
	get schema(): MarkSpec {
		return { attrs: {} };
	}
	get view(): MarkViewConstructor {
		return this._view;
	}
}

class NodeExtension extends Node {
	get name() {
		return "paragraph";
	}
	get schema(): NodeSpec {
		return { content: "inline*" };
	}
}

class NodeExtensionWithView extends Node {
	private readonly _view = vi.fn() as unknown as NodeViewConstructor;
	get name() {
		return "image";
	}
	get schema(): NodeSpec {
		return {};
	}
	get view(): NodeViewConstructor {
		return this._view;
	}
}

describe("Extensions", () => {
	describe("constructor", () => {
		it("calls bindEditor and init on each extension", () => {
			const ext = new PlainExtension();
			ext.bindEditor = vi.fn();
			ext.init = vi.fn();

			new Extensions([ext], mockEditor);

			expect(ext.bindEditor).toHaveBeenCalledWith(mockEditor);
			expect(ext.init).toHaveBeenCalledOnce();
		});

		it("stores the editor reference", () => {
			const exts = new Extensions([], mockEditor);
			expect(exts.editor).toBe(mockEditor);
		});
	});

	describe("buttons", () => {
		it("returns only buttons matching the given type", () => {
			class BoldMark extends Mark {
				get name() {
					return "bold";
				}
				get schema(): MarkSpec {
					return {};
				}
				get button() {
					return { icon: "bold", label: "Bold" };
				}
			}

			class ParagraphNode extends Node {
				get name() {
					return "paragraph";
				}
				get schema(): NodeSpec {
					return {};
				}
				get button() {
					return { icon: "paragraph", label: "Paragraph" };
				}
			}

			const exts = new Extensions(
				[new BoldMark(), new ParagraphNode()],
				mockEditor
			);

			expect(Object.keys(exts.buttons("mark"))).toStrictEqual(["bold"]);
			expect(Object.keys(exts.buttons("node"))).toStrictEqual(["paragraph"]);
		});

		it("keys a single button by extension name", () => {
			class BoldMark extends Mark {
				get name() {
					return "bold";
				}
				get schema(): MarkSpec {
					return {};
				}
				get button() {
					return { icon: "bold", label: "Bold" };
				}
			}

			const exts = new Extensions([new BoldMark()], mockEditor);
			const buttons = exts.buttons("mark");

			expect(buttons["bold"]).toMatchObject({
				icon: "bold",
				label: "Bold",
				name: "bold"
			});
		});

		it("keys array buttons by their id, falling back to name", () => {
			class HeadingNode extends Node {
				get name() {
					return "heading";
				}
				get schema(): NodeSpec {
					return {};
				}
				get button() {
					return [
						{ id: "h1", icon: "h1", label: "H1" },
						{ name: "h2", icon: "h2", label: "H2" }
					];
				}
			}

			const exts = new Extensions([new HeadingNode()], mockEditor);
			const buttons = exts.buttons("node");

			expect(Object.keys(buttons)).toStrictEqual(["h1", "h2"]);
		});

		it("skips array buttons with no id or name", () => {
			class HeadingNode extends Node {
				get name() {
					return "heading";
				}
				get schema(): NodeSpec {
					return {};
				}
				get button() {
					return [{ icon: "h1", label: "H1" }];
				}
			}

			const exts = new Extensions([new HeadingNode()], mockEditor);
			expect(Object.keys(exts.buttons("node"))).toHaveLength(0);
		});
	});

	describe("commands", () => {
		it("returns false when the view is not editable", () => {
			class BoldMark extends Mark {
				get name() {
					return "bold";
				}
				get schema(): MarkSpec {
					return {};
				}
				commands() {
					return () => true;
				}
			}

			const exts = new Extensions([new BoldMark()], mockEditor);
			const view = mockView(false);
			const commands = exts.commands({ schema, view });

			expect(commands["bold"]()).toBe(false);
		});

		it("focuses the view and runs the command", () => {
			class BoldMark extends Mark {
				get name() {
					return "bold";
				}
				get schema(): MarkSpec {
					return {};
				}
				commands() {
					return () => "done";
				}
			}

			const exts = new Extensions([new BoldMark()], mockEditor);
			const view = mockView();
			const commands = exts.commands({ schema, view });

			const result = commands["bold"]();

			expect(view.focus).toHaveBeenCalledOnce();
			expect(result).toBe("done");
		});

		it("calls a returned function with (state, dispatch, view)", () => {
			const innerCommand = vi.fn().mockReturnValue(true);
			class BoldMark extends Mark {
				get name() {
					return "bold";
				}
				get schema(): MarkSpec {
					return {};
				}
				commands() {
					return () => innerCommand;
				}
			}

			const exts = new Extensions([new BoldMark()], mockEditor);
			const view = mockView();
			const commands = exts.commands({ schema, view });

			commands["bold"]();

			expect(innerCommand).toHaveBeenCalledWith(
				view.state,
				view.dispatch,
				view
			);
		});

		it("uses the extension name as the command name for single-command extensions", () => {
			class BoldMark extends Mark {
				get name() {
					return "bold";
				}
				get schema(): MarkSpec {
					return {};
				}
				commands() {
					return () => true;
				}
			}

			const exts = new Extensions([new BoldMark()], mockEditor);
			const commands = exts.commands({ schema, view: mockView() });

			expect("bold" in commands).toBe(true);
		});

		it("uses the object keys as command names for multi-command extensions", () => {
			class HistoryExtension extends PlainExtension {
				commands() {
					return { undo: () => true, redo: () => true };
				}
			}

			const exts = new Extensions([new HistoryExtension()], mockEditor);
			const commands = exts.commands({ schema, view: mockView() });

			expect("undo" in commands).toBe(true);
			expect("redo" in commands).toBe(true);
		});
	});

	describe("getAllowedExtensions", () => {
		it("returns all extensions when called without arguments", () => {
			const ext = new PlainExtension();
			const exts = new Extensions([ext], mockEditor);

			expect(exts.getAllowedExtensions()).toStrictEqual([ext]);
		});

		it("returns an empty array when passed true", () => {
			const exts = new Extensions([new PlainExtension()], mockEditor);
			expect(exts.getAllowedExtensions(true)).toStrictEqual([]);
		});

		it("excludes extensions whose names are in the array", () => {
			const ext = new PlainExtension();
			const exts = new Extensions([ext], mockEditor);

			expect(exts.getAllowedExtensions(["plain"])).toStrictEqual([]);
			expect(exts.getAllowedExtensions(["other"])).toStrictEqual([ext]);
		});
	});

	describe("marks", () => {
		it("returns a map of mark name to MarkSpec, excluding non-marks", () => {
			const exts = new Extensions(
				[new MarkExtension(), new PlainExtension()],
				mockEditor
			);

			expect(exts.marks).toStrictEqual({ bold: { attrs: {} } });
		});
	});

	describe("markViews", () => {
		it("only includes marks that define a view", () => {
			const withView = new MarkExtensionWithView();
			const exts = new Extensions([new MarkExtension(), withView], mockEditor);

			expect(Object.keys(exts.markViews)).toStrictEqual(["link"]);
			expect(exts.markViews["link"]).toBe(withView.view);
		});
	});

	describe("nodes", () => {
		it("returns a map of node name to NodeSpec, excluding non-nodes", () => {
			const exts = new Extensions(
				[new NodeExtension(), new PlainExtension()],
				mockEditor
			);

			expect(exts.nodes).toStrictEqual({ paragraph: { content: "inline*" } });
		});
	});

	describe("nodeViews", () => {
		it("only includes nodes that define a view", () => {
			const withView = new NodeExtensionWithView();
			const exts = new Extensions([new NodeExtension(), withView], mockEditor);

			expect(Object.keys(exts.nodeViews)).toStrictEqual(["image"]);
			expect(exts.nodeViews["image"]).toBe(withView.view);
		});
	});

	describe("plugins", () => {
		it("passes Plugin instances through unchanged", () => {
			const plugin = new Plugin({});
			class PluginExtension extends PlainExtension {
				plugins() {
					return [plugin];
				}
			}

			const exts = new Extensions([new PluginExtension()], mockEditor);
			const result = exts.plugins({ schema });

			expect(result[0]).toBe(plugin);
		});

		it("wraps plain PluginSpec objects in a Plugin", () => {
			const spec = { props: {} };
			class PluginExtension extends PlainExtension {
				plugins() {
					return [spec as never];
				}
			}

			const exts = new Extensions([new PluginExtension()], mockEditor);
			const result = exts.plugins({ schema });

			expect(result[0]).toBeInstanceOf(Plugin);
		});
	});
});
