<template>
	<p class="k-url-field-preview" :class="$options.class" :data-link="link">
		{{ column.before }}
		<k-link :to="link" @click.native.stop>
			{{ text }}
		</k-link>
		{{ column.after }}
	</p>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	props: {
		value: [String, Object]
	},
	computed: {
		link() {
			return typeof this.value === "object" ? this.value.href : this.value;
		},
		text() {
			if (typeof this.value === "object") {
				return this.value.text;
			}

			return this.link;
		}
	}
};
</script>

<style>
.k-url-field-preview {
	padding: 0.325rem 0.75rem;
	overflow-x: hidden;
	text-overflow: ellipsis;
}
.k-url-field-preview[data-link] {
	color: var(--link-color);
}
.k-url-field-preview a {
	text-decoration: underline;
	text-underline-offset: var(--link-underline-offset);
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
	border-radius: var(--rounded-xs);
	outline-offset: 2px;
}
.k-url-field-preview a:hover {
	color: var(--color-black);
}
</style>
