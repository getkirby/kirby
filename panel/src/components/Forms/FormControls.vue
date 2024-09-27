<template>
	<k-button-group
		v-if="buttons.length"
		layout="collapsed"
		class="k-form-controls"
	>
		<k-button
			v-for="button in buttons"
			:key="button.text"
			v-bind="button"
			size="sm"
			variant="filled"
		/>
		<k-dropdown-content
			v-if="isLocked"
			ref="lock"
			align-x="end"
			class="k-form-controls-dropdown"
		>
			<p>
				{{ $t("form.locked") }}
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
						<dd>{{ $library.dayjs(modified).fromNow() }}</dd>
					</div>
				</dl>
			</template>
			<template v-if="preview">
				<hr />
				<k-dropdown-item :link="preview" icon="preview" target="_blank">
					{{ $t("form.preview") }}
				</k-dropdown-item>
			</template>
		</k-dropdown-content>
	</k-button-group>
</template>

<script>
/**
 * @displayName FormControls
 * @since 5.0.0
 */
export default {
	props: {
		editor: String,
		isLocked: Boolean,
		isUnsaved: Boolean,
		modified: String,
		/**
		 * Preview URL for changes
		 */
		preview: String
	},
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
						click: () => this.$refs.lock.toggle()
					}
				];
			}

			if (this.isUnsaved === true) {
				return [
					{
						theme: "notice",
						text: this.$t("discard"),
						icon: "undo",
						click: () => this.discard()
					},
					{
						theme: "notice",
						text: this.$t("save"),
						icon: "check",
						click: () => this.$emit("submit")
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
