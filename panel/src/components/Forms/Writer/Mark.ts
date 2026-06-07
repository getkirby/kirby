import type { InputRule } from "prosemirror-inputrules";
import type { Attrs, MarkSpec, MarkType } from "prosemirror-model";
import type { Command, Plugin } from "prosemirror-state";
import type { MarkViewConstructor } from "prosemirror-view";
import Extension, { type BaseContext, type Commands } from "./Extension";

export type MarkContext = BaseContext & { type: MarkType };

export default abstract class Mark<
	TOptions extends Record<string, unknown> = Record<string, unknown>
> extends Extension<TOptions> {
	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	commands(context: MarkContext): Commands {
		return {};
	}

	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	inputRules(context: MarkContext): InputRule[] {
		return [];
	}

	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	keys(context: MarkContext): Record<string, Command | (() => void)> {
		return {};
	}

	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	pasteRules(context: MarkContext): Plugin[] {
		return [];
	}

	remove(): void {
		this.editor.removeMark(this.name);
	}

	abstract get schema(): MarkSpec;

	toggle(): void {
		this.editor.toggleMark(this.name);
	}

	get type(): "mark" {
		return "mark";
	}

	update(attrs: Attrs): void {
		this.editor.updateMark(this.name, attrs);
	}

	get view(): MarkViewConstructor | undefined {
		return undefined;
	}
}
