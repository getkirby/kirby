<template>
	<div
		:class="['k-textarea-input', $attrs.class]"
		:data-over="over"
		:data-size="size"
		:style="$attrs.style"
	>
		<div class="k-textarea-input-wrapper">
			<k-textarea-toolbar
				v-if="buttons && !disabled"
				ref="toolbar"
				:buttons="buttons"
				:disabled="disabled"
				:uploads="uploads"
				@mousedown.prevent
				@command="onCommand"
			/>
			<textarea
				ref="input"
				v-bind="{
					autofocus,
					disabled,
					id,
					minlength,
					name,
					placeholder,
					required,
					spellcheck,
					value
				}"
				v-direction
				:data-font="font"
				class="k-textarea-input-native"
				@click="$refs.toolbar?.close()"
				@focus="onFocus"
				@input="onInput"
				@keydown.meta.enter="onSubmit"
				@keydown.ctrl.enter="onSubmit"
				@keydown.meta.exact="onShortcut"
				@keydown.ctrl.exact="onShortcut"
				@dragover="onOver"
				@dragleave="onOut"
				@drop="onDrop"
				@selectionchange="onSelectionChange"
			/>
		</div>
	</div>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { props as ToolbarProps } from "@/components/Forms/Toolbar/TextareaToolbar.vue";

import {
	font,
	maxlength,
	minlength,
	placeholder,
	spellcheck
} from "@/mixins/props.js";

export const props = {
	mixins: [
		ToolbarProps,
		InputProps,
		font,
		maxlength,
		minlength,
		placeholder,
		spellcheck
	],
	props: {
		endpoints: Object,
		preselect: Boolean,
		/**
		 * Pre-selects the size before auto-sizing kicks in.
		 * This can be useful to fill gaps in field layouts.
		 * @values small, medium, large, huge
		 */
		size: String,
		value: String
	}
};

/**
 * @example <k-input :value="text" @input="text = $event" name="text" type="textarea" />
 */
