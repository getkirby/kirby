<template>
	<k-field v-bind="$props" class="k-files-field">
		<template v-if="more && !disabled" #options>
			<k-button-group class="k-field-options">
				<k-options-dropdown ref="options" v-bind="options" @action="onAction" />
			</k-button-group>
		</template>

		<k-dropzone :disabled="!canUpload" @drop="drop">
			<k-collection
				v-bind="collection"
				@empty="prompt"
				@sort="onInput"
				@sortChange="$emit('change', $event)"
			>
				<template #options="{ index }">
					<k-button
						v-if="!disabled"
						:title="$t('remove')"
						icon="remove"
						@click="remove(index)"
					/>
				</template>
			</k-collection>
		</k-dropzone>

		<k-files-dialog ref="selector" :has-search="search" @submit="select" />
	</k-field>
</template>

<script>
import picker from "@/mixins/forms/picker.js";

/**
 * @example <k-files-field :value="files" @input="files = $event" name="files" label="Files" />
 */
export default {
	mixins: [picker],
	props: {
		uploads: [Boolean, Object, Array]
	},
	computed: {
		canUpload() {
			return !this.disabled && this.more && this.uploads;
		},
		emptyProps() {
			return {
				icon: "image",
				text: this.empty || this.$t("field.files.empty")
			};
		},
		options() {
			if (this.uploads) {
				return {
					icon: this.btnIcon,
					size: "xs",
					text: this.btnLabel,
					variant: "filled",
					options: [
						{ icon: "check", text: this.$t("select"), click: "open" },
						{ icon: "upload", text: this.$t("upload"), click: "upload" }
					]
				};
			}

			return {
				options: [
					{ icon: "check", text: this.$t("select"), click: () => this.open() }
				]
			};
		},
		uploadOptions() {
			return {
				accept: this.uploads.accept,
				max: this.max,
				multiple: this.multiple,
				url: this.$panel.urls.api + "/" + this.endpoints.field + "/upload",
				on: {
					done: this.onUpload
				}
			};
		}
	},
	created() {
		this.$events.$on("file.delete", this.removeById);
	},
	destroyed() {
		this.$events.$off("file.delete", this.removeById);
	},
	methods: {
		drop(files) {
			if (this.uploads === false) {
				return false;
			}

			return this.$panel.upload.open(files, this.uploadOptions);
		},
		isSelected(file) {
			return this.selected.find((f) => f.id === file.id);
		},
		onAction(action) {
			// no need for `action` modifier
			// as native button `click` prop requires
			// inline function when only one option available
			if (!this.canUpload) {
				return;
			}

			switch (action) {
				case "open":
					return this.open();
				case "upload":
					return this.$panel.upload.pick(this.uploadOptions);
			}
		},
		onUpload(files) {
			if (this.multiple === false) {
				this.selected = [];
			}

			for (const file of files) {
				if (!this.isSelected(file)) {
					this.selected.push(file);
				}
			}

			this.onInput();
			this.$events.$emit("model.update");
		},
		prompt() {
			if (this.disabled) {
				return false;
			}

			if (this.canUpload) {
				return this.$refs.options.toggle();
			}

			this.open();
		}
	}
};
</script>

<style>
.k-files-field[data-disabled="true"] .k-item * {
	pointer-events: all !important;
}
</style>
