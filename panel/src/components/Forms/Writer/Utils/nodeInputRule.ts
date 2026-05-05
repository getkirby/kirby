import { InputRule } from "prosemirror-inputrules";
import type { Attrs, NodeType } from "prosemirror-model";

export default function nodeInputRule(
	regexp: RegExp,
	type: NodeType,
	getAttrs: Attrs | ((match: RegExpMatchArray) => Attrs)
): InputRule {
	return new InputRule(regexp, (state, match, start, end) => {
		const attrs = getAttrs instanceof Function ? getAttrs(match) : getAttrs;
		const { tr } = state;

		if (match[0]) {
			tr.replaceWith(start, end, type.create(attrs));
			return tr;
		}

		return null;
	});
}