export default {
	mixins: [Input, props],
	emits: ["focus", "input", "submit"],
	data() {
		return {
			over: false,
			selectionRange: null
		};
	},
	computed: {
		uploadOptions() {
			return {
				url: this.$panel.urls.api + "/" + this.endpoints.field + "/upload",
				multiple: false,
				on: {
					cancel: async () => await this.restoreSelection(),
					done: async (files) => {
						await this.restoreSelection();
						await this.insertUpload(files);
					}
				}
			};
		}
	},
	watch: {
		async value() {
			await this.$nextTick();
			this.$library.autosize.update(this.$refs.input);
		}
	},
	async mounted() {
		await this.$nextTick();
		this.$library.autosize(this.$refs.input);

		if (this.$props.autofocus) {
			this.focus();
		}

		if (this.$props.preselect) {
			this.select();
		}
	},
	methods: {
		dialog(dialog) {
			this.$panel.dialog.open({
				component: "k-toolbar-" + dialog + "-dialog",
				props: {
					value: this.parseSelection()
				},
				on: {
					cancel: async () => await this.restoreSelection(),
					submit: async (text) => {
						this.$panel.dialog.close();
						await this.restoreSelection();
						await this.insert(text);
					}
				}
			});
		},
		file() {
			this.$panel.dialog.open({
				component: "k-files-dialog",
				props: {
					endpoint: this.endpoints.field + "/files",
					multiple: false
				},
				on: {
					cancel: async () => await this.restoreSelection(),
					submit: async (file) => {
						this.$panel.dialog.close();
						await this.restoreSelection();
						await this.insertFile(file);
					}
				}
			});
		},
		focus() {
			this.$refs.input.focus();
		},
		async insert(text) {
			const input = this.$refs.input;
			const current = input.value;

			if (typeof text === "function") {
				text = text(input, this.selection());
			}

			this.focus();

			// try first via execCommand as this will be considered
			// as a user action and can be undone by the browser's
			// native undo function
			document.execCommand("insertText", false, text);

			if (input.value === current) {
				const { start, end } = this.selectionRange;

				const mode = start === end ? "end" : "select";
				input.setRangeText(text, start, end, mode);
			}

			this.$emit("input", input.value);

			return input.value;
		},
		async insertFile(files) {
			if (files?.length > 0) {
				await this.insert(files.map((file) => file.dragText).join("\n\n"));
			}
		},
		async insertUpload(files) {
			await this.insertFile(files);
			// `$panel.content.update()` cancels the previously
			// started lazy save request from the emitted `input`
			// event in `insertFile` > `insert` and reloads the view
			// after the request went through.
			await this.$panel.content.update();
		},
		onCommand(command, ...args) {
			if (typeof this[command] !== "function") {
				return console.warn(command + " is not a valid command");
			}

			this[command](...args);
		},
		onDrop($event) {
			// dropping files
			if (this.uploads && this.$helper.isUploadEvent($event)) {
				return this.$panel.upload.open(
					$event.dataTransfer.files,
					this.uploadOptions
				);
			}

			// dropping text
			if (this.$panel.drag.type === "text") {
				this.focus();
				this.insert(this.$panel.drag.data);
			}
		},
		onFocus($event) {
			this.$emit("focus", $event);
		},
		onInput($event) {
			this.$emit("input", $event.target.value);
		},
		onOut() {
			this.$refs.input.blur();
			this.over = false;
		},
		onOver($event) {
			// drag & drop for files
			if (this.uploads && this.$helper.isUploadEvent($event)) {
				$event.dataTransfer.dropEffect = "copy";
				this.focus();
				this.over = true;
				return;
			}

			// drag & drop for text
			if (this.$panel.drag.type === "text") {
				$event.dataTransfer.dropEffect = "copy";
				this.focus();
				this.over = true;
			}
		},
		onSelectionChange() {
			this.selectionRange = {
				start: this.$refs.input.selectionStart,
				end: this.$refs.input.selectionEnd
			};
		},
		onShortcut($event) {
			if (
				this.buttons !== false &&
				$event.key !== "Meta" &&
				$event.key !== "Control"
			) {
				this.$refs.toolbar?.shortcut($event.key, $event);
			}
		},
		onSubmit($event) {
			return this.$emit("submit", $event);
		},
		parseSelection() {
			const selection = this.selection();

			if (selection?.length === 0) {
				return {
					href: null,
					title: null
				};
			}

			let regex;
			if (this.$panel.config.kirbytext) {
				regex = /^\(link:\s*(?<url>.*?)(?:\s*text:\s*(?<text>.*?))?\)$/is;
			} else {
				regex = /^(\[(?<text>.*?)\]\((?<url>.*?)\))|(<(?<link>.*?)>)$/is;
			}

			const matches = regex.exec(selection);

			if (matches !== null) {
				return {
					href: matches.groups.url ?? matches.groups.link,
					title: matches.groups.text ?? null
				};
			}

			return {
				href: null,
				title: selection
			};
		},
		async prepend(text) {
			return this.insert(text + " " + this.selection());
		},
		async restoreSelection() {
			if (this.selectionRange) {
				this.$refs.input.setSelectionRange(
					this.selectionRange.start,
					this.selectionRange.end
				);
			}

			await this.$nextTick();
		},
		/**
		 * @deprecated 5.0.0 Use `restoreSelection` instead
		 */
		restoreSelectionCallback() {
			// restore selection as `insert` method
			// depends on it
			return async (callback) => {
				await this.restoreSelection();

				if (callback) {
					callback();
				}
			};
		},
		select() {
			this.$refs.select();
		},
		selection() {
			if (!this.selectionRange) {
				return "";
			}

			const { start, end } = this.selectionRange;
			return this.$refs.input.value.substring(start, end);
		},
		async toggle(before, after) {
			after ??= before;
			const selection = this.selection();

			if (selection.startsWith(before) && selection.endsWith(after)) {
				return this.insert(
					selection
						.slice(before.length)
						.slice(0, selection.length - before.length - after.length)
				);
			}

			return this.wrap(before, after);
		},
		upload() {
			this.$panel.upload.pick(this.uploadOptions);
		},
		async wrap(before, after) {
			after ??= before;
			await this.insert(before + this.selection() + after);
		}
	}
};
</script>

<style>
.k-textarea-input[data-size="small"] {
	--textarea-size: 7.5rem;
}
.k-textarea-input[data-size="medium"] {
	--textarea-size: 15rem;
}
.k-textarea-input[data-size="large"] {
	--textarea-size: 30rem;
}
.k-textarea-input[data-size="huge"] {
	--textarea-size: 45rem;
}
.k-textarea-input-wrapper {
	position: relative;
	display: block;
}
.k-textarea-input-native {
	resize: none;
	min-height: var(--textarea-size);
}
.k-textarea-input-native:focus {
	outline: 0;
}
.k-textarea-input-native[data-font="monospace"] {
	font-family: var(--font-mono);
}

/* Input Context */
.k-input[data-type="textarea"] .k-input-element {
	min-width: 0;
}
.k-input[data-type="textarea"] .k-textarea-input-native {
	padding: var(--input-padding-multiline);
}
</style>
