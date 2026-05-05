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

	get button(): Button | Button[] | undefined {
		return undefined;
	}

	commands(
		// eslint-disable-next-line @typescript-eslint/no-unused-vars
		_context: BaseContext
	): ExtensionCommand | Record<string, ExtensionCommand> {
		return {};
	}

	get defaults(): TOptions {
		return {} as TOptions;
	}

	init(): void {}

	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	inputRules(context: BaseContext): InputRule[] {
		return [];
	}

	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	keys(context: BaseContext): Record<string, Command | (() => void)> {
		return {};
	}

	abstract get name(): string;

	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	pasteRules(context: BaseContext): Plugin[] {
		return [];
	}

	// eslint-disable-next-line @typescript-eslint/no-explicit-any
	plugins(): (Plugin | PluginSpec<any>)[] {
		return [];
	}

	get type(): string {
		return "extension";
	}
}
