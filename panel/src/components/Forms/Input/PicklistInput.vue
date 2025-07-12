<template>
	<k-navigate
		:class="['k-picklist-input', $attrs.class]"
		:style="$attrs.style"
		element="nav"
		axis="y"
		select="input[type=search], label, .k-picklist-input-body button"
		@prev="$emit('escape')"
	>
		<header v-if="search" class="k-picklist-input-header">
			<div class="k-picklist-input-search">
				<k-search-input
					ref="search"
					:autofocus="autofocus"
					:disabled="disabled"
					:placeholder="placeholder"
					:value="query"
					@input="query = $event"
					@keydown.escape.prevent="escape"
					@keydown.enter.prevent="add"
				/>
				<k-button
					v-if="showCreate"
					class="k-picklist-input-create"
					icon="add"
					size="xs"
					@click="add"
				/>
			</div>
		</header>

		<template v-if="filteredOptions.length">
			<div class="k-picklist-input-body">
				<k-input-validator
					v-bind="{ min, max, required }"
					:value="JSON.stringify(value)"
				>
					<component
						:is="multiple ? 'k-checkboxes-input' : 'k-radio-input'"
						ref="options"
						:disabled="disabled"
						:options="choices"
						:value="value"
						class="k-picklist-input-options"
						@input="input"
						@keydown.enter.prevent="enter"
					/>
				</k-input-validator>
				<k-button
					v-if="display !== true && filteredOptions.length > display"
					class="k-picklist-input-more"
					icon="angle-down"
					@click="display = true"
				>
					{{ $t("options.all", { count: filteredOptions.length }) }}
				</k-button>
			</div>
		</template>

		<template v-else-if="showEmpty">
			<div class="k-picklist-input-body">
				<p class="k-picklist-input-empty">
					{{ $t("options.none") }}
				</p>
			</div>
		</template>
	</k-navigate>
</template>

<script>
import Input, { props as InputProps } from "@/mixins/input.js";
import { autofocus, disabled, options, required } from "@/mixins/props.js";

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
	emits: ["create", "escape", "input"],
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
		placeholder() {
			if (this.search.placeholder) {
				return this.search.placeholder;
			}

			if (this.options.length > 0) {
				return this.$t("filter") + "…";
			}

			return this.$t("enter") + "…";
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
		enter(event) {
			event.target?.click();
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
	}
};
</script>

<style>
:root {
	--picklist-rounded: var(--rounded-sm);
	--picklist-highlight: var(--color-yellow-500);
}

.k-picklist-input {
	--choice-color-text: currentColor;
	--button-rounded: var(--picklist-rounded);
}

.k-picklist-input-header {
	--input-rounded: var(--picklist-rounded);
}
.k-picklist-input-search {
	display: flex;
	align-items: center;
	border-radius: var(--picklist-rounded);
}
.k-picklist-input-search .k-search-input {
	height: var(--button-height);
}
.k-picklist-input-search:focus-within {
	outline: var(--outline);
}
.k-picklist-dropdown .k-picklist-input-create:focus {
	outline: 0;
}
.k-picklist-dropdown .k-picklist-input-create[aria-disabled="true"] {
	visibility: hidden;
}

.k-picklist-input-options.k-grid {
	--columns: 1;
}
.k-picklist-input-options li + li {
	margin-top: var(--spacing-1);
}
.k-picklist-input-options .k-choice-input {
	padding-inline: var(--spacing-2);
}

.k-picklist-input-options .k-choice-input {
	--choice-color-checked: var(--color-focus);
}
.k-picklist-input-options .k-choice-input:has(:checked) {
	--choice-color-text: var(--color-focus);
}
.k-picklist-input-options .k-choice-input[aria-disabled="true"] {
	--choice-color-text: var(--color-text-dimmed);
}
.k-picklist-input-options .k-choice-input:has(:focus-within) {
	outline: var(--outline);
}
.k-picklist-input-options .k-choice-input b {
	font-weight: var(--font-normal);
	color: var(--picklist-highlight);
}

.k-picklist-input-more.k-button {
	--button-width: 100%;
	--button-align: start;
	--button-color-text: var(--color-text-dimmed);
	padding-inline: var(--spacing-2);
}
.k-picklist-input-more.k-button .k-button-icon {
	position: relative;
	inset-inline-start: -1px;
}

.k-picklist-input-empty {
	height: var(--button-height);
	line-height: 1.25rem;
	padding: var(--spacing-1) var(--spacing-2);
	color: var(--color-text-dimmed);
}
</style>
