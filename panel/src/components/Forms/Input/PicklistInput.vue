<template>
	<k-navigate
		element="nav"
		axis="y"
		select="input[type=search], label, button"
		class="k-picklist-input"
		@prev="$emit('escape')"
	>
		<header v-if="search" class="k-picklist-input-header">
			<k-search-input
				ref="search"
				:autofocus="autofocus"
				:disabled="disabled"
				:placeholder="(search.placeholder ?? $t('filter')) + ' …'"
				:value="query"
				class="k-picklist-input-search"
				@input="query = $event"
				@keydown.escape.native.prevent="escape"
				@keydown.enter.native.prevent="add"
			/>
		</header>

		<template v-if="filteredOptions.length">
			<component
				:is="multiple ? 'k-checkboxes-input' : 'k-radio-input'"
				ref="options"
				:disabled="disabled"
				:options="choices"
				:value="value"
				class="k-picklist-input-options"
				@input="input"
			/>

			<k-button
				v-if="display !== true && filteredOptions.length > display"
				class="k-picklist-input-more"
				@click="display = true"
			>
				{{ $t("options.all", { count: filteredOptions.length }) }}
			</k-button>
		</template>

		<p v-if="showEmpty" class="k-picklist-input-empty">
			{{ $t("options.none") }}
		</p>

		<footer v-if="showCreate" class="k-picklist-input-footer">
			<k-button
				:current="filteredOptions.length === 0"
				icon="add"
				class="k-picklist-input-create"
				@click="add"
			>
				<strong>{{ create.submit ?? $t("add") }}:</strong>
				<span class="k-picklist-input-create-preview">{{ query }}</span>
			</k-button>
		</footer>
	</k-navigate>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { autofocus, disabled, options, required } from "@/mixins/props.js";

import {
	required as validateRequired,
	minLength as validateMinLength,
	maxLength as validateMaxLength
} from "vuelidate/lib/validators";

export const picklist = {
	mixins: [autofocus, disabled, options, required],
	props: {
		/**
		 * Which terms to ignore when showing the create button
		 */
		ignore: {
			default: () => [],
			type: Array
		},
		/**
		 * The maximum number of accepted tags
		 */
		max: Number,
		/**
		 * The minimum number of required tags
		 */
		min: Number,
		/**
		 * Whether to show the search input
		 * @value false | true | { min, placeholder }
		 */
		search: {
			default: true,
			type: [Object, Boolean]
		}
	}
};

export const props = {
	mixins: [InputProps, picklist],
	props: {
		/**
		 * Whether to show the create button
		 * @value false | true | { submit }
		 */
		create: {
			type: [Boolean, Object],
			default: false
		},
		/**
		 * Whether to allow multiple selections
		 */
		multiple: {
			type: Boolean,
			default: true
		},
		value: {
			type: [Array, String],
			default: () => []
		}
	}
};

/**
 * A filterable list of checkbox/radio options
 * with an optional create button
 * @since 4.0.0
 *
 * @example <k-picklist-input
 * 		:create="create"
 * 		:options="options"
 * 		:value="value"
 *	/>
 */
