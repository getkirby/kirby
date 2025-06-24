<template>
	<div
		ref="editor"
		v-direction
		:class="['k-writer', 'k-writer-input', $attrs.class]"
		:data-disabled="disabled"
		:data-empty="isEmpty"
		:data-placeholder="placeholder"
		:spellcheck="spellcheck"
		:style="$attrs.style"
	>
		<k-writer-toolbar
			v-if="editor && !disabled"
			ref="toolbar"
			v-bind="toolbarOptions"
			@command="onCommand"
		/>

		<textarea
			ref="output"
			:name="name"
			:required="required"
			:value="value"
			class="input-hidden"
			tabindex="-1"
		/>
	</div>
</template>

<script>
import Editor from "../Writer/Editor";
import Mark from "../Writer/Mark";
import Node from "../Writer/Node";

// Marks
import Bold from "../Writer/Marks/Bold";
import Clear from "../Writer/Marks/Clear";
import Code from "../Writer/Marks/Code";
import Email from "../Writer/Marks/Email";
import Italic from "../Writer/Marks/Italic";
import Link from "../Writer/Marks/Link";
import Strike from "../Writer/Marks/Strike";
import Sup from "../Writer/Marks/Sup";
import Sub from "../Writer/Marks/Sub";
import Underline from "../Writer/Marks/Underline";

// Nodes
import BulletList from "../Writer/Nodes/BulletList";
import HardBreak from "../Writer/Nodes/HardBreak";
import Heading from "../Writer/Nodes/Heading";
import HorizontalRule from "../Writer/Nodes/HorizontalRule";
import ListItem from "../Writer/Nodes/ListItem";
import OrderedList from "../Writer/Nodes/OrderedList";
import Quote from "../Writer/Nodes/Quote";

// Extensions
import History from "../Writer/Extensions/History.js";
import Insert from "../Writer/Extensions/Insert.js";
import Keys from "../Writer/Extensions/Keys.js";
import Toolbar from "../Writer/Extensions/Toolbar.js";

// Input
import Input, { props as InputProps } from "@/mixins/input.js";
import {
	maxlength,
	minlength,
	placeholder,
	spellcheck
} from "@/mixins/props.js";

export const props = {
	mixins: [InputProps, maxlength, minlength, placeholder, spellcheck],
	props: {
		breaks: Boolean,
		code: Boolean,
		emptyDocument: {
			type: Object,
			default: () => ({
				type: "doc",
				content: []
			})
		},
		extensions: Array,
		headings: {
			default: () => [1, 2, 3, 4, 5, 6],
			type: [Array, Boolean]
		},
		inline: Boolean,
		keys: Object,
		marks: {
			type: [Array, Boolean],
			default: true
		},
		nodes: {
			type: [Array, Boolean],
			default: () => ["heading", "bulletList", "orderedList"]
		},
		paste: {
			type: Function,
			default: () => () => false
		},
		/**
		 * See `k-writer-toolbar` for available options
		 */
		toolbar: {
			type: Object,
			default: () => ({
				inline: true
			})
		},
		value: {
			type: String,
			default: ""
		}
	}
};

