<template>
	<k-dialog
		ref="dialog"
		class="k-upload-dialog"
		v-bind="$props"
		@cancel="cancel"
		@submit="submit"
	>
		<k-dropzone @drop="$panel.upload.select($event)">
			<template v-if="$panel.upload.isEmpty()">
				<k-empty icon="upload" layout="cards" @click="$panel.upload.open()">
					No files selected
				</k-empty>
			</template>
			<template v-else>
				<ul class="k-items k-cards-items" data-layout="cards" data-size="tiny">
					<li v-for="(file, index) in $panel.upload.files" :key="file.uuid">
						<a :href="file.url" target="_blank">
							<k-image :src="file.url" back="black" />
						</a>
						<footer>
							<k-input type="slug" v-model="file.name" />
							<p>{{ file.type }} {{ file.niceSize }}</p>
						</footer>
					</li>
				</ul>
			</template>
		</k-dropzone>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

export default {
	mixins: [Dialog],
	props: {
		size: {
			default: "large",
			type: String
		},
		submitButton: {
			type: [String, Boolean],
			default: () => window.panel.$t("upload")
		}
	}
};
</script>

<style>
.k-upload-dialog {
	--dialog-color-back: var(--color-dark);
	--dialog-color-text: var(--color-white);
}
.k-upload-dialog .k-dialog-footer {
	border-top: 0;
}
.k-upload-dialog li {
	border-radius: var(--rounded);
	overflow: hidden;
	background: var(--color-gray-900);
}
.k-upload-dialog li footer {
	padding: var(--spacing-3);
}
.k-upload-dialog li .k-input {
	font-size: var(--text-sm);
	margin-bottom: 0.5rem;
}
.k-upload-dialog li footer p {
	font-size: var(--text-xs);
	color: var(--color-gray-500);
}
.k-upload-dialog .k-dialog-button-submit.k-button {
	color: var(--color-green-400);
}
</style>
