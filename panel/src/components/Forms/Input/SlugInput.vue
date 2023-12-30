<template>
	<input
		ref="input"
		v-bind="{
			autofocus,
			disabled,
			id,
			minlength,
			name,
			pattern,
			placeholder,
			required
		}"
		v-direction
		:value="slug"
		autocomplete="off"
		spellcheck="false"
		type="text"
		class="k-text-input"
		@input="$emit('input', $event)"
	/>
</template>

<script>
import TextInput, { props as TextInputProps } from "./TextInput.vue";

export const props = {
	mixins: [TextInputProps],
	props: {
		allow: {
			type: String,
			default: ""
		},
		formData: {
			type: Object,
			default: () => ({})
		},
		sync: {
			type: String
		}
	}
};

/**
 * @example <k-input :value="slug" @input="slug = $event" name="slug" type="slug" />
 */
export default {
	extends: TextInput,
	mixins: [props],
	data() {
		return {
			slug: this.sluggify(this.value),
			slugs: this.$panel.language.rules ?? this.$panel.system.slugs,
			syncValue: null
		};
	},
	watch: {
		formData: {
			handler(newValue) {
				if (this.disabled) {
					return false;
				}

				if (!this.sync || newValue[this.sync] === undefined) {
					return false;
				}

				if (newValue[this.sync] == this.syncValue) {
					return false;
				}

				this.syncValue = newValue[this.sync];
				this.onInput(this.sluggify(this.syncValue));
			},
			deep: true,
			immediate: true
		},
		value(newValue) {
			newValue = this.sluggify(newValue);

			if (newValue !== this.slug) {
				this.slug = newValue;
				this.$emit("input", this.slug);
			}
		}
	},
	methods: {
		sluggify(value) {
			return this.$helper.slug(
				value,
				[this.slugs, this.$panel.system.ascii],
				this.allow
			);
		},
		onInput(value) {
			this.slug = this.sluggify(value);
			this.$emit("input", this.slug);
		}
	}
};
</script>
