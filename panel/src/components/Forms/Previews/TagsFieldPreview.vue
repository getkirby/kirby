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
				:html="html"
				:image="tag.image"
				:link="tag.link"
				:text="tag.text"
				element="div"
				theme="light"
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
		value: {
			default: () => [],
			type: [Array, String]
		}
	},
	computed: {
		tags() {
			let tags = this.value;

			// predefined options
			const options = this.column.options ?? this.field.options;

			// a value without option is only a custom tag
			// if the field accepts any value
			const custom = options === undefined || this.field.accept === "all";

			if (typeof tags === "string") {
				tags = tags.split(",").map((tag) => tag.trim());
			}

			return (tags ?? []).map((tag) => {
				if (typeof tag === "string") {
					tag = { value: tag, text: tag };
				}

				const option = options?.find((option) => option.value === tag.value);

				if (option) {
					tag.text = option.text;
				} else if (custom === false) {
					tag.text = `[${this.$t("unknown")}]`;
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
