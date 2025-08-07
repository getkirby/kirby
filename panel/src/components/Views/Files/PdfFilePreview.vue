<template>
	<div
		class="k-default-file-preview k-pdf-file-preview"
		:data-supported="supported"
	>
		<object
			:data="url"
			type="application/pdf"
			class="k-pdf-file-preview-object"
		>
			<k-file-preview-frame>
				<a :href="url">
					<k-icon
						:color="$helper.color(image.color)"
						:type="image.icon"
						class="k-item-icon"
					/>
				</a>
			</k-file-preview-frame>
		</object>

		<k-file-preview-details :details="details" />
	</div>
</template>

<script>
/**
 * File view preview for PDF documents
 * @since 5.0.0
 */
export default {
	props: {
		details: Array,
		image: {
			default: () => ({}),
			type: Object
		},
		url: String
	},
	computed: {
		supported() {
			return window.navigator.pdfViewerEnabled;
		}
	}
};
</script>

<style>
.k-pdf-file-preview[data-supported="true"] {
	grid-template-columns: 1fr;
}

.k-pdf-file-preview .k-pdf-file-preview-object {
	width: 100%;
}

.k-pdf-file-preview[data-supported="true"] .k-pdf-file-preview-object {
	aspect-ratio: 1/1;
	border-bottom: 1px solid var(--color-gray-850);
}

@container (min-width: 36rem) {
	.k-pdf-file-preview[data-supported="true"] .k-pdf-file-preview-object {
		aspect-ratio: 3/2;
	}
}

@container (min-width: 60rem) {
	.k-pdf-file-preview[data-supported="true"] {
		grid-template-columns: 70% auto;
	}
	.k-pdf-file-preview[data-supported="true"] .k-pdf-file-preview-object {
		aspect-ratio: 5/3;
		border-bottom: 0;
		border-right: 1px solid var(--color-gray-850);
	}
}
</style>
