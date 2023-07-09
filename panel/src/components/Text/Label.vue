<template>
	<component
		:is="element"
		:for="input"
		:class="'k-' + type + '-label'"
		:data-invalid="invalid"
		class="k-label"
	>
		<k-link v-if="link" :to="link">
			<!-- @slot Content/text of the label -->
			<slot />
		</k-link>
		<slot v-else />

		<abbr v-if="required && !invalid" :title="$t(type + '.required')">✶</abbr>
		<abbr :title="$t(type + '.invalid')" class="k-label-invalid">&times;</abbr>
	</component>
</template>

<script>
/**
 * <k-label> is used to label form fields and sections
 * @public
 */
export default {
	props: {
		/**
		 * ID of the input element to which the label belongs
		 */
		input: {
			type: [String, Number]
		},
		/**
		 * Whether the input value is currently invalid
		 */
		invalid: {
			type: Boolean
		},
		/**
		 * Sets a link for the label. Link can be absolute or relative.
		 */
		link: {
			type: String
		},
		/**
		 * Whether a value is required for the input
		 */
		required: {
			default: false,
			type: Boolean
		},
		/**
		 * Which type the label belongs to
		 * @values field, section
		 */
		type: {
			default: "field",
			type: String
		}
	},
	computed: {
		element() {
			return this.type === "section" ? "h2" : "label";
		}
	}
};
</script>

<style>
.k-label {
	position: relative;
	display: flex;
	align-items: center;
	height: var(--height-xs);
	overflow: clip;
	text-overflow: ellipsis;
	white-space: nowrap;
	font-weight: var(--font-semi);
}
[aria-disabled] .k-label {
	opacity: var(--opacity-disabled);
	cursor: not-allowed;
}

/** Required and invalid sign **/
.k-label abbr {
	font-size: var(--text-xs);
	color: var(--color-gray-500);
	margin-inline-start: var(--spacing-1);
}

.k-label abbr.k-label-invalid {
	display: none;
	color: var(--color-red-700);
}

/** Tracking invalid via CSS */
/** TODO: replace once invalid state is tracked in panel.content */
:where(.k-field:has([data-invalid]), .k-section:has([data-invalid]), )
	> header
	> .k-label
	abbr.k-label-invalid {
	display: inline-block;
}

.k-field:has([data-invalid])
	> .k-field-header
	> .k-label
	abbr:has(+ abbr.k-label-invalid) {
	display: none;
}
</style>
