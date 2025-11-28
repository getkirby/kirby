<template>
	<template v-if="currentType === 'page' || currentType === 'file'">
		<component
			:is="`k-${currentType}s-field-preview`"
			v-if="value"
			:removable="removable"
			:value="[value]"
			@remove="$emit('remove', $event)"
		/>
		<slot v-else name="placeholder" />
	</template>
	<div
		v-else
		:class="{
			'k-link-field-preview': true,
			'k-url-field-preview': isLink,
			[$attrs.class]: true
		}"
		:style="$attrs.style"
	>
		<template v-if="isLink">
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
	}
};
</script>

<style>
.k-link-field-preview {
	padding-inline: var(--table-cell-padding);
	min-width: 0;
}
</style>
