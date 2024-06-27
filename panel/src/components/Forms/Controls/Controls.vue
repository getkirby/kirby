<template>
	<k-button-group layout="collapsed" class="k-form-controls">
		<k-button
			v-for="button in buttons"
			:key="button.text"
			v-bind="button"
			size="sm"
			variant="filled"
		/>

		<template v-if="dropdown.length">
			<k-button
				:theme="buttons[0].theme"
				icon="dots"
				size="sm"
				variant="filled"
				@click="$refs.dropdown.toggle()"
			/>
			<k-dropdown-content ref="dropdown" :options="dropdown" align-x="end" />
		</template>
	</k-button-group>
</template>

<script>
/**
 * @displayName FormControls
 * @since 5.0.0
 */
export default {
	props: {
		/**
		 * Whether the model is currently a draft
		 */
		isDraft: Boolean,
		/**
		 * Whether the content is locked, and if, by whom
		 */
		isLocked: [String, Boolean],
		/**
		 * Whether the content is fully published (no changes)
		 */
		isPublished: Boolean,
		/**
		 * Whether all content are saved
		 */
		isSaved: Boolean
	},
	emits: ["discard", "publish", "save"],
	computed: {
		buttons() {
			if (this.isLocked) {
				return [
					{
						theme: "negative",
						text: this.isLocked,
						icon: "lock",
						click: () => this.locked()
					}
				];
			}

			if (this.isPublished) {
				return [
					{
						theme: "passive",
						text: this.$t("form.published"),
						icon: "check",
						disabled: true
					}
				];
			}

			return [
				{
					theme: "positive",
					text: this.isSaved ? this.$t("form.saved") : this.$t("form.save"),
					icon: this.isSaved ? "check" : "draft",
					disabled: this.isSaved,
					click: () => this.$emit("save")
				},
				{
					theme: "positive",
					text: this.$t("form.publish"),
					icon: "live",
					click: () => this.$emit("publish")
				}
			];
		},
		dropdown() {
			const dropdown = [];

			if (this.isLocked) {
				return dropdown;
			}

			if (this.isPublished === false && this.isDraft === false) {
				dropdown.push({
					icon: "trash",
					text: this.$t("form.discard"),
					click: () => this.discard()
				});
			}

			return dropdown;
		}
	},
	methods: {
		discard() {
			this.$panel.dialog.open({
				component: "k-remove-dialog",
				props: {
					size: "medium",
					submitButton: {
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
		},
		locked() {
			this.$panel.notification.open({
				icon: "lock",
				theme: "negative",
				message: this.$t("form.locked")
			});
		}
	}
};
</script>
