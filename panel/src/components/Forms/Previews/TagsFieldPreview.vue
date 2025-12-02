<template>
	<ul
		:class="['k-tags-field-preview', 'k-tags', $options.class, $attrs.class]"
		:style="$attrs.style"
	>
		<li
			v-for="(tag, tagIndex) in tags"
			:key="tag.id ?? tag.value ?? tag.text ?? tagIndex"
		>
			<k-tag
			  :element="!removable ? 'div' : undefined"
				:html="html"
				:image="tag.image"
				:link="!removable ? tag.link : undefined"
				:text="tag.text"
				:removable="removable"
				theme="light"
				@remove="$emit('remove', $event)"
			/>
		</li>
	</ul>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	props: {
		/**
		 * If set to `true`, the `text` is rendered as HTML code,
		 * otherwise as plain text
		 */
		html: {
			type: Boolean
		},
		removable: Boolean,
		value: {
			default: () => [],
			type: [Array, String]
		}
	},
	emits: ["remove"],
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
