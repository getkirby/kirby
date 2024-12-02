<template>
	<li :data-completed="completed" class="k-upload-item">
		<k-upload-item-preview
			:back="back"
			:color="color"
			:cover="cover"
			:icon="icon"
			:type="type"
			:url="url"
		/>

		<k-input
			:disabled="completed || !editable"
			:after="'.' + extension"
			:required="true"
			:value="name"
			allow="a-z0-9@._-"
			class="k-upload-item-input"
			type="slug"
			@input="$emit('rename', $event)"
		/>

		<div class="k-upload-item-body">
			<p class="k-upload-item-meta">
				{{ niceSize }}
				<template v-if="progress"> - {{ progress }}% </template>
			</p>
			<p v-if="error" class="k-upload-item-error" data-theme="negative">
				{{ error }}
			</p>
			<k-progress
				v-else-if="progress"
				:value="progress"
				class="k-upload-item-progress"
			/>
		</div>

		<div class="k-upload-item-toggle">
			<k-button
				v-if="!completed && !progress && removable"
				icon="remove"
				@click="$emit('remove')"
			/>
			<k-button
				v-else-if="!completed && progress"
				:disabled="true"
				icon="loader"
			/>
			<k-button
				v-else-if="completed"
				icon="check"
				theme="positive"
				@click="$emit('remove')"
			/>
		</div>
	</li>
</template>

<script>
import { props as Preview } from "./UploadItemPreview.vue";

/**
 * Represents one file to upload in an upload dialog
 * @since 4.3.0
 */
export default {
	mixins: [Preview],
	props: {
		/**
		 * Whether the upload is completed
		 */
		completed: Boolean,
		/**
		 * Whether the file name is editable
		 */
		editable: {
			type: Boolean,
			default: true
		},
		/**
		 * Upload error to display
		 */
		error: [String, Boolean],
		/**
		 * File extension
		 */
		extension: String,
		/**
		 * Unique id of file
		 */
		id: String,
		/**
		 * Filename
		 */
		name: String,
		/**
		 * File size to display
		 */
		niceSize: String,
		/**
		 * Upload progress
		 * @value 0-100
		 */
		progress: Number,
		/**
		 * Whether the file is removable
		 */
		removable: {
			type: Boolean,
			default: true
		}
	},
	emits: ["remove", "rename"]
};
</script>

<style>
:root {
	--upload-item-color-back: var(--item-color-back);
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
	background: var(--upload-item-color-back);
	box-shadow: var(--shadow);
	min-height: 6rem;
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
	border-bottom: 1px solid var(--color-border);
	border-start-end-radius: var(--rounded);
}
.k-upload-item-input.k-input:focus-within {
	outline: 2px solid var(--color-focus);
	z-index: 1;
	border-radius: var(--rounded);
}
.k-upload-item-input.k-input[data-disabled="true"] {
	--input-color-back: var(--upload-item-color-back);
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
	color: var(--theme-color-text);
}
.k-upload-item-progress {
	--progress-height: 0.25rem;
	--progress-color-back: var(--panel-color-back);
	margin-bottom: 0.3125rem;
}
.k-upload-item-toggle {
	grid-area: toggle;
	align-self: end;
}
.k-upload-item-toggle > * {
	padding: var(--spacing-3);
}
</style>
