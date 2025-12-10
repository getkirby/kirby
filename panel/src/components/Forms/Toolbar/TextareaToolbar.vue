<template>
	<k-toolbar ref="toolbar" :buttons="layout" class="k-textarea-toolbar" />
</template>

<script>
export const props = {
	props: {
		buttons: {
			type: [Array, Boolean],
			default: true
		},
		/**
		 * Whether the toolbar's file upload button shows a dropdown with
		 * select and upload options or emits event directly
		 */
		uploads: [Boolean, Object, Array]
	}
};

/**
 * Toolbar for `k-textarea-input`
 * @since 4.0.0
 * @unstable
 */
export default {
	mixins: [props],
	emits: ["command"],
	computed: {
		commands() {
			return {
				headlines: {
					label: this.$t("toolbar.button.headings"),
					icon: "title",
					dropdown: [
						{
							label: this.$t("toolbar.button.heading.1"),
							icon: "h1",
							click: () => this.command("prepend", "#")
						},
						{
							label: this.$t("toolbar.button.heading.2"),
							icon: "h2",
							click: () => this.command("prepend", "##")
						},
						{
							label: this.$t("toolbar.button.heading.3"),
							icon: "h3",
							click: () => this.command("prepend", "###")
						}
					]
				},
				bold: {
					label: this.$t("toolbar.button.bold"),
					icon: "bold",
					click: () => this.command("toggle", "**"),
					shortcut: "b"
				},
				italic: {
					label: this.$t("toolbar.button.italic"),
					icon: "italic",
					click: () => this.command("toggle", "*"),
					shortcut: "i"
				},
				link: {
					label: this.$t("toolbar.button.link"),
					icon: "url",
					click: () => this.command("dialog", "link"),
					shortcut: "k"
				},
				email: {
					label: this.$t("toolbar.button.email"),
					icon: "email",
					click: () => this.command("dialog", "email"),
					shortcut: "e"
				},
				file: {
					label: this.$t("toolbar.button.file"),
					icon: "attachment",
					click: () => this.command("file"),
					dropdown: this.uploads
						? [
								{
									label: this.$t("toolbar.button.file.select"),
									icon: "check",
									click: () => this.command("file")
								},
								{
									label: this.$t("toolbar.button.file.upload"),
									icon: "upload",
									click: () => this.command("upload")
								}
							]
						: undefined
				},
				code: {
					label: this.$t("toolbar.button.code"),
					icon: "code",
					click: () => this.command("toggle", "`")
				},
				ul: {
					label: this.$t("toolbar.button.ul"),
					icon: "list-bullet",
					click: () =>
						this.command("insert", (input, selection) =>
							selection
								.split("\n")
								.map((line) => "- " + line)
								.join("\n")
						)
				},
				ol: {
					label: this.$t("toolbar.button.ol"),
					icon: "list-numbers",
					click: () =>
						this.command("insert", (input, selection) =>
							selection
								.split("\n")
								.map((line, index) => index + 1 + ". " + line)
								.join("\n")
						)
				}
			};
		},
		default() {
			return [
				"headlines",
				"|",
				"bold",
				"italic",
				"code",
				"|",
				"link",
				"email",
				"file",
				"|",
				"ul",
				"ol"
			];
		},
		layout() {
			if (this.buttons === false) {
				return [];
			}

			const layout = [];
			const buttons = Array.isArray(this.buttons) ? this.buttons : this.default;
			const available = {
				...this.commands,
				...(this.$panel.plugins.textareaButtons ?? {})
			};

			for (const button of buttons) {
				if (button === "|") {
					layout.push("|");
				} else if (available[button]) {
					const command = {
						...available[button],
						click: () => {
							available[button].click?.call(this);
						}
					};

					layout.push(command);
				}
			}

			return layout;
		}
	},
	methods: {
		/**
		 * Closes all dropdowns etc.
		 */
		close() {
			this.$refs.toolbar.close();
		},
		/**
		 * Emits command to textarea input component
		 *
		 * Supports the following commands:
		 * - `dialog` opens a dialog component
		 * - `insert` inserts the given text at the current selection
		 * - `prepend` prepends the given text to the current selection
		 * - `toggle` toggles wrapping of current selection (accepts before, after texts)
		 * - `upload` opens the file upload dialog
		 * - `wrap` wraps the current selection with the given text
		 */
		command(name, ...args) {
			this.$emit("command", name, ...args);
		},
		/**
		 * Looks up if any command responds to key shortcut
		 * and, if found, executes it
		 *
		 * @param {String} shortcut shortcut key name
		 * @param {Event} e
		 */
		shortcut(shortcut, e) {
			const command = this.layout.find(
				(button) => button.shortcut === shortcut
			);

			if (command) {
				e.preventDefault();
				command.click?.();
			}
		}
	}
};
</script>

<style>
.k-toolbar.k-textarea-toolbar {
	border-end-start-radius: 0;
	border-end-end-radius: 0;
	border-bottom: 1px solid var(--toolbar-border);
}
.k-toolbar.k-textarea-toolbar > .k-button:first-child {
	border-end-start-radius: 0;
}
.k-toolbar.k-textarea-toolbar > .k-button:last-child {
	border-end-end-radius: 0;
}
</style>
