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
					{{ $t("files.empty") }}
				</k-empty>
			</template>
			<template v-else>
				<ul class="k-upload-items">
					<li
						v-for="(file, index) in queue"
						:key="file.uuid"
						class="k-upload-item"
					>
						<a :href="file.url" class="k-upload-item-preview" target="_blank">
							<k-image
								v-if="file.type.match('(jpg|jpeg|gif|png|webp|avif)')"
								:cover="true"
								:src="file.url"
								back="black"
							/>
							<k-aspect-ratio v-else ratio="1/1">
								<k-icon type="file" />
							</k-aspect-ratio>
						</a>
						<div class="k-upload-item-body">
							<k-input
								v-model="file.name"
								:novalidate="true"
								:required="true"
								class="k-upload-item-input"
								type="slug"
							/>
							<p class="k-upload-item-meta">
								.{{ file.extension }} - {{ file.niceSize }}
							</p>
							<p class="k-upload-item-error">{{ file.error }}</p>
						</div>
						<k-progress
							v-if="file.progress > 0 && !file.error"
							:value="file.progress"
						/>
						<div class="k-upload-item-toggle">
							<input type="checkbox" v-model="file.upload" />
						</div>
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
		submitButton: {
			type: [String, Boolean],
			default: () => window.panel.$t("upload")
		}
	},
	computed: {
		queue() {
			return this.$panel.upload.files.filter((file) => file.progress !== 100);
		}
	}
};
</script>

<style>
.k-upload-dialog.k-dialog {
	--dialog-color-back: var(--color-dark);
	--dialog-color-text: var(--color-white);
	--dialog-width: 40rem;
}
.k-upload-dialog .k-dialog-footer {
	border-top: 0;
}
.k-upload-dialog .k-dialog-button-submit.k-button {
	color: var(--color-green-400);
}
.k-upload-dialog .k-empty {
	background: rgba(0, 0, 0, 0.25);
	border-color: var(--color-gray-900);
}

.k-upload-items {
	display: grid;
	gap: 0.25rem;
}
.k-upload-item {
	display: grid;
	grid-template-areas:
		"preview body toggle"
		"preview progress progess";
	grid-template-columns: 6rem 1fr 2.5rem;
	grid-template-rows: 1fr 1.25rem;
	gap: 0.75rem;
	border-radius: var(--rounded);
	overflow: hidden;
	background: var(--color-gray-900);
	height: 6rem;
}
.k-upload-item-preview {
	grid-area: preview;
	display: block;
	width: 100%;
	height: 100%;
	outline: 0;
}
.k-upload-item-preview .k-aspect-ratio > * {
	display: grid;
	place-items: center;
	color: var(--color-gray-500);
	border-right: 1px solid var(--color-dark);
}
.k-upload-item-body {
	grid-area: body;
	padding-top: var(--spacing-3);
}
.k-upload-item-input.k-input {
	font-size: var(--text-sm);
	margin-bottom: 0.5rem;
	border-radius: var(--rounded);
}
.k-upload-item-meta {
	font-size: var(--text-xs);
	color: var(--color-gray-500);
}
.k-upload-item-error {
	font-size: var(--text-xs);
	margin-top: 0.5rem;
	color: var(--color-red-400);
}
.k-upload-item .k-progress {
	grid-area: progress;
	--progress-height: 0.25rem;
	--progress-color-value: var(--color-green-400);
	--progress-color-back: var(--color-dark);
}
.k-upload-item-toggle {
	display: grid;
	place-items: center;
	aspect-ratio: 1/1;
	grid-area: toggle;
}
.k-upload-item:not(:has(:checked)) {
	opacity: 0.25;
}
</style>
