<template>
	<k-draggable
		v-direction
		:list="tags"
		:options="dragOptions"
		:data-layout="layout"
		class="k-tags-input"
		@end="onInput"
	>
		<k-tag
			v-for="tag in tags"
			:ref="tag.value"
			:key="tag.value"
			:removable="!disabled"
			name="tag"
			@click.native.stop
			@blur.native="selectTag(null)"
			@focus.native="selectTag(tag)"
			@keydown.enter.native="edit(tag)"
			@keydown.native.left="navigate('prev')"
			@keydown.native.right="navigate('next')"
			@dblclick.native="edit(tag)"
			@remove="remove(tag)"
		>
			<!-- eslint-disable-next-line vue/no-v-html -->
			<span v-html="tag.text" />
		</k-tag>

		<template #footer>
			<k-select-dropdown
				ref="selector"
				:options="dropdownOptions"
				@create="addString($event)"
				@select="addTag($event)"
			>
				<k-button
					ref="toggle"
					icon="plus"
					class="k-tags-input-toggle"
					size="xs"
					variant="filled"
					@click.native="$refs.selector.open()"
					@keydown.native="search"
					@keydown.native.delete="navigate('last')"
					@keydown.native.left="navigate('last')"
				/>
			</k-select-dropdown>
		</template>
	</k-draggable>
</template>

<script>
import { autofocus, disabled, id, name, required } from "@/mixins/props.js";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength
} from "vuelidate/lib/validators";

