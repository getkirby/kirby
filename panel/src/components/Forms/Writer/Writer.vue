<template>
	<div
		ref="editor"
		v-direction
		:data-disabled="disabled"
		:data-empty="isEmpty"
		:data-placeholder="placeholder"
		:data-toolbar-inline="Boolean(toolbar.inline)"
		:spellcheck="spellcheck"
		class="k-writer"
	>
		<k-writer-toolbar
			v-if="editor && !disabled"
			ref="toolbar"
			v-bind="toolbar"
			:editor="editor"
			@command="onCommand"
		/>
	</div>
</template>

<script>
import Editor from "./Editor";
import Mark from "./Mark";
import Node from "./Node";

// Marks
import Bold from "./Marks/Bold";
import Clear from "./Marks/Clear";
import Code from "./Marks/Code";
import Email from "./Marks/Email";
import Italic from "./Marks/Italic";
import Link from "./Marks/Link";
import Strike from "./Marks/Strike";
import Sup from "./Marks/Sup";
import Sub from "./Marks/Sub";
import Underline from "./Marks/Underline";

// Nodes
import BulletList from "./Nodes/BulletList";
import HardBreak from "./Nodes/HardBreak";
import Heading from "./Nodes/Heading";
import HorizontalRule from "./Nodes/HorizontalRule";
import ListItem from "./Nodes/ListItem";
import OrderedList from "./Nodes/OrderedList";
import Quote from "./Nodes/Quote";

// Extensions
import History from "./Extensions/History.js";
import Insert from "./Extensions/Insert.js";
import Keys from "./Extensions/Keys.js";
import Toolbar from "./Extensions/Toolbar.js";

export const props = {
	props: {
		autofocus: Boolean,
		breaks: Boolean,
		code: Boolean,
		disabled: Boolean,
		emptyDocument: {
			type: Object,
			default: () => ({
				type: "doc",
				content: []
			})
		},
		extensions: Array,
		headings: [Array, Boolean],
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
		placeholder: String,
		spellcheck: Boolean,
		toolbar: {
			type: Object,
			default: () => ({ inline: true })
		},
		value: {
			type: String,
			default: ""
		}
	}
};

export default {
	mixins: [props],
	data() {
		return {
			editor: null,
			json: {},
			html: this.value,
			isEmpty: true
		};
	},
	computed: {
		isCursorAtEnd() {
			return this.editor.selectionIsAtEnd;
		},
		isCursorAtStart() {
			return this.editor.selectionIsAtStart;
		}
	},
	watch: {
		value(newValue, oldValue) {
			if (newValue !== oldValue && newValue !== this.html) {
				this.html = newValue;
				this.editor.setContent(this.html);
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
	},
	beforeDestroy() {
		this.editor.destroy();
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

			// take each extenstion object and turn
			// it into an instance that extends the Mark class
			for (const name in plugins) {
				marks[name] = Object.create(
					Mark.prototype,
					Object.getOwnPropertyDescriptors(plugins[name])
				);
			}

			return marks;
		},
		createNodes() {
			const hardBreak = new HardBreak({
				text: true,
				enter: this.inline
			});

			// inline fields only get the hard break
			if (this.inline === true) {
				return [hardBreak];
			}

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

					// always install the hard break
					installed.push(hardBreak);

					return installed;
				}
			);
		},
		createNodesFromPanelPlugins() {
			const plugins = window.panel.plugins.writerNodes ?? {};
			const nodes = {};

			// take each extenstion object and turn
			// it into an instance that extends the Node class
			for (const name in plugins) {
				nodes[name] = Object.create(
					Node.prototype,
					Object.getOwnPropertyDescriptors(plugins[name])
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
		onCommand(command, ...args) {
			this.editor.command(command, ...args);
		}
	}
};
</script>

<style>
.k-writer {
	position: relative;
	width: 100%;
	display: grid;
	line-height: 1.5;
	grid-template-areas: "content";
	gap: var(--spacing-1);
}

.k-writer .ProseMirror {
	overflow-wrap: break-word;
	word-wrap: break-word;
	word-break: break-word;
	white-space: pre-wrap;
	-webkit-font-variant-ligatures: none;
	font-variant-ligatures: none;
	line-height: inherit;
	grid-area: content;
}
.k-writer .ProseMirror:focus {
	outline: 0;
}
.k-writer .ProseMirror * {
	caret-color: currentColor;
}
.k-writer .ProseMirror a {
	color: var(--color-focus);
	text-decoration: underline;
}
.k-writer .ProseMirror > *:last-child {
	margin-bottom: 0;
}
.k-writer .ProseMirror :where(p, ul, ol, h1, h2, h3) {
	margin-bottom: 0.75rem;
}
.k-writer .ProseMirror :where(p, ul, ol) {
	line-height: 1.5;
}

.k-writer .ProseMirror h1 {
	font-size: var(--text-3xl);
	line-height: 1.25em;
}
.k-writer .ProseMirror h2 {
	font-size: var(--text-2xl);
	line-height: 1.25em;
}
.k-writer .ProseMirror h3 {
	font-size: var(--text-xl);
	line-height: 1.25em;
}
.k-writer .ProseMirror :where(h1, h2, h3) strong {
	font-weight: 700;
}

.k-writer .ProseMirror strong {
	font-weight: 600;
}

.k-writer .ProseMirror :where(sup, sub) {
	font-size: var(--text-xs);
	line-height: 1;
}

.k-writer .ProseMirror code {
	position: relative;
	font-size: 0.925em;
	display: inline-block;
	line-height: 1.325;
	padding: 0.05em 0.325em;
	background: var(--color-gray-300);
	border-radius: var(--rounded);
	font-family: var(--font-mono);
}
.k-writer .ProseMirror :where(ul, ol) {
	padding-inline-start: 1.75rem;
}
.k-writer .ProseMirror ul > li {
	list-style: disc;
}
.k-writer .ProseMirror ul ul > li {
	list-style: circle;
}
.k-writer .ProseMirror ul ul ul > li {
	list-style: square;
}
.k-writer .ProseMirror ol > li {
	list-style: decimal;
}
.k-writer .ProseMirror li > :where(p, ol, ul) {
	margin: 0;
}

.k-writer .ProseMirror hr {
	border: 0;
	border-top: 2px solid var(--color-gray-300);
	border-radius: var(--rounded);
	margin-block: var(--spacing-1);
}
.k-writer .ProseMirror hr.ProseMirror-selectednode {
	outline: 2px var(--color-focus) solid;
}

.k-writer-code pre {
	tab-size: 2;
	font-size: var(--text-sm);
	line-height: 2em;
	overflow-x: auto;
	overflow-y: hidden;
	-webkit-overflow-scrolling: touch;
	white-space: pre;
}
.k-writer-code code {
	font-family: var(--font-mono);
}

.k-writer blockquote {
	font-size: var(--text-lg);
	margin-bottom: var(--spacing-1);
	line-height: 1.25em;
	padding-inline-start: var(--spacing-4);
	border-inline-start: 2px solid var(--color-black);
}

.k-writer[data-placeholder][data-empty="true"]::before {
	grid-area: content;
	content: attr(data-placeholder);
	color: var(--color-gray-500);
	pointer-events: none;
	white-space: pre-wrap;
	word-wrap: break-word;
	line-height: 1.5;
}
</style>
