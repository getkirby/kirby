<template>
	<div class="k-file-default-preview">
		<k-file-preview-thumb>
			<k-icon
				:color="$helper.color(image.color)"
				:type="image.icon"
				class="k-item-icon"
			/>
		</k-file-preview-thumb>

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
		details: {
			default: () => [],
			type: Array
		},
		image: {
			default: () => ({}),
			type: Object
		}
	}
};
</script>

<style>
.k-file-default-preview {
	display: grid;
	align-items: stretch;
}

.k-file-default-preview .k-file-preview-thumb-column {
	aspect-ratio: 1/1;
}

.k-file-default-preview .k-file-preview-thumb > .k-icon {
	--icon-size: 3rem;
}

@container (min-width: 36rem) {
	.k-file-default-preview {
		grid-template-columns: 50% auto;
	}
	.k-file-default-preview-thumb-column {
		aspect-ratio: auto;
	}
}

@container (min-width: 65rem) {
	.k-file-default-preview {
		grid-template-columns: 33.333% auto;
	}
	.k-file-default-preview-thumb-column {
		aspect-ratio: 1/1;
	}
}
</style>