export default {
	mixins: [Input, props],
	emits: ["input"],
	data() {
		return {
			editor: null,
			json: {},
			html: this.value,
			isEmpty: true
		};
	},
	computed: {
		characters() {
			const plain = this.$helper.string.stripHTML(this.value ?? "");
			return this.$helper.string.unescapeHTML(plain).length;
		},
		isCursorAtEnd() {
			return this.editor.selectionIsAtEnd;
		},
		isCursorAtStart() {
			return this.editor.selectionIsAtStart;
		},
		toolbarOptions() {
			return {
				// if custom set of marks is enabled, use as toolbar default as well
				marks:
					Array.isArray(this.marks) || this.marks === false
						? this.marks
						: undefined,
				...this.toolbar,
				editor: this.editor
			};
		}
	},
	watch: {
		value(newValue, oldValue) {
			if (newValue !== oldValue && newValue !== this.html) {
				this.html = newValue;
				this.editor.setContent(this.html);
				this.isEmpty = this.editor.isEmpty();
			}
		}
	},
	mounted() {
		this.editor = new Editor({
			autofocus: this.autofocus,
			content: this.value,
			editable: !this.disabled,
			element: this.$el,
			emptyDocument: this.emptyDocument,
			parseOptions: {
				preserveWhitespace: true
			},
			events: {
				link: (editor) => {
					this.$panel.dialog.open({
						component: "k-link-dialog",
						props: {
							value: editor.getMarkAttrs("link")
						},
						on: {
							cancel: () => editor.focus(),
							submit: (values) => {
								this.$panel.dialog.close();
								editor.command("toggleLink", values);
							}
						}
					});
				},
				email: (editor) => {
					this.$panel.dialog.open({
						component: "k-email-dialog",
						props: {
							value: this.editor.getMarkAttrs("email")
						},
						on: {
							cancel: () => editor.focus(),
							submit: (values) => {
								this.$panel.dialog.close();
								editor.command("toggleEmail", values);
							}
						}
					});
				},
				paste: this.paste,
				update: (payload) => {
					if (!this.editor) {
						return;
					}

					// compare documents to avoid minor HTML differences
					// to cause unwanted updates
					const jsonNew = JSON.stringify(this.editor.getJSON());
					const jsonOld = JSON.stringify(this.json);

					if (jsonNew === jsonOld) {
						return;
					}

					this.json = jsonNew;
					this.isEmpty = payload.editor.isEmpty();

					// create the final HTML to send to the server
					this.html = payload.editor.getHTML();

					// when a new list item or heading is created,
					// textContent length returns 0.
					// checking active nodes to prevent this issue.
					// Empty input is no nodes or just the paragraph node
					// and its length 0
					if (
						this.isEmpty &&
						(payload.editor.activeNodes.length === 0 ||
							payload.editor.activeNodes.includes("paragraph"))
					) {
						this.html = "";
					}

					this.$emit("input", this.html);
					this.validate();
				}
			},
			extensions: [
				...this.createMarks(),
				...this.createNodes(),

				// Extensions
				new Keys(this.keys),
				new History(),
				new Insert(),
				new Toolbar(this),
				...(this.extensions || [])
			],
			inline: this.inline
		});

		this.isEmpty = this.editor.isEmpty();
		this.json = this.editor.getJSON();

		this.$panel.events.on("click", this.onBlur);
		this.$panel.events.on("focus", this.onBlur);

		this.validate();

		if (this.$props.autofocus) {
			this.focus();
		}
	},
	beforeDestroy() {
		this.editor.destroy();
		this.$panel.events.off("click", this.onBlur);
		this.$panel.events.off("focus", this.onBlur);
	},
	methods: {
		command(command, ...args) {
			this.editor.command(command, ...args);
		},
		createMarks() {
			return this.filterExtensions(
				{
					clear: new Clear(),
					code: new Code(),
					underline: new Underline(),
					strike: new Strike(),
					link: new Link(),
					email: new Email(),
					bold: new Bold(),
					italic: new Italic(),
					sup: new Sup(),
					sub: new Sub(),
					...this.createMarksFromPanelPlugins()
				},
				this.marks
			);
		},
		createMarksFromPanelPlugins() {
			const plugins = window.panel.plugins.writerMarks ?? {};
			const marks = {};

			// take each extension object and turn
			// it into an instance that extends the Mark class
			for (const name in plugins) {
				marks[name] = Object.create(
					Mark.prototype,
					Object.getOwnPropertyDescriptors({ name, ...plugins[name] })
				);
			}

			return marks;
		},
		createNodes() {
			const hardBreak = new HardBreak({
				text: true,
				enter: this.inline
			});

			return this.filterExtensions(
				{
					bulletList: new BulletList(),
					orderedList: new OrderedList(),
					heading: new Heading({ levels: this.headings }),
					horizontalRule: new HorizontalRule(),
					listItem: new ListItem(),
					quote: new Quote(),
					...this.createNodesFromPanelPlugins()
				},
				this.nodes,
				(allowed, installed) => {
					// install the list item when there's a list available
					if (
						allowed.includes("bulletList") ||
						allowed.includes("orderedList")
					) {
						installed.push(new ListItem());
					}

					// inline fields should not have non-inline nodes
					if (this.inline === true) {
						installed = installed.filter((node) => node.schema.inline === true);
					}

					// always install the hard break
					installed.push(hardBreak);

					return installed;
				}
			);
		},
		createNodesFromPanelPlugins() {
			const plugins = window.panel.plugins.writerNodes ?? {};
			const nodes = {};

			// take each extension object and turn
			// it into an instance that extends the Node class
			for (const name in plugins) {
				nodes[name] = Object.create(
					Node.prototype,
					Object.getOwnPropertyDescriptors({ name, ...plugins[name] })
				);
			}

			return nodes;
		},
		getHTML() {
			return this.editor.getHTML();
		},
		filterExtensions(available, allowed, postFilter) {
			if (allowed === false) {
				allowed = [];
			} else if (allowed === true || Array.isArray(allowed) === false) {
				allowed = Object.keys(available);
			}

			let installed = [];

			for (const extension in available) {
				if (allowed.includes(extension)) {
					installed.push(available[extension]);
				}
			}

			if (typeof postFilter === "function") {
				installed = postFilter(allowed, installed);
			}

			return installed;
		},
		focus() {
			this.editor.focus();
		},
		getSplitContent() {
			return this.editor.getHTMLStartToSelectionToEnd();
		},
		onBlur(event) {
			if (this.$el.contains(event.target) === false) {
				this.$refs.toolbar?.close();
			}
		},
		onCommand(command, ...args) {
			this.editor.command(command, ...args);
		},
		async validate() {
			// add short delay to ensure the editor has updated its content
			await new Promise((resolve) => setTimeout(() => resolve(""), 50));

			let error = "";

			if (
				this.isEmpty === false &&
				this.minlength &&
				this.characters < this.minlength
			) {
				error = this.$t("error.validation.minlength", {
					min: this.minlength
				});
			} else if (
				this.isEmpty === false &&
				this.maxlength &&
				this.characters > this.maxlength
			) {
				error = this.$t("error.validation.maxlength", {
					max: this.maxlength
				});
			}

			this.$refs.output?.setCustomValidity(error);
		}
	}
};
</script>

<style>
.k-writer-input {
	position: relative;
	width: 100%;
	display: grid;
	grid-template-areas: "content";
	gap: var(--spacing-1);
}

.k-writer-input .ProseMirror {
	overflow-wrap: break-word;
	word-wrap: break-word;
	word-break: break-word;
	white-space: pre-wrap;
	font-variant-ligatures: none;
	grid-area: content;
	padding: var(--input-padding-multiline);
}
.k-writer-input .ProseMirror:focus {
	outline: 0;
}
.k-writer-input .ProseMirror * {
	caret-color: currentColor;
}

.k-writer-input .ProseMirror hr.ProseMirror-selectednode {
	outline: var(--outline);
}

.k-writer-input[data-placeholder][data-empty="true"]::before {
	grid-area: content;
	content: attr(data-placeholder);
	color: var(--input-color-placeholder);
	pointer-events: none;
	white-space: pre-wrap;
	word-wrap: break-word;
	line-height: var(--text-line-height);
	padding: var(--input-padding-multiline);
}
</style>
