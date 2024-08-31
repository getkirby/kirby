<template>
	<div
		:class="['k-tags-field-preview', $options.class, $attrs.class]"
		:style="$attrs.style"
	>
		<k-tags
			:draggable="false"
			:html="html"
			:value="tags"
			element="ul"
			element-tag="li"
			theme="light"
		/>
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";
import { props as TagsProps } from "@/components/Navigation/Tags.vue";

export default {
	mixins: [FieldPreview, TagsProps],
	props: {
		value: {
			default: () => [],
			type: [Array, String]
		}
	},
	computed: {
		tags() {
			let tags = this.value;

			// predefined options
			const options = this.column.options ?? this.field.options ?? [];

			if (typeof tags === "string") {
				tags = tags.split(",");
			}

			return (tags ?? []).map((tag) => {
				if (typeof tag === "string") {
					tag = { value: tag, text: tag };
				}

				for (const option of options) {
					if (option.value === tag.value) {
						tag.text = option.text;
					}
				}

				return tag;
			});
		}
	}
};
</script>

<style>
.k-tags-field-preview {
	--tags-gap: 0.25rem;
	--tag-text-size: var(--text-xs);

	padding: 0.375rem var(--table-cell-padding);
	overflow: hidden;
}
.k-tags-field-preview .k-tags {
	flex-wrap: nowrap;
}
</style>
