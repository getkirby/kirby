/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */

import type Extension from "@/components/Forms/Writer/Extension";
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

/**
 * Describes which extensions are enabled:
 * `false` (none), `true` (all), an array (subset) of names and/or
 * ready-made extension instances, or an object keyed by name
 * with `true`/`false`/options.
 */
export type Allowed<T = never> =
	boolean | (string | T)[] | Record<string, unknown> | null | undefined;

/**
 * Resolves the list of allowed extensions
 * from the various `allowed` config formats.
 */
export function allowedExtensions<T>(
	available: Record<string, unknown>,
	allowed: Allowed<T>
): (string | T)[] {
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
}

/**
 * Returns all available marks.
 *
 * The order  determines the ProseMirror schema mark rank, which
 * decides the nesting priority: marks listed first wrap the ones listed
 * later. Interactive marks (link, email) come first so that they are not
 * split up by decorative marks like bold or italic.
 * @see https://github.com/getkirby/kirby/issues/5481
 */
export function availableMarks(
	options: Record<string, Record<string, unknown> | undefined> = {}
): Record<string, Mark> {
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
}

/**
 * Creates instances of all mark extensions
 * registered by Panel plugins
 */
export function availableMarksFromPlugins(): Record<string, Mark> {
	return createExtensionsFromPlugins(
		window?.panel?.plugins?.writerMarks ?? {},
		Mark.prototype
	);
}

/**
 * Creates instances of all built-in nodes (plus plugin nodes),
 * passing the matching options to each constructor
 */
export function availableNodes(
	options: Record<string, Record<string, unknown> | undefined> = {}
): Record<string, Node> {
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
}

/**
 * Creates instances of all node extensions
 * registered by panel plugins
 */
export function availableNodesFromPlugins(): Record<string, Node> {
	return createExtensionsFromPlugins(
		window?.panel?.plugins?.writerNodes ?? {},
		Node.prototype
	);
}

/**
 * Turns each plugin extension definition into an instance
 * that extends the given `Node` or `Mark` prototype
 */
export function createExtensionsFromPlugins<T extends Extension>(
	plugins: Record<string, object>,
	proto: T
): Record<string, T> {
	const extensions: Record<string, T> = {};

	// take each extension object and turn
	// it into an instance that extends the Node or Mark class
	for (const name in plugins) {
		extensions[name] = Object.create(
			proto,
			Object.getOwnPropertyDescriptors({ name, ...plugins[name] })
		);
	}

	return extensions;
}

/**
 * Creates the installed marks for the given `marks` config,
 * always re-installing any `required` marks
 */
export function createMarks(
	marks: Allowed<Mark>,
	required: string[] = []
): Record<string, Mark> {
	const options = extensionOptions(marks);
	const available = availableMarks(options);
	const installed = filterExtensions(available, marks);

	// re-install all required extensions
	for (const extension of required) {
		installed[extension] = available[extension];
	}

	return installed;
}

/**
 * Creates the installed nodes for the given `nodes` config,
 * always re-installing any `required` nodes and the list item
 * node whenever a bullet or ordered list is installed
 */
export function createNodes(
	nodes: Allowed<Node>,
	required: string[] = []
): Record<string, Node> {
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
}

/**
 * Extracts the per-extension option objects from an `allowed`
 * config object, ignoring boolean and non-object values
 */
export function extensionOptions(
	allowed: Allowed<unknown>
): Record<string, Record<string, unknown>> {
	if (
		Array.isArray(allowed) === true ||
		isObject(allowed) === false ||
		allowed === null
	) {
		return {};
	}

	const options: Record<string, Record<string, unknown>> = {};

	for (const [name, value] of Object.entries(allowed)) {
		if (typeof value === "object" && value !== null) {
			options[name] = value as Record<string, unknown>;
		}
	}

	return options;
}

/**
 * Reduces the `available` extensions to those enabled by `allowed`,
 * installing any ready-made instance under its own name
 */
export function filterExtensions<T extends { name: string }>(
	available: Record<string, T>,
	allowed: Allowed<T>
): Record<string, T> {
	const names = allowedExtensions(available, allowed);

	const installed: Record<string, T> = {};

	// iterate over `allowed` (not `available`) so that the order defined
	// in the blueprint is preserved; this order becomes the ProseMirror
	// schema mark rank and thus determines the nesting priority of marks
	for (const extension of names) {
		if (typeof extension !== "string") {
			installed[extension.name] = extension;
		} else if (available[extension] !== undefined) {
			installed[extension] = available[extension];
		}
	}

	return installed;
}

/**
 * Keeps only the inline nodes from the given list of nodes
 */
export function keepInlineNodes(nodes: Node[]): Node[] {
	return nodes.filter((node) => node.schema.inline === true);
}

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
