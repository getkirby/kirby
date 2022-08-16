<template>
	<div class="k-html-field-preview" :class="$options.class">
		{{ column.before }}
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div v-html="html" />
		{{ column.after }}
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	inheritAttrs: false,
	props: {
		value: String
	},
	computed: {
		html() {
			return this.value;
		}
	}
};
</script>

<style>
.k-html-field-preview {
	padding: 0.325rem 0.75rem;
	overflow: hidden;
	text-overflow: ellipsis;
	white-space: nowrap;
	line-height: 1.5em;
}
.k-html-field-preview p:not(:last-child) {
	margin-bottom: 1.5em;
}
.k-html-field-preview ul,
.k-html-field-preview ol {
	margin-inline-start: 1rem;
}
.k-html-field-preview ul > li {
	list-style: disc;
}
.k-html-field-preview ol ul > li,
.k-html-field-preview ul ul > li {
	list-style: circle;
}
.k-html-field-preview ol > li {
	list-style: decimal;
}
.k-html-field-preview ol > li::marker {
	color: var(--color-gray-500);
	font-size: var(--text-xs);
}
</style>
