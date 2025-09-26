<template>
	<k-button-group
		v-if="buttons.length"
		layout="collapsed"
		class="k-form-controls"
	>
		<k-button
			v-for="button in buttons"
			:key="button.text"
			class="k-form-controls-button"
			v-bind="button"
			:disabled="isProcessing"
			:size="size"
			variant="filled"
		/>
		<k-dropdown ref="dropdown" align-x="end" class="k-form-controls-dropdown">
			<p v-if="isLocked">
				{{ $t("form.locked") }}
			</p>
			<p v-else>
				{{ $t("form.unsaved") }}
			</p>

			<template v-if="editor || modified">
				<hr />
				<dl>
					<div v-if="editor">
						<dt><k-icon type="user" /></dt>
						<dd>{{ editor }}</dd>
					</div>
					<div v-if="modified">
						<dt><k-icon type="clock" /></dt>
						<dd>
							{{ $library.dayjs(modified).format("YYYY-MM-DD HH:mm:ss") }}
						</dd>
					</div>
				</dl>
			</template>

			<template v-if="preview">
				<hr />
				<k-dropdown-item :link="preview" icon="window">
					{{ $t("form.preview") }}
				</k-dropdown-item>
			</template>
		</k-dropdown>
	</k-button-group>
</template>

<script>
export const props = {
	props: {
		editor: String,
		hasDiff: Boolean,
		isLocked: Boolean,
		isProcessing: Boolean,
		modified: [String, Date],
		/**
		 * Preview URL for changes
		 */
		preview: [String, Boolean],
		size: {
			type: String,
			default: "sm"
		}
	}
};

/**
 * @displayName FormControls
 * @since 5.0.0
 */
export default {
	mixins: [props],
	emits: ["discard", "submit"],
	computed: {
		buttons() {
			if (this.isLocked === true) {
				return [
					{
						theme: "negative",
						dropdown: true,
						text: this.editor,
						icon: "lock",
						responsive: true,
						click: () => this.$refs.dropdown.toggle()
					}
				];
			}

			if (this.hasDiff === true) {
				return [
					{
						theme: "notice",
						text: this.$t("discard"),
						icon: "undo",
						responsive: true,
						click: () => this.discard()
					},
					{
						theme: "notice",
						text: this.$t("save"),
						icon: this.isProcessing ? "loader" : "check",
						click: () => this.$emit("submit")
					},
					{
						theme: "notice",
						icon: "dots",
						click: () => this.$refs.dropdown.toggle()
					}
				];
			}

			return [];
		}
	},
	methods: {
		discard() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					size: "medium",
					submitButton: {
						theme: "notice",
						icon: "undo",
						text: this.$t("form.discard")
					},
					text: this.$t("form.discard.confirm")
				},
				on: {
					submit: () => {
						this.$panel.dialog.close();
						this.$emit("discard");
					}
				}
			});
		}
	}
};
</script>

<style>
.k-form-controls-dropdown {
	max-width: 15rem;
}
.k-form-controls-dropdown p {
	line-height: var(--leading-normal);
	padding: var(--spacing-1) var(--spacing-2);
}
.k-form-controls-dropdown dl div {
	padding: var(--spacing-1) var(--spacing-2);
	line-height: var(--leading-normal);
	display: flex;
	align-items: center;
	gap: 0.75rem;
	color: var(--color-gray-500);
}
</style>
