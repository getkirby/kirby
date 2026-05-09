import type { InputRule } from "prosemirror-inputrules";
import type { NodeSpec, NodeType } from "prosemirror-model";
import type { Command, Plugin } from "prosemirror-state";
import type { NodeViewConstructor } from "prosemirror-view";
import Extension, { type BaseContext, type Commands } from "./Extension";

export type NodeContext = BaseContext & { type: NodeType };

export default abstract class Node<
	TOptions extends Record<string, unknown> = Record<string, unknown>
> extends Extension<TOptions> {
	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	commands(context: NodeContext): Commands {
		return {};
	}

	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	inputRules(context: NodeContext): InputRule[] {
		return [];
	}

	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	keys(context: NodeContext): Record<string, Command | (() => void)> {
		return {};
	}

	// eslint-disable-next-line @typescript-eslint/no-unused-vars
	pasteRules(context: NodeContext): Plugin[] {
		return [];
	}

	abstract get schema(): NodeSpec;

	get type(): "node" {
		return "node";
	}

	get view(): NodeViewConstructor | undefined {
		return undefined;
	}
}
