<template>
	<div class="k-default-file-preview">
		<k-file-preview-frame>
			<k-icon
				:color="$helper.color(image.color)"
				:type="image.icon"
				class="k-item-icon"
			/>
		</k-file-preview-frame>

		<k-file-preview-details :details="details" />
	</div>
</template>

<script>
/**
 * General file view preview for files
 * @since 5.0.0
 */
export default {
	props: {
		details: Array,
		image: {
			default: () => ({}),
			type: Object
		}
	}
};
</script>

<style>
.k-default-file-preview .k-file-preview-frame .k-item-icon {
	--icon-size: 3rem;
}

@container (min-width: 36rem) {
	.k-default-file-preview {
		grid-template-columns: 50% auto;
	}
	.k-default-file-preview-thumb-column {
		aspect-ratio: auto;
	}
}

@container (min-width: 65rem) {
	.k-default-file-preview {
		grid-template-columns: 33.333% auto;
	}
	.k-default-file-preview-thumb-column {
		aspect-ratio: 1/1;
	}
}
</style>
