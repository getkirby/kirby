<template>
	<k-block-figure
		:back="back"
		:caption="content.caption"
		:caption-marks="captionMarks"
		:empty-text="$t('field.blocks.image.placeholder') + ' …'"
		:disabled="disabled"
		:is-empty="!src"
		empty-icon="image"
		@open="open"
		@update="update"
	>
		<k-image-frame :alt="content.alt" :cover="crop" :ratio="ratio" :src="src" />
		<k-block-background-dropdown :value="back" @input="onBack" />
	</k-block-figure>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeImage
 */
export default {
	extends: Block,
	data() {
		return {
			back: this.onBack() ?? "transparent"
		};
	},
	computed: {
		captionMarks() {
			return this.field("caption", { marks: true }).marks;
		},
		crop() {
			return this.content.crop ?? false;
		},
		src() {
			if (this.content.location === "web") {
				return this.content.src;
			}

			return this.content.image?.[0];
		},
		ratio() {
			return this.content.ratio;
		}
	},
	methods: {
		onBack(value) {
			const id = `kirby.imageBlock.${this.endpoints.field}.${this.id}`;

			if (value !== undefined) {
				this.back = value;
				sessionStorage.setItem(id, value);
			} else {
				return sessionStorage.getItem(id);
			}
		}
	}
};
</script>

<style>
.k-block-container.k-block-container-type-image {
	padding: 0;
}
.k-block-type-image .k-block-figure {
	padding: var(--spacing-3);
	border-radius: var(--rounded);
}
.k-block-type-image .k-block-figure-container {
	text-align: center;
	line-height: 0;
}
.k-block-type-image .k-block-figure[data-empty="true"] {
	padding: var(--spacing-3);
}

.k-block-type-image .k-image-frame:not([style*="--ratio"]) {
	max-width: 100%;
	max-height: 30rem;
	margin-inline: auto;
}
.k-block-type-image .k-background-dropdown {
	position: absolute;
	inset-inline-end: var(--spacing-3);
	bottom: var(--spacing-3);
	opacity: 0;
	transition: opacity 0.2s ease-in-out;
}
.k-block-type-image:hover .k-background-dropdown {
	opacity: 1;
}
</style>
