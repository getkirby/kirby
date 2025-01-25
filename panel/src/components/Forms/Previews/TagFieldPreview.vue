<template>
	<ul
		:class="[
			'k-tag-field-preview',
			'k-tags-field-preview',
			'k-tags',
			$options.class,
			$attrs.class
		]"
		:style="$attrs.style"
	>
		<li>
			<k-tag :html="html" :text="value" theme="light" @click.native.stop />
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
		value: String
	}
};
</script>
