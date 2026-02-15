<template>
	<div
		:class="[
			'k-link-field-preview',
			{ 'k-url-field-preview': isLink },
			$attrs.class
		]"
		:style="$attrs.style"
	>
		<template v-if="currentType === 'page' || currentType === 'file'">
			<template v-if="model">
				<k-tag
					:image="{
						...model.image,
						cover: true
					}"
					:removable="removable"
					:text="model.label"
					@remove="$emit('remove', $event)"
				/>
			</template>
			<slot v-else name="placeholder" />
		</template>
		<template v-else-if="isLink">
			<p class="k-text">
				<a :href="value" target="_blank">
					<span>{{ detected.link }}</span>
				</a>
			</p>
		</template>
		<template v-else>
			{{ detected.link }}
		</template>
	</div>
</template>

<script>
import FieldPreview from "@/mixins/forms/fieldPreview.js";

export default {
	mixins: [FieldPreview],
	props: {
		removable: Boolean,
		type: String
	},
	emits: ["remove"],
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
		},
		isLink() {
			return ["url", "email", "tel"].includes(this.currentType);
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

<style>
.k-link-field-preview {
	--tag-height: var(--height-xs);
	--tag-color-back: var(--panel-color-back);
	--tag-color-text: currentColor;
	--tag-color-toggle: var(--tag-color-text);
	--tag-color-toggle-border: var(--color-gray-300);
	--tag-color-focus-back: var(--tag-color-back);
	--tag-color-focus-text: var(--tag-color-text);
	padding-inline: var(--table-cell-padding);
	min-width: 0;
}
.k-link-field-preview .k-tag {
	min-width: 0;
	max-width: 100%;
}
.k-link-field-preview .k-tag-text {
	font-size: var(--text-xs);
	min-width: 0;
}
</style>
