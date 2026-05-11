import {
	Slice,
	Fragment,
	type Attrs,
	type MarkType,
	type Node
} from "prosemirror-model";
import { Plugin } from "prosemirror-state";

/**
 * @deprecate 6.0.0 Use `markPasteRule` instead
 */
export default function pasteRule(
	regexp: RegExp,
	type: MarkType,
	getAttrs: Attrs | ((match: string) => Attrs) = {}
): Plugin {
	const handler = (fragment: Fragment): Fragment => {
		const nodes: Node[] = [];

		fragment.forEach((child) => {
			if (child.isText) {
				const { text } = child;
				let pos = 0;
				let match;

				do {
					match = regexp.exec(text!);
					if (match) {
						const start = match.index;
						const end = start + match[0].length;
						const attrs =
							getAttrs instanceof Function ? getAttrs(match[0]) : getAttrs;

						if (start > 0) {
							nodes.push(child.cut(pos, start));
						}

						nodes.push(
							child
								.cut(start, end)
								.mark(type.create(attrs).addToSet(child.marks))
						);

						pos = end;
					}
				} while (match);

				if (pos < text!.length) {
					nodes.push(child.cut(pos));
				}
			} else {
				nodes.push(child.copy(handler(child.content)));
			}
		});

		return Fragment.fromArray(nodes);
	};

	return new Plugin({
		props: {
			transformPasted: (slice) =>
				new Slice(handler(slice.content), slice.openStart, slice.openEnd)
		}
	});
}
