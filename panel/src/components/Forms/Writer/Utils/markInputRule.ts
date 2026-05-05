import { InputRule } from "prosemirror-inputrules";
import { type Attrs, type Mark, MarkType } from "prosemirror-model";
import type { EditorState, Transaction } from "prosemirror-state";

function getMarksBetween(start: number, end: number, state: EditorState) {
	const marks: { start: number; end: number; mark: Mark }[] = [];

	state.doc.nodesBetween(start, end, (node, pos) => {
		marks.push(
			...node.marks.map((mark) => ({
				start: pos,
				end: pos + node.nodeSize,
				mark
			}))
		);
	});

	return marks;
}

export default function markInputRule(
	regexp: RegExp,
	markType: MarkType,
	getAttrs: Attrs | ((match: RegExpMatchArray) => Attrs) = {}
): InputRule {
	return new InputRule(
		regexp,
		(
			state: EditorState,
			match: RegExpMatchArray,
			start: number,
			end: number
		): Transaction | null => {
			const attrs = getAttrs instanceof Function ? getAttrs(match) : getAttrs;
			const { tr } = state;
			const m = match.length - 1;

			let markEnd = end;
			let markStart = start;

			if (match[m]) {
				const matchStart = start + match[0].indexOf(match[m - 1]);
				const matchEnd = matchStart + match[m - 1].length - 1;
				const textStart = matchStart + match[m - 1].lastIndexOf(match[m]);
				const textEnd = textStart + match[m].length;

				const excludedMarks = getMarksBetween(start, end, state)
					.filter((item) => item.mark.type.excludes(markType))
					.filter((item) => item.end > matchStart);

				if (excludedMarks.length) {
					return null;
				}

				if (textEnd < matchEnd) {
					tr.delete(textEnd, matchEnd);
				}

				if (textStart > matchStart) {
					tr.delete(matchStart, textStart);
				}

				markStart = matchStart;
				markEnd = markStart + match[m].length;
			}

			tr.addMark(markStart, markEnd, markType.create(attrs));
			tr.removeStoredMark(markType);

			return tr;
		}
	);
}
