<template>
	<div :data-over="over" :data-size="size" class="k-textarea-input">
		<div class="k-textarea-input-wrapper">
			<k-toolbar
				v-if="buttons && !disabled"
				ref="toolbar"
				:buttons="buttons"
				:disabled="disabled"
				:can-upload="uploads"
				@mousedown.native.prevent
				@command="onCommand"
			/>
			<k-autosize>
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
				/>
			</k-autosize>
		</div>
	</div>
</template>

<script>
import { props as ToolbarProps } from "@/components/Forms/Toolbar.vue";
import Input, { props as InputProps } from "@/mixins/input.js";
import {
	font,
	maxlength,
	minlength,
	placeholder,
	spellcheck
} from "@/mixins/props.js";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength
} from "vuelidate/lib/validators";

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
		theme: String,
		value: String
	}
};

/**
 * @example <k-input :value="text" @input="text = $event" name="text" type="textarea" />
 */
export default {
	mixins: [Input, props],
	data() {
		return {
			over: false
		};
	},
	computed: {
		uploadOptions() {
			return {
				url: this.$panel.urls.api + "/" + this.endpoints.field + "/upload",
				multiple: false,
				on: { done: this.insertUpload }
			};
		}
	},
	watch: {
		value() {
			this.onInvalid();
			this.$nextTick(() => {
				this.$refs.input.autosize();
			});
		}
	},
	mounted() {
		this.onInvalid();

		if (this.$props.autofocus) {
			this.focus();
		}

		if (this.$props.preselect) {
			this.select();
		}
	},
	methods: {
		dialog(dialog) {
			// store selection
			const start = this.$refs.input.selectionStart;
			const end = this.$refs.input.selectionEnd;

			// restore selection as `insert` method
			// depends on it
			const restoreSelection = (callback) => {
				setTimeout(() => {
					this.$refs.input.setSelectionRange(start, end);

					if (callback) {
						callback();
					}
				});
			};

			this.$panel.dialog.open({
				component: "k-toolbar-" + dialog + "-dialog",
				props: {
					value: this.parseSelection()
				},
				on: {
					cancel: restoreSelection,
					submit: (text) => {
						this.$panel.dialog.close();
						restoreSelection(() => this.insert(text));
					}
				}
			});
		},
		focus() {
			this.$refs.input.focus();
		},
		insert(text) {
			const input = this.$refs.input;

			setTimeout(() => {
				input.focus();
				input.setRangeText(text, input.selectionStart, input.selectionEnd);
				this.$emit("input", input.value);
			});
		},
		insertFile(files) {
			if (files?.length > 0) {
				this.insert(files.map((file) => file.dragText).join("\n\n"));
			}
		},
		insertUpload(files) {
			this.insertFile(files);
			this.$events.emit("model.update");
		},
		onCommand(command, callback) {
			if (typeof this[command] !== "function") {
				return console.warn(command + " is not a valid command");
			}

			if (typeof callback === "function") {
				callback = callback(this.$refs.input, this.selection());
			}

			this[command](callback);
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
		onInvalid() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
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
			} else {
				return {
					href: null,
					title: selection
				};
			}
		},
		prepend(prepend) {
			this.insert(prepend + " " + this.selection());
		},
		select() {
			this.$refs.select();
		},
		selectFile() {
			this.$panel.dialog.open({
				component: "k-files-dialog",
				props: {
					endpoint: this.endpoints.field + "/files",
					multiple: false
				},
				on: {
					cancel: this.cancel,
					submit: (file) => {
						this.insertFile(file);
						this.$panel.dialog.close();
					}
				}
			});
		},
		selection() {
			return this.$refs.input.value.substring(
				this.$refs.input.selectionStart,
				this.$refs.input.selectionEnd
			);
		},
		uploadFile() {
			this.$panel.upload.pick(this.uploadOptions);
		},
		wrap(text) {
			this.insert(text + this.selection() + text);
		}
	},
	validations() {
		return {
			value: {
				required: this.required ? validateRequired : true,
				minLength: this.minlength ? validateMinLength(this.minlength) : true,
				maxLength: this.maxlength ? validateMaxLength(this.maxlength) : true
			}
		};
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
