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
			<k-tag :text="content" theme="light" @click.stop />
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
		value: String
	},
	computed: {
		content() {
			// the deprecated `html` flag rendered the value raw
			return this.html === true ? html(this.value) : this.value;
		}
	},
	created() {
		if (this.html === true) {
			window.panel.deprecated(
				"`k-tag-field-preview`: the `html` prop has been deprecated. Trusted HTML in the value is rendered as-is."
			);
		}
	}
};
</script>
