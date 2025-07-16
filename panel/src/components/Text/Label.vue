<template>
	<component
		:is="element"
		:for="input"
		:class="'k-' + type + '-label'"
		class="k-label"
	>
		<k-link v-if="link" :to="link">
			<!-- @slot Content/text of the label -->
			<span class="k-label-text"><slot /></span>
		</k-link>
		<span v-else class="k-label-text">
			<slot />
		</span>
		<template v-if="input !== false">
			<abbr v-if="required" :title="$t(type + '.required')">✶</abbr>
			<abbr
				:title="$t(type + '.invalid')"
				data-theme="negative"
				class="k-label-invalid"
			>
				&times;
			</abbr>
		</template>
	</component>
</template>

<script>
/**
 * Used to label form fields and sections
 */
export default {
	props: {
		/**
		 * ID of the input element to which the label belongs
		 */
		input: {
			type: [String, Number, Boolean]
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
			return this.type === "section" || this.input === false ? "h2" : "label";
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
	font-weight: var(--font-semi);
	min-width: 0;
	flex: 1;
}
[aria-disabled="true"] .k-label {
	opacity: var(--opacity-disabled);
	cursor: not-allowed;
}

.k-label > a {
	display: inline-flex;
	height: var(--height-xs);
	align-items: center;
	padding-inline: var(--spacing-2);
	margin-inline-start: calc(-1 * var(--spacing-2));
	border-radius: var(--rounded);
	min-width: 0;
}
.k-label-text {
	text-overflow: ellipsis;
	white-space: nowrap;
	overflow-x: clip;
	min-width: 0;
}

/** Required and invalid sign **/
.k-label abbr {
	font-size: var(--text-xs);
	color: var(--color-gray-500);
	margin-inline-start: var(--spacing-1);
}

.k-label abbr.k-label-invalid {
	display: none;
	color: var(--theme-color-text);
}

/** Tracking invalid via CSS */
/** TODO: replace once invalid state is tracked in panel.content */
:where(.k-field:has(:invalid), .k-section:has([data-invalid="true"]))
	> header
	> .k-label
	abbr.k-label-invalid {
	display: inline-block;
}

.k-field:has(:invalid)
	> .k-field-header
	> .k-label
	abbr:has(+ abbr.k-label-invalid) {
	display: none;
}
</style>
