<template>
	<div class="k-link-field-preview">
		<template v-if="current.type === 'page' || current.type === 'file'">
			<template v-if="model">
				<k-pages-field-preview
					v-if="current.type === 'page'"
					:value="[{ text: model.label, value: current.link }]"
				/>
				<k-files-field-preview
					v-else
					:value="[{ filename: model.label, image: model.image }]"
				/>
			</template>
		</template>

		<k-url-field-preview
			v-else-if="current.type === 'url'"
			:value="current.link"
		/>
		<k-email-field-preview
			v-else-if="current.type === 'email'"
			:value="current.link"
		/>
		<k-text-field-preview v-else :value="current.link" />
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	inheritAttrs: false,
	data() {
		return {
			model: null
		};
	},
	computed: {
		current() {
			return this.$helper.link.detect(this.value);
		}
	},
	watch: {
		current: {
			async handler(value, old) {
				if (value === old) {
					return;
				}

				this.model = await this.$helper.link.preview(this.current);
			},
			immediate: true
		}
	}
};
</script>
