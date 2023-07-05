<template>
	<component
		:is="element"
		:for="input"
		:class="'k-' + type + '-label'"
		:data-invalid="invalid"
		class="k-label"
	>
		<k-link v-if="link" :to="link">
			<slot />
		</k-link>
		<slot v-else />
		<abbr v-if="invalid" :title="$t(type + '.invalid')">&times;</abbr>
		<abbr v-else-if="required" :title="$t(type + '.required')">✶</abbr>
	</component>
</template>

<script>
export default {
	props: {
		input: {
			type: [String, Number]
		},
		invalid: {
			type: Boolean
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
.k-label abbr {
	color: var(--color-gray-500);
	margin-inline-start: 0.375rem;
}

.k-label[data-invalid] abbr {
	color: var(--color-red-700);
}
</style>
