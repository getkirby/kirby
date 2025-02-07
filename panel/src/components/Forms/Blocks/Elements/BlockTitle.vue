<template>
	<div :class="['k-block-title', $attrs.class]" :style="$attrs.style">
		<k-icon :type="icon" class="k-block-icon" />
		<span class="k-block-title-text">
			<span v-if="name" class="k-block-name">
				{{ name }}
			</span>
			<span v-if="label" class="k-block-label">
				{{ label }}
			</span>
		</span>
	</div>
</template>

<script>
export const props = {
	props: {
		/**
		 * The block content is an object of values,
		 * depending on the block type.
		 */
		content: {
			default: () => ({}),
			type: [Array, Object]
		},
		/**
		 * The fieldset definition with all fields, tabs, etc.
		 */
		fieldset: {
			default: () => ({}),
			type: Object
		}
	}
};

export default {
	mixins: [props],
	inheritAttrs: false,
	computed: {
		icon() {
			return this.fieldset.icon ?? "box";
		},
		label() {
			if (!this.fieldset.label || this.fieldset.label.length === 0) {
				return false;
			}

			if (this.fieldset.label === this.name) {
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
			return this.fieldset.name ?? this.fieldset.label;
		}
	}
};
</script>

<style>
.k-block-title {
	display: flex;
	align-items: top;
	min-width: 0;
	padding-inline-end: 0.75rem;
	gap: var(--spacing-2);
	flex-shrink: 1;
}
.k-block-title-text {
	display: flex;
	flex-shrink: 1;
	flex-wrap: wrap;
	min-width: 0;
	gap: var(--spacing-2);
}
.k-block-name,
.k-block-label {
	line-height: 1.25;
	overflow: hidden;
	min-width: 0;
	white-space: wrap;
	text-overflow: ellipsis;
}
.k-block-label {
	color: var(--color-text-dimmed);
}
.k-block-icon {
	--icon-color: var(--color-gray-600);
	width: 1rem;
}
</style>