export default {
	mixins: [Input, props],
	data() {
		return {
			display: this.search.display ?? true,
			query: ""
		};
	},
	computed: {
		choices() {
			let options = this.filteredOptions;

			if (this.display !== true) {
				options = options.slice(0, this.display);
			}

			return options.map((option) => ({
				...option,
				// disable options if max is reached that are not yet selected,
				// allow interaction with already selected options to allow deselecting
				disabled:
					option.disabled ||
					(this.isFull && this.value.includes(option.value) === false),
				text: this.highlight(option.text)
			}));
		},
		filteredOptions() {
			// min length for the search to kick in
			if (this.query.length < (this.search.min ?? 0)) {
				return;
			}

			return this.$helper.array.search(this.options, this.query, {
				field: "text"
			});
		},
		isFull() {
			return this.max && this.value.length >= this.max;
		},
		showCreate() {
			if (this.create === false) {
				return false;
			}

			// don't show the create button if the max is reached
			if (this.isFull) {
				return false;
			}

			// don't show the create button if the query is empty
			if (this.query.trim().length === 0) {
				return false;
			}

			// don't show the button if the query is in the ignore list
			if (this.ignore.includes(this.query) === true) {
				return false;
			}

			// don't show the button if the query is in the ignore list
			if (this.create.ignore?.includes(this.query) === true) {
				return false;
			}

			// don't show the button if the query matches an existing option
			const matches = this.options.filter(
				(option) => option.text === this.query || option.value === this.query
			);

			return matches.length === 0;
		},
		showEmpty() {
			return this.create === false && this.filteredOptions.length === 0;
		}
	},
	watch: {
		value: {
			handler() {
				/**
				 * Validation failed
				 */
				this.$emit("invalid", this.$v.$invalid, this.$v);
			},
			immediate: true
		}
	},
	methods: {
		add() {
			if (this.showCreate) {
				/**
				 * New option shall be created from input
				 * @property {string} input
				 */
				this.$emit("create", this.query);
			}
		},
		escape() {
			if (this.query.length === 0) {
				/**
				 * Escape key was hit to close the list
				 */
				this.$emit("escape");
			} else {
				this.query = "";
			}
		},
		focus() {
			if (this.$refs.search) {
				this.$refs.search.focus();
			} else {
				this.$refs.options?.focus();
			}
		},
		highlight(string) {
			// make sure that no HTML exists before in the string
			// to avoid XSS when displaying via `v-html`
			string = this.$helper.string.stripHTML(string);

			if (this.query.length > 0) {
				const regex = new RegExp(`(${RegExp.escape(this.query)})`, "ig");
				return string.replace(regex, "<b>$1</b>");
			}

			return string;
		},
		input(values) {
			/**
			 * Selected values have changed
			 * @property {array} values
			 */
			this.$emit("input", values);
		}
	},
	validations() {
		return {
			value: {
				required: this.required ? validateRequired : true,
				minLength: this.min ? validateMinLength(this.min) : true,
				maxLength: this.max ? validateMaxLength(this.max) : true
			}
		};
	}
};
</script>

<style>
:root {
	--picklist-highlight: var(--color-yellow-600);
	--picklist-rounded: var(--rounded-sm);
	--picklist-selected: var(--color-focus);
	--picklist-separator: var(--color-border);
}

.k-picklist-input {
	--choice-color-text: currentColor;
	--button-rounded: var(--picklist-rounded);
}

.k-picklist-input-header
	+ :where(.k-picklist-input-options, .k-picklist-input-empty) {
	border-top: 1px solid var(--picklist-separator);
}

.k-picklist-input-search {
	--input-rounded: var(--picklist-rounded);
	height: var(--button-height);
}

.k-picklist-input-options .k-choice-input {
	min-height: var(--button-height);
	padding: var(--spacing-1) var(--spacing-2);
	border-radius: var(--picklist-rounded);
}
.k-picklist-input-options .k-choice-input:has(:checked) {
	--choice-color-text: var(--picklist-selected);
	--choice-color-checked: var(--picklist-selected);
}
.k-picklist-input-options .k-choice-input[aria-disabled="true"] {
	--choice-color-text: var(--color-text-dimmed);
}
.k-picklist-input-options .k-choice-input:has(:focus-within) {
	outline: var(--outline);
}
.k-picklist-input-options .k-choice-input b {
	color: var(--picklist-highlight);
}

.k-picklist-input-more.k-button {
	--button-width: 100%;
	padding: var(--spacing-1) var(--spacing-2);
	font-size: var(--text-xs);
}

.k-picklist-input-empty {
	height: var(--button-height);
	display: flex;
	align-items: center;
	padding: var(--spacing-2);
	color: var(--color-text-dimmed);
}

.k-picklist-input-options + .k-picklist-input-footer {
	border-top: 1px solid var(--picklist-separator);
}
.k-picklist-input-create {
	--button-width: 100%;
	--button-align: start;
	--button-height: auto;
	padding-block: var(--button-padding);
	gap: var(--spacing-3);
}
.k-picklist-input-create[aria-current="true"] {
	outline: var(--outline);
}
.k-picklist-input-create .k-button-icon {
	align-self: start;
}
.k-picklist-input-create strong {
	display: block;
	font-weight: var(--font-semi);
	margin-bottom: var(--spacing-1);
}
.k-picklist-input-create-preview {
	color: var(--picklist-highlight);
}
</style>
