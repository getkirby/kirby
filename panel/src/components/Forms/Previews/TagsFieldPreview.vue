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
import html from "@/panel/html";

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
export default {
	mixins: [FieldPreview],
	props: {
		/**
		 * If set to `true`, the `text` is rendered as HTML code,
		 * otherwise as plain text
		 * @deprecated 6.0.0 Trusted HTML in the value is rendered as-is
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
						// an option's text is already trusted HTML
						tag.text = option.text;
					}
				}

				// the deprecated `html` flag rendered every tag raw
				if (this.html === true) {
					tag.text = html(tag.text);
				}

				return tag;
			});
		}
	},
	created() {
		if (this.html === true) {
			window.panel.deprecated(
				"`k-tags-field-preview`: the `html` prop has been deprecated. Trusted HTML in the value is rendered as-is."
			);
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
