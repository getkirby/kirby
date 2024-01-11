<template>
	<k-block-figure
		:caption="content.caption"
		:caption-marks="captionMarks"
		:empty-text="$t('field.blocks.image.placeholder') + ' …'"
		:is-empty="!src"
		empty-icon="image"
		@open="open"
		@update="update"
	>
		<template v-if="src">
			<k-image-frame
				v-if="ratio"
				:ratio="ratio"
				:cover="crop"
				:alt="content.alt"
				:src="src"
			/>
			<img
				v-else
				:alt="content.alt"
				:src="src"
				class="k-block-type-image-auto"
			/>
		</template>
	</k-block-figure>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeImage
 */
export default {
	extends: Block,
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

			if (this.content.image?.[0]?.url) {
				return this.content.image[0].url;
			}

			return false;
		},
		ratio() {
			return this.content.ratio ?? false;
		}
	}
};
</script>

<style>
.k-block-type-image .k-block-figure-container {
	text-align: center;
	line-height: 0;
}
.k-block-type-image-auto {
	max-width: 100%;
	max-height: 30rem;
	margin-inline: auto;
}
</style>
