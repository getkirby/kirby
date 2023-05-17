<template>
	<k-dialog
		ref="dialog"
		class="k-upload-dialog k-upload-replace-dialog"
		v-bind="$props"
		@submit="submit"
	>
		<ul class="k-upload-items">
			<li class="k-upload-original">
				<k-image
					v-if="original.url.match('(jpg|jpeg|gif|png|webp|avif)')"
					:cover="true"
					:src="original.url"
					back="black"
				/>
				<k-aspect-ratio v-else ratio="1/1">
					<k-icon
						back="pattern"
						v-bind="original.image"
						:type="original.image?.icon ?? 'file'"
					/>
				</k-aspect-ratio>
			</li>

			<li>&larr;</li>

			<li
				v-for="file in $panel.upload.files"
				:key="file.id"
				:data-completed="file.completed"
				class="k-upload-item"
			>
				<a :href="file.url" class="k-upload-item-preview" target="_blank">
					<k-image
						v-if="file.type.match('(jpg|jpeg|gif|png|webp|avif)')"
						:cover="true"
						:src="file.url"
						back="pattern"
					/>
					<k-aspect-ratio v-else ratio="1/1">
						<k-icon
							back="pattern"
							v-bind="original.image"
							:type="original.image?.icon ?? 'file'"
						/>
					</k-aspect-ratio>
				</a>
				<k-input
					:value="$helper.file.name(original.filename)"
					:disabled="true"
					:after="'.' + file.extension"
					class="k-upload-item-input"
					type="text"
				/>
				<div class="k-upload-item-body">
					<p class="k-upload-item-meta">
						{{ file.niceSize }}
						<template v-if="file.progress"> - {{ file.progress }}% </template>
					</p>
					<p class="k-upload-item-error">{{ file.error }}</p>
				</div>
				<div class="k-upload-item-progress">
					<k-progress
						v-if="file.progress > 0 && !file.error"
						:value="file.progress"
					/>
				</div>
				<div class="k-upload-item-toggle">
					<k-button
						v-if="file.completed"
						icon="check"
						theme="positive"
						@click="$panel.upload.remove(file.id)"
					/>
					<div v-else-if="file.progress">
						<k-loader />
					</div>
				</div>
			</li>
		</ul>
	</k-dialog>
</template>

<script>
import UploadDialog from "./UploadDialog.vue";

export default {
	extends: UploadDialog,
	props: {
		original: Object,
		submitButton: {
			type: [String, Boolean, Object],
			default: () => {
				return {
					icon: "upload",
					text: window.panel.$t("replace")
				};
			}
		}
	}
};
</script>

<style>
.k-upload-replace-dialog .k-upload-items {
	display: flex;
	gap: var(--spacing-3);
	align-items: center;
}

.k-upload-original {
	width: 6rem;
	border-radius: var(--rounded);
	box-shadow: var(--shadow);
	overflow: hidden;
}

.k-upload-replace-dialog .k-upload-item {
	flex-grow: 1;
}
</style>
