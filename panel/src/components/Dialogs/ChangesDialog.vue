<template>
	<k-dialog v-bind="$props" class="k-changes-dialog">
		<header slot="header" class="k-changes-dialog-header">
			<nav class="k-tabs">
				<k-button class="k-tabs-button" :current="true">
					Your changes
				</k-button>
				<k-button class="k-tabs-button"> Team changes </k-button>
			</nav>

			<k-button-group :buttons="buttons()" size="xs" />
		</header>

		<k-table :index="false" :columns="columns" :rows="rows" />

		<!-- <k-section v-if="pages.length" :label="$t('lock.unsaved.pages')">
			<k-items :items="items(pages)" layout="list" />
		</k-section>

		<k-section v-if="files.length" :label="$t('lock.unsaved.files')">
			<k-items :items="items(files)" layout="list" />
		</k-section>

		<k-section v-if="users.length" :label="$t('lock.unsaved.users')">
			<k-items :items="items(users)" layout="list" />
		</k-section> -->

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
			default: "huge"
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
	computed: {
		columns() {
			return {
				image: {
					label: "",
					width: "var(--table-row-height)",
					type: "image",
					mobile: true
				},
				text: {
					label: "Title",
					type: "url",
					mobile: true
				},
				type: {
					type: "tags",
					width: "6rem",
					mobile: true
				},
				when: {
					type: "text",
					width: "10rem"
				},
				user: {
					width: "12rem",
					type: "text"
				}
			};
		},
		rows() {
			return this.items([...this.pages, ...this.files, ...this.users]);
		}
	},
	methods: {
		buttons() {
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
								text: `Do you really want to discard all unsaved changes?`
							},
							on: {
								submit: () => this.discardAll()
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
								text: `Do you really want to save all unsaved changes?`
							},
							on: {
								submit: () => this.saveAll()
							}
						});
					}
				}
			];
		},
		async discard(model) {
			try {
				await this.$panel.api.post(model.link + "/changes/discard");
				this.$panel.dialog.refresh();
			} catch (e) {
				this.$panel.notification.error(e);
			}
		},
		async discardAll() {
			try {
				await this.$panel.api.post("/changes/discard");
				this.$panel.dialog.close();
				this.$panel.dialog.refresh();
			} catch (e) {
				this.$panel.notification.error(e);
			}
		},
		items(items) {
			items = items.map((item) => {
				item.text = {
					text: item.text,
					href: item.link
				};

				item.options = [
					{
						icon: "undo",
						text: "Discard",
						click: () => this.discard(item)
					},
					{
						icon: "check",
						text: "Save",
						click: () => this.save(item)
					},
					"-",
					{
						icon: "window",
						text: "Preview changes",
						link: this.$panel.url(item.link + "/preview/changes").toString(),
						target: "_blank"
					}
				];

				return item;
			});

			return this.$helper.array.sortBy(items, "modified desc");
		},
		preview(model) {
			this.$panel.view.open(model.link + "/preview/changes");
		},
		async save(model) {
			try {
				await this.$panel.api.post(model.link + "/changes/publish");
				this.$panel.dialog.refresh();
			} catch (e) {
				this.$panel.notification.error(e);
			}
		},
		async saveAll() {
			try {
				await this.$panel.api.post("/changes/publish");
				this.$panel.dialog.close();
				this.$panel.dialog.refresh();
			} catch (e) {
				this.$panel.notification.error(e);
			}
		}
	}
};
</script>

<style>
.k-changes-dialog {
	--dialog-padding: 0;
	max-height: 100%;
}
.k-changes-dialog-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	height: var(--height-xl);
	flex-shrink: 0;
	padding-inline: var(--spacing-3);
	border-bottom: 1px solid var(--color-gray-900);
	z-index: 1;
}
.k-changes-dialog-header .k-tabs {
	margin-bottom: 0px;
}

.k-changes-dialog .k-dialog-body {
	--header-sticky-offset: 0;
	flex-grow: 1;
	overflow-y: scroll;
	overflow-x: hidden;
}
.k-changes-dialog .k-dialog-body .k-table th {
	border-radius: 0;
}
</style>
