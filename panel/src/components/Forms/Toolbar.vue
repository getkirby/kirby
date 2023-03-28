<template>
	<nav class="k-toolbar">
		<template v-for="(button, buttonIndex) in layout">
			<!-- divider -->
			<div
				v-if="button.divider"
				:key="buttonIndex + '-divider'"
				class="k-toolbar-divider"
			/>

			<!-- dropdown -->
			<k-dropdown v-else-if="button.dropdown" :key="buttonIndex + '-dropdown'">
				<k-button
					:key="buttonIndex"
					:icon="button.icon"
					:title="button.label"
					tabindex="-1"
					class="k-toolbar-button"
					@click="$refs[buttonIndex + '-dropdown'][0].toggle()"
				/>
				<k-dropdown-content :ref="buttonIndex + '-dropdown'">
					<k-dropdown-item
						v-for="(dropdownItem, dropdownItemIndex) in button.dropdown"
						:key="dropdownItemIndex"
						:icon="dropdownItem.icon"
						@click="command(dropdownItem.command, dropdownItem.args)"
					>
						{{ dropdownItem.label }}
					</k-dropdown-item>
				</k-dropdown-content>
			</k-dropdown>

			<!-- single button -->
			<k-button
				v-else
				:key="buttonIndex + '-button'"
				:icon="button.icon"
				:title="button.label"
				tabindex="-1"
				class="k-toolbar-button"
				@click="command(button.command, button.args)"
			/>
		</template>
	</nav>
</template>

<script>
const list = function (type) {
	this.command("insert", (input, selection) => {
		let html = [];

		selection.split("\n").forEach((line, index) => {
			let prepend = type === "ol" ? index + 1 + "." : "-";
			html.push(prepend + " " + line);
		});

		return html.join("\n");
	});
};

export default {
	layout: [
		"headlines",
		"bold",
		"italic",
		"|",
		"link",
		"email",
		"file",
		"|",
		"code",
		"ul",
		"ol"
	],
	props: {
		buttons: {
			type: [Boolean, Array],
			default: true
		},
		uploads: [Boolean, Object, Array]
	},
	data() {
		let layout = {};
		let shortcuts = {};
		let buttons = [];
		let commands = this.commands();

		if (this.buttons === false) {
			return layout;
		}

		if (Array.isArray(this.buttons)) {
			buttons = this.buttons;
		}

		if (Array.isArray(this.buttons) !== true) {
			buttons = this.$options.layout;
		}

		buttons.forEach((item, index) => {
			if (item === "|") {
				layout["divider-" + index] = { divider: true };
			} else if (commands[item]) {
				let button = commands[item];
				layout[item] = button;

				if (button.shortcut) {
					shortcuts[button.shortcut] = item;
				}
			}
		});

		// inject custom textarea buttons
		const customButtons = window.panel.plugins.textareaButtons ?? {};

		if (this.buttons === true && Object.keys(customButtons).length) {
			layout["divider-custom-buttons"] = { divider: true };
		}

		Object.keys(customButtons).forEach((name) => {
			const button = customButtons[name];

			// check required props for the button
			if (
				!button.label ||
				!button.icon ||
				(!button.command && !button.dropdown)
			) {
				return;
			}

			layout[name] = button;

			if (button.shortcut) {
				shortcuts[button.shortcut] = name;
			}
		});

		return {
			layout: layout,
			shortcuts: shortcuts
		};
	},
	methods: {
		command(command, callback) {
			if (typeof command === "function") {
				command.apply(this);
			} else {
				this.$emit("command", command, callback);
			}
		},
		close() {
			for (const ref in this.$refs) {
				const component = this.$refs[ref][0];

				if (typeof component?.close === "function") {
					component.close();
				}
			}
		},
		fileCommandSetup() {
			let command = {
				label: this.$t("toolbar.button.file"),
				icon: "attachment"
			};

			if (this.uploads === false) {
				command.command = "selectFile";
			} else {
				command.dropdown = {
					select: {
						label: this.$t("toolbar.button.file.select"),
						icon: "check",
						command: "selectFile"
					},
					upload: {
						label: this.$t("toolbar.button.file.upload"),
						icon: "upload",
						command: "uploadFile"
					}
				};
			}

			return command;
		},
		commands() {
			return {
				headlines: {
					label: this.$t("toolbar.button.headings"),
					icon: "title",
					dropdown: {
						h1: {
							label: this.$t("toolbar.button.heading.1"),
							icon: "h1",
							command: "prepend",
							args: "#"
						},
						h2: {
							label: this.$t("toolbar.button.heading.2"),
							icon: "h2",
							command: "prepend",
							args: "##"
						},
						h3: {
							label: this.$t("toolbar.button.heading.3"),
							icon: "h3",
							command: "prepend",
							args: "###"
						}
					}
				},
				bold: {
					label: this.$t("toolbar.button.bold"),
					icon: "bold",
					command: "wrap",
					args: "**",
					shortcut: "b"
				},
				italic: {
					label: this.$t("toolbar.button.italic"),
					icon: "italic",
					command: "wrap",
					args: "*",
					shortcut: "i"
				},
				link: {
					label: this.$t("toolbar.button.link"),
					icon: "url",
					shortcut: "k",
					command: "dialog",
					args: "link"
				},
				email: {
					label: this.$t("toolbar.button.email"),
					icon: "email",
					shortcut: "e",
					command: "dialog",
					args: "email"
				},
				file: this.fileCommandSetup(),
				code: {
					label: this.$t("toolbar.button.code"),
					icon: "code",
					command: "wrap",
					args: "`"
				},
				ul: {
					label: this.$t("toolbar.button.ul"),
					icon: "list-bullet",
					command: () => list.apply(this, ["ul"])
				},
				ol: {
					label: this.$t("toolbar.button.ol"),
					icon: "list-numbers",
					command: () => list.apply(this, ["ol"])
				}
			};
		},
		shortcut(shortcut, $event) {
			if (this.shortcuts[shortcut]) {
				const button = this.layout[this.shortcuts[shortcut]];

				if (!button) {
					return false;
				}

				$event.preventDefault();

				this.command(button.command, button.args);
			}
		}
	}
};
</script>

<style>
.k-toolbar {
	--toolbar-size: 38px;
	--toolbar-text: #aaa;
	--toolbar-back: var(--color-white);
	--toolbar-hover: rgba(239, 239, 239, 0.5);
	--toolbar-border: var(--color-background);

	display: flex;
	max-width: 100%;
	height: var(--toolbar-size);
	margin-bottom: var(--spacing-1);

	color: var(--toolbar-text);
	background: var(--toolbar-back);
	border-start-start-radius: var(--rounded);
	border-start-end-radius: var(--rounded);
	border-bottom: 1px solid var(--toolbar-border);
}

.k-toolbar-divider {
	width: 1px;
	border-left: 1px solid var(--toolbar-border);
}

.k-toolbar-button {
	--button-width: var(--toolbar-size);
	--button-height: var(--toolbar-size);
}
.k-toolbar-button:hover {
	--button-color-hover: var(--toolbar-hover);
}

.k-toolbar:not([data-inline="true"]):has(~ :focus-within) {
	position: sticky;
	top: calc(var(--header-bar-height) + var(--spacing-1) + 3px);
	margin-bottom: var(--height-md);
	inset-inline: 0;
	z-index: 1;

	--toolbar-text: var(--color-black);
	--toolbar-border: rgba(0, 0, 0, 0.1);
	box-shadow: rgba(0, 0, 0, 0.05) 0 2px 5px;
}
</style>
