<template>
	<figure>
		<ul @dblclick="open">
			<template v-if="content.images.length === 0">
				<li
					v-for="index in 5"
					:key="index"
					class="k-block-type-gallery-placeholder"
				>
					<k-aspect-ratio :ratio="ratio" />
				</li>
			</template>
			<template v-else>
				<li v-for="image in content.images" :key="image.id">
					<k-aspect-ratio :ratio="ratio" :cover="crop">
						<img
							:src="image.url"
							:srcset="image.image.srcset"
							:alt="image.alt"
						/>
					</k-aspect-ratio>
				</li>
			</template>
		</ul>
		<figcaption v-if="content.caption">
			<k-writer
				:inline="true"
				:marks="captionMarks"
				:value="content.caption"
				@input="$emit('update', { caption: $event })"
			/>
		</figcaption>
	</figure>
</template>

<script>
/**
 * @displayName BlockTypeGallery
 * @internal
 */
export default {
	computed: {
		captionMarks() {
			return this.field("caption", { marks: true }).marks;
		},
		crop() {
			return this.content.crop || false;
		},
		ratio() {
			return this.content.ratio || false;
		}
	}
};
</script>

<style>
.k-block-type-gallery ul {
	display: grid;
	grid-gap: 0.75rem;
	grid-template-columns: repeat(auto-fit, minmax(6rem, 1fr));
	line-height: 0;
	align-items: center;
	justify-content: center;
	cursor: pointer;
}
.k-block-type-gallery-placeholder {
	background: var(--color-background);
}
.k-block-type-gallery figcaption {
	padding-top: 0.5rem;
	color: var(--color-gray-600);
	font-size: var(--text-sm);
	text-align: center;
}
</style>
