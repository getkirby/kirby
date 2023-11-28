<template>
	<div class="k-block-title">
		<k-icon :type="icon" class="k-block-icon" />
		<span v-if="name" class="k-block-name">
			{{ name }}
		</span>
		<span v-if="label" class="k-block-label">
			{{ label }}
		</span>
	</div>
</template>

<script>
export default {
	inheritAttrs: false,
	props: {
		fieldset: {
			default: () => ({}),
			type: Object
		},
		content: {
			default: () => ({}),
			type: Object
		}
	},
	computed: {
		icon() {
			return this.fieldset.icon ?? "box";
		},
		label() {
			if (!this.fieldset.label || this.fieldset.label.length === 0) {
				return false;
			}

			if (this.fieldset.label === this.fieldset.name) {
				return false;
			}

			let label = this.$helper.string.template(
				this.fieldset.label,
				this.content
			);

			if (label === "…") {
				return false;
			}

			label = this.$helper.string.stripHTML(label);
			return this.$helper.string.unescapeHTML(label);
		},
		name() {
			return this.fieldset.name;
		}
	}
};
</script>

<style>
.k-block-title {
	display: flex;
	align-items: center;
	min-width: 0;
	padding-inline-end: 0.75rem;
	line-height: 1;
	gap: var(--spacing-2);
}
.k-block-icon {
	--icon-color: var(--color-gray-600);
	width: 1rem;
}
.k-block-label {
	color: var(--color-text-dimmed);
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}
</style>
