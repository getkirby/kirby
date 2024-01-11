<template>
	<k-dialog
		ref="dialog"
		class="k-upload-dialog"
		v-bind="$props"
		:disabled="disabled || $panel.upload.files.length === 0"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<k-dropzone @drop="$panel.upload.select($event)">
			<template v-if="$panel.upload.files.length === 0">
				<k-empty icon="upload" layout="cards" @click="$panel.upload.pick()">
					{{ $t("files.empty") }}
				</k-empty>
			</template>
			<template v-else>
				<ul class="k-upload-items">
					<li
						v-for="file in $panel.upload.files"
						:key="file.id"
						:data-completed="file.completed"
						class="k-upload-item"
					>
						<a :href="file.url" class="k-upload-item-preview" target="_blank">
							<k-image-frame
								v-if="isPreviewable(file.type)"
								:cover="true"
								:src="file.url"
								back="pattern"
							/>
							<k-icon-frame
								v-else
								back="black"
								color="white"
								ratio="1/1"
								icon="file"
							/>
						</a>
						<k-input
							:disabled="file.completed"
							:after="'.' + file.extension"
							:novalidate="true"
							:required="true"
							:value="file.name"
							class="k-upload-item-input"
							type="slug"
							@input="file.name = $event"
						/>
						<div class="k-upload-item-body">
							<p class="k-upload-item-meta">
								{{ file.niceSize }}
								<template v-if="file.progress">
									- {{ file.progress }}%
								</template>
							</p>
							<p v-if="file.error" class="k-upload-item-error">
								{{ file.error }}
							</p>
							<k-progress
								v-else-if="file.progress"
								:value="file.progress"
								class="k-upload-item-progress"
							/>
						</div>
						<div class="k-upload-item-toggle">
							<k-button
								v-if="!file.completed && !file.progress"
								icon="remove"
								@click="$panel.upload.remove(file.id)"
							/>

							<div v-else-if="!file.completed">
								<k-icon type="loader" />
							</div>

							<k-button
								v-else
								icon="check"
								theme="positive"
								@click="$panel.upload.remove(file.id)"
							/>
						</div>
					</li>
				</ul>
			</template>
		</k-dropzone>
	</k-dialog>
</template>

<script>
import Dialog from "@/mixins/dialog.js";

/**
 * @since 4.0.0
 */
export default {
	mixins: [Dialog],
	props: {
		submitButton: {
			type: [String, Boolean, Object],
			default: () => {
				return {
					icon: "upload",
					text: window.panel.$t("upload")
				};
			}
		}
	},
	methods: {
		isPreviewable(mime) {
			return [
				"image/jpeg",
				"image/jpg",
				"image/gif",
				"image/png",
				"image/webp",
				"image/avif",
				"image/svg+xml"
			].includes(mime);
		}
	}
};
</script>

<style>
.k-upload-dialog.k-dialog {
	--dialog-width: 40rem;
}

.k-upload-items {
	display: grid;
	gap: 0.25rem;
}
.k-upload-item {
	accent-color: var(--color-focus);
	display: grid;
	grid-template-areas:
		"preview input input"
		"preview body toggle";
	grid-template-columns: 6rem 1fr auto;
	grid-template-rows: var(--input-height) 1fr;
	border-radius: var(--rounded);
	background: var(--color-white);
	box-shadow: var(--shadow);
	min-height: 6rem;
}
.k-upload-item-preview {
	grid-area: preview;
	display: flex;
	width: 100%;
	height: 100%;
	overflow: hidden;
	border-start-start-radius: var(--rounded);
	border-end-start-radius: var(--rounded);
}
.k-upload-item-preview:focus {
	border-radius: var(--rounded);
	outline: 2px solid var(--color-focus);
	z-index: 1;
}

.k-upload-item-body {
	grid-area: body;
	display: flex;
	flex-direction: column;
	justify-content: space-between;
	padding: var(--spacing-2) var(--spacing-3);
	min-width: 0;
}
.k-upload-item-input.k-input {
	--input-color-border: transparent;
	--input-padding: var(--spacing-2) var(--spacing-3);
	--input-rounded: 0;
	grid-area: input;
	font-size: var(--text-sm);
	border-bottom: 1px solid var(--color-light);
}
.k-upload-item-input.k-input:focus-within {
	outline: 2px solid var(--color-focus);
	z-index: 1;
	border-radius: var(--rounded);
}
.k-upload-item-input .k-input-after {
	color: var(--color-gray-600);
}
.k-upload-item-meta {
	font-size: var(--text-xs);
	color: var(--color-gray-600);
}
.k-upload-item-error {
	font-size: var(--text-xs);
	margin-top: 0.25rem;
	color: var(--color-red-700);
}
.k-upload-item-progress {
	--progress-height: 0.25rem;
	--progress-color-back: var(--color-light);
}
.k-upload-item-toggle {
	grid-area: toggle;
	align-self: end;
}
.k-upload-item-toggle > * {
	padding: var(--spacing-3);
}
.k-upload-item[data-completed="true"] .k-upload-item-progress {
	--progress-color-value: var(--color-green-400);
}
</style>