export const props = {
	mixins: [autofocus, disabled, id, name, required],
	props: {
		accept: {
			type: String,
			default: "all"
		},
		icon: {
			type: [String, Boolean],
			default: "tag"
		},
		/**
		 * You can set the layout to `list` to extend the width of each tag
		 * to 100% and show them in a list. This is handy in narrow columns
		 * or when a list is a more appropriate design choice for the input
		 * in general.
		 */
		layout: String,
		/**
		 * The maximum number of accepted tags
		 */
		max: Number,
		/**
		 * The minimum number of required tags
		 */
		min: Number,
		/**
		 * Options will be shown in the autocomplete dropdown
		 * as soon as you start typing.
		 */
		options: {
			type: Array,
			default: () => []
		},
		separator: {
			type: String,
			default: ","
		},
		value: {
			type: Array,
			default: () => []
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	data() {
		return {
			tags: this.toValues(this.value),
			selected: null
		};
	},
	computed: {
		dragOptions() {
			return {
				delay: 1,
				disabled: !this.draggable,
				draggable: ".k-tag"
			};
		},
		draggable() {
			return this.tags.length > 1;
		},
		dropdownOptions() {
			return this.options.filter((option) => {
				return this.value.includes(option.value) === false;
			});
		},
		skip() {
			return this.tags.map((tag) => tag.value);
		}
	},
	watch: {
		value(value) {
			this.tags = this.toValues(value);
			this.onInvalid();
		}
	},
	mounted() {
		this.onInvalid();

		if (this.$props.autofocus) {
			this.focus();
		}
	},
	methods: {
		addString(string, focus = true) {
			if (!string) {
				return;
			}

			string = string.trim();

			if (string.length === 0) {
				return;
			}

			if (string.includes(this.separator) === true) {
				for (const tag of string.split(this.separator)) {
					this.addString(tag);
				}

				return;
			}

			const tag = this.toValue(string);

			if (tag) {
				this.addTag(tag, focus);
			}
		},
		addTag(tag, focus = true) {
			this.addTagToIndex(tag);
			this.$refs.selector.close();

			if (focus) {
				this.focus();
			}
		},
		addTagToIndex(tag) {
			if (this.accept === "options") {
				const option = this.options.find(
					(option) => option.value === tag.value
				);

				if (!option) {
					return;
				}
			}

			if (
				this.index(tag) === -1 &&
				(!this.max || this.tags.length < this.max)
			) {
				this.tags.push(tag);
				this.onInput();
			}
		},
		edit(tag) {
			this.$panel.dialog.open({
				component: "k-form-dialog",
				props: {
					fields: {
						tag: {
							label: "Tag",
							type: "text",
							icon: "tag"
						}
					},
					submitButton: this.$t("change"),
					value: {
						tag: this.$helper.string.unescapeHTML(tag.text)
					}
				},
				on: {
					submit: (value) => {
						const index = this.index(tag);
						const updated = this.toValue(value);

						this.$set(this.tags, index, updated);
						this.onInput();
						this.$panel.dialog.close();
					}
				}
			});
		},
		focus() {
			this.$refs.toggle.focus();
		},
		get(position) {
			let nextIndex = null;
			let currIndex = null;

			switch (position) {
				case "prev":
					if (!this.selected) return;

					currIndex = this.index(this.selected);
					nextIndex = currIndex - 1;

					if (nextIndex < 0) return;
					break;

				case "next":
					if (!this.selected) return;

					currIndex = this.index(this.selected);
					nextIndex = currIndex + 1;

					if (nextIndex >= this.tags.length) return;
					break;

				case "first":
					nextIndex = 0;
					break;

				case "last":
					nextIndex = this.tags.length - 1;
					break;

				default:
					nextIndex = position;
					break;
			}

			let nextTag = this.tags[nextIndex];

			if (nextTag) {
				let nextRef = this.$refs[nextTag.value];

				if (nextRef?.[0]) {
					return {
						ref: nextRef[0],
						tag: nextTag,
						index: nextIndex
					};
				}
			}

			return false;
		},
		index(tag) {
			return this.tags.findIndex((item) => item.value === tag.value);
		},
		navigate(position) {
			var result = this.get(position);
			if (result) {
				result.ref.focus();
				this.selectTag(result.tag);
			} else if (position === "next") {
				this.focus();
				this.selectTag(null);
			}
		},
		onInput() {
			// make sure to only emit values
			const tags = this.tags.map((tag) => tag.value);
			this.$emit("input", tags);
		},
		onInvalid() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
		},
		remove(tag) {
			// get neighboring tags
			const prev = this.get("prev");
			const next = this.get("next");

			// remove tag and fire input event
			this.tags.splice(this.index(tag), 1);
			this.onInput();

			if (prev) {
				this.selectTag(prev.tag);
				prev.ref.focus();
			} else if (next) {
				this.selectTag(next.tag);
			} else {
				this.selectTag(null);
				this.$refs.toggle.focus();
			}
		},
		search(event) {
			const char = String.fromCharCode(event.keyCode);

			if (char.match(/(\w)/g)) {
				this.$refs.selector.open();
			}
		},
		select() {
			this.focus();
		},
		selectTag(tag) {
			this.selected = tag;
		},
		/**
		 * @param {String,Object} value
		 * @returns {text: String, value: String}
		 */
		toValue(value) {
			const option = this.options.find((option) => option.value === value);

			// if only options are allwed as value
			if (this.accept === "options") {
				return option;
			}

			// always prefer options as source
			// as they can be trusted without escaping
			if (option) {
				return option;
			}

			if (typeof value === "string") {
				value = { value: value };
			}

			return {
				value: value.value,
				// always escape HTML in text for tags that
				// can't be matched with any defined option
				// to avoid XSS when displaying via `v-html`
				text: this.$helper.string.escapeHTML(value.text ?? value.value)
			};
		},
		toValues(values) {
			// objects to array
			if (typeof values === "object") {
				values = Object.values(values);
			}

			if (Array.isArray(values) === false) {
				return [];
			}

			return values.map(this.toValue).filter((item) => item);
		}
	},
	validations() {
		return {
			tags: {
				required: this.required ? validateRequired : true,
				minLength: this.min ? validateMinLength(this.min) : true,
				maxLength: this.max ? validateMaxLength(this.max) : true
			}
		};
	}
};
</script>

<style>
.k-tags-input {
	display: flex;
	flex-wrap: wrap;
}
.k-tags-input .k-sortable-ghost {
	outline: var(--outline);
}
.k-tags-input[data-layout="list"] .k-tag {
	width: 100%;
}
.k-tags-input-toggle.k-button {
	--button-rounded: var(--button-height);
}

/* Field Theme */
.k-input[data-theme="field"][data-type="tags"] .k-tags-input {
	display: flex;
	align-items: center;
	gap: 0.25rem;
	padding: 0.25rem;
}
</style>
