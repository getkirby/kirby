<template>
	<ul
		v-if="options.length"
		:data-theme="theme"
		:style="'--columns:' + columns"
		class="k-choices-input"
	>
		<li v-for="(option, index) in options" :key="index">
			<k-choice-input
				:checked="selected.indexOf(option.value) !== -1"
				:id="id + '-' + index"
				:info="option.info"
				:label="option.text"
				:theme="theme"
				:type="type"
				:value="option.value"
				@input="onInput(option.value, $event)"
			/>
		</li>
	</ul>
	<k-empty v-else icon="info">{{ $t("options.none") }}</k-empty>
</template>

<script>
import { autofocus, disabled, id, required } from "@/mixins/props.js";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength
} from "vuelidate/lib/validators";

export const props = {
	mixins: [autofocus, disabled, id, required],
	props: {
		columns: Number,
		max: Number,
		min: Number,
		options: {
			default: () => [],
			type: Array
		},
		theme: String,
		/**
		 * The value for the input should be provided as array. Each value in the array corresponds with the value in the options. If you provide a string, the string will be split by comma.
		 */
		value: {
			type: [Array, Object],
			default: () => []
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	data() {
		return {
			selected: this.toArray(this.value)
		};
	},
	watch: {
		value(value) {
			this.selected = this.toArray(value);
		},
		selected() {
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
		focus() {
			this.$el.querySelector("input")?.focus();
		},
		onInput(key, value) {
			if (value === true) {
				this.selected.push(key);
			} else {
				const index = this.selected.indexOf(key);
				if (index !== -1) {
					this.selected.splice(index, 1);
				}
			}
			this.$emit("input", this.selected);
		},
		onInvalid() {
			this.$emit("invalid", this.$v.$invalid, this.$v);
		},
		select() {
			this.focus();
		},
		toArray(value) {
			if (Array.isArray(value) === true) {
				return value;
			}

			if (typeof value === "string") {
				return String(value).split(",");
			}

			if (typeof value === "object") {
				return Object.values(value);
			}
		}
	},
	validations() {
		return {
			selected: {
				required: this.required ? validateRequired : true,
				min: this.min ? validateMinLength(this.min) : true,
				max: this.max ? validateMaxLength(this.max) : true
			}
		};
	}
};
</script>

<style>
.k-choices-input {
	--columns: 1;
	display: grid;
	gap: var(--spacing-6);
	grid-template-columns: repeat(var(--columns), 1fr);
}
.k-choices-input[data-theme="field"] {
	gap: var(--spacing-1);
}
</style>
