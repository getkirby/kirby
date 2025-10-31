<template>
	<k-dialog v-bind="$props" class="k-changes-dialog">
		<k-section
			v-if="pages.length"
			:buttons="buttons('pages')"
			:label="$t('lock.unsaved.pages')"
		>
			<k-items :items="pages" layout="list" />
		</k-section>

		<k-section
			v-if="files.length"
			:buttons="buttons('files')"
			:label="$t('lock.unsaved.files')"
		>
			<k-items :items="files" layout="list" />
		</k-section>

		<k-section
			v-if="users.length"
			:buttons="buttons('users')"
			:label="$t('lock.unsaved.users')"
		>
			<k-items :items="users" layout="list" />
		</k-section>

		<k-section
			v-if="!pages.length && !files.length && !users.length"
			:label="$t('lock.unsaved')"
		>
			<k-empty icon="edit-line">{{ $t("lock.unsaved.empty") }}</k-empty>
		</k-section>
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
		// eslint-disable-next-line vue/require-prop-types
		cancelButton: {
			default: false
		},
		files: {
			type: Array,
			default: () => []
		},
		pages: {
			type: Array,
			default: () => []
		},
		// eslint-disable-next-line vue/require-prop-types
		size: {
			default: "large"
		},
		// eslint-disable-next-line vue/require-prop-types
		submitButton: {
			default: false
		},
		users: {
			type: Array,
			default: () => []
		}
	},
	methods: {
		buttons(type) {
			return [
				{
					icon: "undo",
					text: this.$t("discard.all") || "Discard all",
					click: () => {
						this.$panel.dialog.open({
							component: "k-remove-dialog",
							props: {
								size: "medium",
								submitButton: {
									theme: "notice",
									icon: "undo",
									text: this.$t("form.discard")
								},
								text: `Do you really want to discard all unsaved changes for ${type}?`
							},
							on: {
								submit: () => this.discardAll(type)
							}
						});
					}
				},
				{
					icon: "check",
					text: this.$t("save.all") || "Save all",
					click: () => {
						this.$panel.dialog.open({
							component: "k-text-dialog",
							props: {
								size: "medium",
								submitButton: {
									theme: "notice",
									text: "Save changes"
								},
								text: `Do you really want to save all unsaved changes for ${type}?`
							},
							on: {
								submit: () => this.saveAll(type)
							}
						});
					}
				}
			];
		},
		async discardAll(type) {
			try {
				await this.$panel.api.post("/changes/discard/" + type);
				this.$panel.dialog.close();
				this.$panel.dialog.refresh();
			} catch (e) {
				this.$panel.notification.error(e);
			}
		},
		async saveAll(type) {
			try {
				await this.$panel.api.post("/changes/publish/" + type);
				this.$panel.dialog.close();
				this.$panel.dialog.refresh();
			} catch (e) {
				this.$panel.notification.error(e);
			}
		}
	}
};
</script>
