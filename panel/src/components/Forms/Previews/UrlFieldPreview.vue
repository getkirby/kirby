<template>
	<p
		:class="['k-url-field-preview', $options.class, $attrs.class]"
		:data-link="Boolean(link)"
		:style="$attrs.style"
	>
		{{ column.before }}
		<k-link :to="link" @click.stop>
			<span>{{ text }}</span>
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
	padding-inline: var(--table-cell-padding);
}
.k-url-field-preview[data-link="true"] {
	color: var(--link-color);
}
.k-url-field-preview a {
	display: inline-flex;
	align-items: center;
	height: var(--height-xs);
	padding-inline: var(--spacing-1);
	margin-inline: calc(var(--spacing-1) * -1);
	border-radius: var(--rounded);
	max-width: 100%;
	min-width: 0;
}
.k-url-field-preview a > * {
	overflow: hidden;
	white-space: nowrap;
	text-overflow: ellipsis;
	text-decoration: underline;
	text-underline-offset: var(--link-underline-offset);
}
.k-url-field-preview a:hover {
	color: var(--link-color-hover);
}
</style>
