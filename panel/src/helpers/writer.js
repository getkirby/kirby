/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */

import Mark from "@/components/Forms/Writer/Mark";
import Node from "@/components/Forms/Writer/Node";
import { isObject } from "./object";

// Marks
import {
	Bold,
	Clear,
	Code,
	Email,
	Italic,
	Link,
	Strike,
	Sup,
	Sub,
	Underline
} from "@/components/Forms/Writer/Marks";

// Nodes
import {
	BulletList,
	Doc,
	HardBreak,
	Heading,
	HorizontalRule,
	ListItem,
	OrderedList,
	Quote,
	Paragraph,
	Text
} from "@/components/Forms/Writer/Nodes";

export const allowedExtensions = (available, allowed) => {
	if (allowed === false) {
		return [];
	}

	if (allowed === true) {
		return Object.keys(available);
	}

	if (Array.isArray(allowed) === true) {
		return allowed;
	}

	if (isObject(allowed) === true) {
		return Object.keys(allowed).filter((key) => allowed[key] !== false);
	}

	return Object.keys(available);
};

/**
 * Returns all available marks.
 *
 * The order  determines the ProseMirror schema mark rank, which
 * decides the nesting priority: marks listed first wrap the ones listed
 * later. Interactive marks (link, email) come first so that they are not
 * split up by decorative marks like bold or italic.
 * @see https://github.com/getkirby/kirby/issues/5481
 */
export const availableMarks = (options = {}) => {
	return {
		link: new Link(options.link ?? {}),
		email: new Email(options.email ?? {}),
		bold: new Bold(options.bold ?? {}),
		clear: new Clear(options.clear ?? {}),
		code: new Code(options.code ?? {}),
		italic: new Italic(options.italic ?? {}),
		strike: new Strike(options.strike ?? {}),
		sup: new Sup(options.sup ?? {}),
		sub: new Sub(options.sub ?? {}),
		underline: new Underline(options.underline ?? {}),
		...availableMarksFromPlugins()
	};
};

export const availableMarksFromPlugins = () => {
	return createExtensionsFromPlugins(
		window?.panel?.plugins?.writerMarks ?? {},
		Mark.prototype
	);
};

export const availableNodes = (options = {}) => {
	return {
		bulletList: new BulletList(options.bulletList ?? {}),
		doc: new Doc(options.doc ?? {}),
		hardBreak: new HardBreak(options.hardBreak ?? {}),
		heading: new Heading(options.heading ?? {}),
		horizontalRule: new HorizontalRule(options.horizontalRule ?? {}),
		listItem: new ListItem(options.listItem ?? {}),
		orderedList: new OrderedList(options.orderedList ?? {}),
		paragraph: new Paragraph(options.paragraph ?? {}),
		quote: new Quote(options.quote ?? {}),
		text: new Text(options.text ?? {}),
		...availableNodesFromPlugins()
	};
};

export const availableNodesFromPlugins = () => {
	return createExtensionsFromPlugins(
		window?.panel?.plugins?.writerNodes ?? {},
		Node.prototype
	);
};

export const createExtensionsFromPlugins = (plugins, proto) => {
	const extensions = {};

	// take each extension object and turn
	// it into an instance that extends the Node or Mark class
	for (const name in plugins) {
		extensions[name] = Object.create(
			proto,
			Object.getOwnPropertyDescriptors({ name, ...plugins[name] })
		);
	}

	return extensions;
};

export const createMarks = (marks, required = []) => {
	const options = extensionOptions(marks);
	const available = availableMarks(options);
	const installed = filterExtensions(available, marks);

	// re-install all required extensions
	for (const extension of required) {
		installed[extension] = available[extension];
	}

	return installed;
};

export const createNodes = (nodes, required = []) => {
	const options = extensionOptions(nodes);
	const available = availableNodes(options);
	const installed = filterExtensions(available, nodes);

	// re-install all required extensions
	for (const extension of required) {
		installed[extension] = available[extension];
	}

	// always install the list item node if there's a bullet list or ordered list
	if (installed.bulletList || installed.orderedList) {
		installed.listItem = available.listItem;
	}

	return installed;
};

export const extensionOptions = (allowed) => {
	if (
		Array.isArray(allowed) === true ||
		isObject(allowed) === false ||
		allowed === null
	) {
		return {};
	}

	const options = {};

	for (const [name, value] of Object.entries(allowed)) {
		if (typeof value === "object" && value !== null) {
			options[name] = value;
		}
	}

	return options;
};

export const filterExtensions = (available, allowed) => {
	allowed = allowedExtensions(available, allowed);

	let installed = {};

	// iterate over `allowed` (not `available`) so that the order defined
	// in the blueprint is preserved; this order becomes the ProseMirror
	// schema mark rank and thus determines the nesting priority of marks
	for (const extension of allowed) {
		if (available[extension]) {
			installed[extension] = available[extension];
		}
	}

	return installed;
};

export const keepInlineNodes = (nodes) => {
	return nodes.filter((node) => node.schema.inline === true);
};

export default {
	allowedExtensions,
	availableMarks,
	availableMarksFromPlugins,
	availableNodes,
	availableNodesFromPlugins,
	createMarks,
	createNodes,
	extensionOptions,
	filterExtensions,
	keepInlineNodes
};
