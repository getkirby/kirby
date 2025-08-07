<template>
	<figure
		:data-empty="isEmpty"
		:style="{ '--block-back': back }"
		class="k-block-type-gallery-figure"
	>
		<ul @dblclick="open">
			<template v-if="isEmpty">
				<li
					v-for="index in 3"
					:key="index"
					class="k-block-type-gallery-placeholder"
				>
					<k-image-frame :ratio="ratio" class="k-block-type-gallery-frame" />
				</li>
			</template>
			<template v-else>
				<li v-for="image in content.images" :key="image.id">
					<k-image-frame
						:ratio="ratio"
						:cover="crop"
						:src="image.url"
						:srcset="image.image.srcset"
						:alt="image.alt"
						class="k-block-type-gallery-frame"
					/>
				</li>

				<k-block-background-dropdown :value="back" @input="onBack" />
			</template>
		</ul>
		<k-block-figure-caption
			v-if="content.caption"
			:disabled="disabled"
			:marks="captionMarks"
			:value="content.caption"
			@input="$emit('update', { caption: $event })"
		/>
	</figure>
</template>

<script>
import Block from "./Default.vue";

/**
 * @displayName BlockTypeGallery
 */
export default {
	extends: Block,
	emits: ["update"],
	data() {
		return {
			back: this.onBack() ?? "var(--block-color-back)"
		};
	},
	computed: {
		captionMarks() {
			return this.field("caption", { marks: true }).marks;
		},
		crop() {
			return this.content.crop;
		},
		isEmpty() {
			return !this.content.images?.length;
		},
		ratio() {
			return this.content.ratio;
		}
	},
	methods: {
		onBack(value) {
			const id = `kirby.galleryBlock.${this.endpoints.field}.${this.id}`;

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
.k-block-container.k-block-container-type-gallery {
	padding: 0;
}
.k-block-type-gallery-figure {
	padding: var(--spacing-3);
	border-radius: var(--rounded);
}
.k-block-type-gallery-figure:not([data-empty="true"]) {
	background: var(--block-back);
}

.k-block-type-gallery-figure ul {
	display: grid;
	grid-gap: 0.75rem;
	grid-template-columns: repeat(auto-fit, minmax(6rem, 1fr));
	line-height: 0;
	align-items: center;
	justify-content: center;
}
.k-block-type-gallery:not([data-disabled="true"])
	.k-block-type-gallery-figure
	ul {
	cursor: pointer;
}
.k-block-type-gallery-frame {
	border-radius: var(--rounded-sm);
}

.k-block-type-gallery[data-disabled="true"] .k-block-type-gallery-placeholder {
	background: light-dark(var(--color-gray-250), var(--color-gray-950));
}
.k-block-type-gallery-placeholder {
	background: var(--panel-color-back);
}
</style>
