import type { InputRule } from "prosemirror-inputrules";
import { keymap } from "prosemirror-keymap";
import { type Command, Plugin, type PluginSpec } from "prosemirror-state";
import utils, { type Utils } from "./Utils";
import Extension, {
	type BaseContext,
	type Button,
	type ExtensionCommand
} from "./Extension";
import type Mark from "./Mark";
import type Node from "./Node";
import type Editor from "./Editor";
import type { MarkSpec, NodeSpec, Schema } from "prosemirror-model";
import type {
	EditorView,
	MarkViewConstructor,
	NodeViewConstructor
} from "prosemirror-view";

type FeatureReturnMap = {
	inputRules: InputRule[];
	keys: Record<string, Command>;
	pasteRules: Plugin[];
	plugins: (Plugin | PluginSpec<unknown>)[];
};

export default class Extensions {
	editor: Editor;
	extensions: Extension[];

	constructor(extensions: Extension[] = [], editor: Editor) {
		for (const extension of extensions) {
			extension.bindEditor(editor);
			extension.init();
		}

		this.editor = editor;
		this.extensions = extensions;
	}

	buttons(type: "mark" | "node" = "mark") {
		const buttons: Record<string, Button> = {};

		for (const extension of this.extensions) {
			if (extension.type !== type) {
				continue;
			}

			if (!extension.button) {
				continue;
			}

			if (Array.isArray(extension.button) === true) {
				for (const button of extension.button) {
					const name = button.id ?? button.name;

					if (name) {
						buttons[name] = button;
					}
				}
			} else {
				buttons[extension.name] = {
					name: extension.name,
					...extension.button
				};
			}
		}

		return buttons;
	}

	commands({ schema, view }: { schema: Schema; view: EditorView }) {
		const wrap =
			(callback: ExtensionCommand): ExtensionCommand =>
			(attrs) => {
				if (!view.editable) {
					return false;
				}

				view.focus();

				const result = callback(attrs);

				return typeof result === "function"
					? result(view.state, view.dispatch, view)
					: result;
			};

		return this.extensions.reduce(
			(allCommands, extension) => {
				const { name, type } = extension;

				const value = extension.commands({
					schema,
					utils,
					...(["node", "mark"].includes(type)
						? {
								type: type === "node" ? schema.nodes[name] : schema.marks[name]
							}
						: {})
				});

				const commands: Record<string, ExtensionCommand> =
					typeof value === "function"
						? { [name]: wrap(value) }
						: Object.fromEntries(
								Object.entries(value).map(([k, cb]) => [k, wrap(cb)])
							);

				return { ...allCommands, ...commands };
			},
			{} as Record<string, ExtensionCommand>
		);
	}

	getAllowedExtensions(excluded?: boolean | string[]) {
		if (excluded === true) {
			return [];
		}

		if (Array.isArray(excluded) === true) {
			return this.extensions.filter(
				(extension) => !excluded.includes(extension.name)
			);
		}

		return this.extensions;
	}

	getFromExtensions<
		K extends keyof FeatureReturnMap,
		P extends { schema: Schema }
	>(
		feature: K,
		params: P,
		extensions = this.extensions
	): FeatureReturnMap[K][] {
		type FeatureCallback = (ctx: P & { utils: Utils }) => FeatureReturnMap[K];

		return (
			extensions
				// only get from pure extensions
				.filter((extension) => extension.type === "extension")
				// only use extensions that implement the feature
				.filter((extension) => extension[feature])
				.map((extension) => {
					const callback = extension[feature] as FeatureCallback;
					return callback.call(extension, { ...params, utils });
				})
		);
	}

	getFromNodesAndMarks<
		K extends keyof FeatureReturnMap,
		P extends { schema: Schema }
	>(
		feature: K,
		params: P,
		extensions = this.extensions
	): FeatureReturnMap[K][] {
		type FeatureCallback = (ctx: BaseContext) => FeatureReturnMap[K];
		const { schema } = params;

		return (
			extensions
				// only get from nodes and marks extensions
				.filter((extension): extension is Mark | Node =>
					["node", "mark"].includes(extension.type)
				)
				// only use extensions that implement the feature
				.filter((extension) => extension[feature])
				.map((extension) => {
					const callback = extension[feature] as FeatureCallback;
					const type =
						extension.type === "node"
							? schema.nodes[extension.name]
							: schema.marks[extension.name];

					return callback.call(extension, { ...params, type, utils });
				})
		);
	}

	inputRules({
		schema,
		excludedExtensions
	}: {
		schema: Schema;
		excludedExtensions?: boolean | string[];
	}) {
		const allowed = this.getAllowedExtensions(excludedExtensions);

		return [
			...this.getFromExtensions("inputRules", { schema }, allowed),
			...this.getFromNodesAndMarks("inputRules", { schema }, allowed)
		].flat();
	}

	keymaps({ schema }: { schema: Schema }) {
		return [
			...this.getFromExtensions("keys", { schema }),
			...this.getFromNodesAndMarks("keys", { schema })
		].map((keys) => keymap(keys));
	}

	get marks() {
		return this.extensions
			.filter((extension): extension is Mark => extension.type === "mark")
			.reduce(
				(marks, { name, schema }) => ({ ...marks, [name]: schema }),
				{} as Record<string, MarkSpec>
			);
	}

	get markViews() {
		return this.extensions
			.filter((extension): extension is Mark => extension.type === "mark")
			.filter((extension) => extension.view)
			.reduce(
				(views, { name, view }) => ({ ...views, [name]: view! }),
				{} as Record<string, MarkViewConstructor>
			);
	}

	get nodes() {
		return this.extensions
			.filter((extension): extension is Node => extension.type === "node")
			.reduce(
				(nodes, { name, schema }) => ({ ...nodes, [name]: schema }),
				{} as Record<string, NodeSpec>
			);
	}

	get nodeViews() {
		return this.extensions
			.filter((extension): extension is Node => extension.type === "node")
			.filter((extension) => extension.view)
			.reduce(
				(views, { name, view }) => ({ ...views, [name]: view! }),
				{} as Record<string, NodeViewConstructor>
			);
	}

	get options() {
		const { view } = this.editor;
		return this.extensions.reduce(
			(options, extension) => ({
				...options,
				[extension.name]: new Proxy(extension.options, {
					set(obj, prop, value) {
						if (typeof prop !== "string") {
							return true;
						}

						const changed = obj[prop] !== value;

						Object.assign(obj, { [prop]: value });

						if (changed && view) {
							view.updateState(view.state);
						}

						return true;
					}
				})
			}),
			{} as Record<string, Record<string, unknown>>
		);
	}

	pasteRules({
		schema,
		excludedExtensions
	}: {
		schema: Schema;
		excludedExtensions?: boolean | string[];
	}) {
		const allowed = this.getAllowedExtensions(excludedExtensions);

		return [
			...this.getFromExtensions("pasteRules", { schema }, allowed),
			...this.getFromNodesAndMarks("pasteRules", { schema }, allowed)
		].flat();
	}

	plugins({ schema }: { schema: Schema }) {
		return [
			...this.getFromExtensions("plugins", { schema }),
			...this.getFromNodesAndMarks("plugins", { schema })
		]
			.flat()
			.map((plugin) =>
				plugin instanceof Plugin ? plugin : new Plugin(plugin)
			);
	}
}
