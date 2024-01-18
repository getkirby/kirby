<template>
	<div class="k-link-field-preview">
		<template v-if="currentType === 'page' || currentType === 'file'">
			<template v-if="model">
				<k-pages-field-preview
					v-if="currentType === 'page'"
					:html="false"
					:value="[{ text: model.label, value: value, image: model.image }]"
				/>
				<k-files-field-preview
					v-else
					:html="false"
					:value="[{ filename: model.label, image: model.image }]"
				/>
			</template>

			<slot v-else name="placeholder" />
		</template>
		<k-url-field-preview
			v-else-if="currentType === 'url'"
			:value="detected.link"
		/>
		<k-email-field-preview
			v-else-if="currentType === 'email'"
			:value="detected.link"
		/>
		<k-text-field-preview v-else :value="detected.link" />
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	inheritAttrs: false,
	props: {
		type: String
	},
	data() {
		return {
			model: null
		};
	},
	computed: {
		currentType() {
			return this.type ?? this.detected.type;
		},
		detected() {
			return this.$helper.link.detect(this.value);
		}
	},
	watch: {
		detected: {
			async handler(value, old) {
				if (value === old) {
					return;
				}

				this.model = await this.$helper.link.preview(this.detected);
			},
			immediate: true
		},
		type() {
			this.model = null;
		}
	}
};
</script>
