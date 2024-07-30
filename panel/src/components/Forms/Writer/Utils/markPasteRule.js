import { Plugin } from "prosemirror-state";
import { Slice, Fragment } from "prosemirror-model";

export default function (regexp, type, getAttrs) {
	const handler = (fragment, parent) => {
		const nodes = [];

		fragment.forEach((child) => {
			if (child.isText) {
				const { text, marks } = child;
				let pos = 0;
				let match;

				const isLink = !!marks.filter((x) => x.type.name === "link")[0];

				while (!isLink && (match = regexp.exec(text)) !== null) {
					if (parent?.type?.allowsMarkType(type) && match[1]) {
						let start, end, textStart, textEnd;

						if (match[0].startsWith(" ")) {
							// support improved regex with lookaheads
							// (they always include a leading space in the match)
							start = match.index + (match[0].length - match[1].length);
							end = start + match[1].length;
							textStart = start + match[1].indexOf(match[2]);
							textEnd = textStart + match[2].length;
						} else {
							// older, more simple regex
							start = match.index;
							end = start + match[0].length;
							textStart = start + match[0].indexOf(match[1]);
							textEnd = textStart + match[1].length;
						}

						const attrs =
							getAttrs instanceof Function ? getAttrs(match) : getAttrs;

						// adding text before markdown to nodes
						if (start > 0) {
							nodes.push(child.cut(pos, start));
						}

						// adding the markdown part to nodes
						nodes.push(
							child
								.cut(textStart, textEnd)
								.mark(type.create(attrs).addToSet(child.marks))
						);

						pos = end;
					}
				}

				// adding rest of text to nodes
				if (pos < text.length) {
					nodes.push(child.cut(pos));
				}
			} else {
				nodes.push(child.copy(handler(child.content, child)));
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
