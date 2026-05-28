import type { InputRule } from "prosemirror-inputrules";
import type { Schema } from "prosemirror-model";
import type { Command, Plugin, PluginSpec } from "prosemirror-state";
import type { Utils } from "./Utils";
import type Editor from "./Editor";

export type BaseContext = {
	schema: Schema;
	utils: Utils;
	[key: string]: unknown;
};

export type Button = {
	attrs?: Record<string, unknown>;
	command?: string;
	icon: string;
	id?: string;
	label: string;
	name?: string;
	separator?: boolean;
	when?: string[];
};

// eslint-disable-next-line @typescript-eslint/no-explicit-any
export type ExtensionCommand = (...args: any[]) => unknown;
export type Commands = ExtensionCommand | Record<string, ExtensionCommand>;

export default abstract class Extension<
	TOptions extends Record<string, unknown> = Record<string, unknown>
> {
	editor!: Editor;
	options: TOptions;

	constructor(options: Partial<TOptions> = {}) {
		this.options = {
			...this.defaults,
			...options
		};
	}

	bindEditor(editor: Editor): void {
		this.editor = editor;
	}

	/**
	 * Toolbar button/s for this extension.
	 * Mainly used by mark and node extensions.
	 */
	get button(): Button | Button[] | undefined {
		return undefined;
	}

	/**
	 * Returns the command(s) this extension contributes.
	 * Return a single function to register it under `extension.name`,
	 * or a keyed object to register multiple commands by their own names.
	 */
	commands(
		// eslint-disable-next-line @typescript-eslint/no-unused-vars
		_context: BaseContext
	): Commands {
		return {};
	}

	/**
	 * Default option values merged with constructor arguments.
	 * Override to declare the extension's configurable defaults.
	 */
	get defaults(): TOptions {
		return {} as TOptions;
	}

	/**
	 * Called after the extension is bound to the editor.
	 * Override to run setup logic that requires `this.editor`.
	 */
	init(): void {}

	/**
	 * ProseMirror input rules triggered while typing (e.g. auto-formatting)
	 */
	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	inputRules(context: BaseContext): InputRule[] {
		return [];
	}

	/**
	 * Keyboard shortcut bindings contributed by this extension
	 */
	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	keys(context: BaseContext): Record<string, Command | (() => void)> {
		return {};
	}

	/**
	 * Unique identifier for this extension,
	 * used as the command name and schema key
	 */
	abstract get name(): string;

	/**
	 * ProseMirror paste rules for transforming pasted content
	 */
	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	pasteRules(context: BaseContext): Plugin[] {
		return [];
	}

	/**
	 * ProseMirror plugins contributed by this extension.
	 */
	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	plugins(): (Plugin | PluginSpec<any>)[] {
		return [];
	}

	/**
	 * Extension category
	 */
	get type(): string {
		return "extension";
	}
}
