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

export default {
	mixins: [props],
	computed: {
		commands() {
			const commands = {
				headlines: {
					label: this.$t("toolbar.button.headings"),
					icon: "title",
					dropdown: [
						{
							label: this.$t("toolbar.button.heading.1"),
							icon: "h1",
							click: () => this.$emit("command", "prepend", "#")
						},
						{
							label: this.$t("toolbar.button.heading.2"),
							icon: "h2",
							click: () => this.$emit("command", "prepend", "##")
						},
						{
							label: this.$t("toolbar.button.heading.3"),
							icon: "h3",
							click: () => this.$emit("command", "prepend", "###")
						}
					]
				},
				bold: {
					label: this.$t("toolbar.button.bold"),
					icon: "bold",
					click: () => this.$emit("command", "wrap", "**"),
					shortcut: "b"
				},
				italic: {
					label: this.$t("toolbar.button.italic"),
					icon: "italic",
					click: () => this.$emit("command", "wrap", "*"),
					shortcut: "i"
				},
				link: {
					label: this.$t("toolbar.button.link"),
					icon: "url",
					click: () => this.$emit("command", "dialog", "link"),
					shortcut: "k"
				},
				email: {
					label: this.$t("toolbar.button.email"),
					icon: "email",
					click: () => this.$emit("command", "dialog", "email"),
					shortcut: "e"
				},
				file: {
					label: this.$t("toolbar.button.file"),
					icon: "attachment"
				},
				code: {
					label: this.$t("toolbar.button.code"),
					icon: "code",
					click: () => this.$emit("command", "wrap", "`")
				},
				ul: {
					label: this.$t("toolbar.button.ul"),
					icon: "list-bullet",
					click: () =>
						this.$emit("command", "insert", (input, selection) =>
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
						this.$emit("command", "insert", (input, selection) =>
							selection
								.split("\n")
								.map((line, index) => index + 1 + ". " + line)
								.join("\n")
						)
				}
			};

			if (this.uploads === false) {
				commands.file.click = () => this.$emit("command", "dialog", "file");
			} else {
				commands.file.dropdown = [
					{
						label: this.$t("toolbar.button.file.select"),
						icon: "check",
						click: () => this.$emit("command", "dialog", "file")
					},
					{
						label: this.$t("toolbar.button.file.upload"),
						icon: "upload",
						click: () => this.$emit("command", "upload")
					}
				];
			}

			return commands;
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
				...Object(window.panel.plugins.textareaButtons ?? {})
			};

			for (const button of buttons) {
				if (button === "|") {
					layout.push("|");
				} else if (available[button]) {
					layout.push(available[button]);
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
</style>
