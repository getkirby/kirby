<template>
	<component
		:is="element"
		:for="input"
		:class="'k-' + type + '-label'"
		class="k-label"
	>
		<k-link v-if="link" :to="link">
			<slot />
		</k-link>
		<slot v-else />
		<abbr v-if="required" :title="$t(type + '.required')">✶</abbr>
	</component>
</template>

<script>
export default {
	props: {
		input: {
			type: [String, Number]
		},
		link: {
			type: String
		},
		required: {
			default: false,
			type: Boolean
		},
		tag: {
			default: "label",
			type: String
		},
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
.k-label abbr,
.k-label::after {
	color: var(--color-gray-500);
	margin-inline-start: 0.375rem;
}

/** Field Labels **/
.k-field:has([data-invalid="true"]) .k-field-label::after {
	content: "×";
	color: var(--color-red-700);
}
.k-field:has([data-invalid="true"]) .k-field-label abbr {
	display: none;
}
</style>
