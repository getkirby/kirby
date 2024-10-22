<template>
	<k-panel class="k-panel-inside k-page-changes-view">
		<header class="k-page-changes-header">
			<k-button-group>
				<k-button
					:link="link"
					:responsive="true"
					:text="$t('back')"
					icon="angle-left"
					size="sm"
					variant="filled"
				/>
				<k-button
					:icon="modes[mode].icon"
					:dropdown="true"
					:responsive="true"
					:text="$t('view')"
					size="sm"
					variant="filled"
					@click="$refs.layout.toggle()"
				/>
				<k-dropdown-content ref="layout" :options="dropdown" />
			</k-button-group>

			<k-form-controls
				:editor="editor"
				:is-locked="isLocked"
				:is-unsaved="isUnsaved"
				:modified="modified"
				@discard="onDiscard"
				@submit="onSubmit"
			/>
		</header>
		<main class="k-page-changes-grid" :data-mode="mode">
			<section v-if="mode === 'latest' || mode === 'compare'">
				<k-headline>
					{{ modes.latest.label }}
					<k-button
						:link="src.latest"
						icon="open"
						size="xs"
						target="_blank"
						variant="filled"
					/>
				</k-headline>
				<iframe :src="src.latest"></iframe>
			</section>
			<section v-if="mode === 'changes' || mode === 'compare'">
				<k-headline>
					{{ modes.changes.label }}
					<k-button
						v-if="isUnsaved"
						:link="src.changes"
						icon="open"
						size="xs"
						target="_blank"
						variant="filled"
					/>
				</k-headline>
				<iframe v-if="isUnsaved" :src="src.changes"></iframe>
				<k-empty v-else>{{ $t("lock.unsaved.empty") }}</k-empty>
			</section>
		</main>
	</k-panel>
</template>

<script>
import PageView from "@/components/Views/Pages/PageView.vue";

export default {
	extends: PageView,
	props: {
		src: Object
	},
	data() {
		return {
			mode: "compare"
		};
	},
	computed: {
		modes() {
			return {
				latest: {
					label: this.$t("version.latest"),
					icon: "layout-right",
					current: this.mode === "latest",
					click: () => (this.mode = "latest")
				},
				changes: {
					label: this.$t("version.changes"),
					icon: "layout-left",
					current: this.mode === "changes",
					click: () => (this.mode = "changes")
				},
				compare: {
					label: this.$t("version.compare"),
					icon: "layout-columns",
					current: this.mode === "compare",
					click: () => (this.mode = "compare")
				}
			};
		},
		dropdown() {
			return [this.modes.latest, this.modes.changes, "-", this.modes.compare];
		}
	},
	methods: {
		async onDiscard() {
			if (this.isLocked === true) {
				return false;
			}

			await this.$panel.content.discard();
			await this.$panel.view.open(this.link);
		},
		async onSubmit() {
			if (this.isLocked === true) {
				return false;
			}

			await this.$panel.content.publish();
			await this.$panel.view.open(this.link);
		}
	}
};
</script>

<style>
.k-page-changes-view {
	position: fixed;
	inset: 0;
	height: 100%;
	display: grid;
	grid-template-rows: auto 1fr;
}
.k-page-changes-header {
	container-type: inline-size;
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: var(--spacing-2);
	border-bottom: 1px solid var(--color-border);
}
.k-page-changes-grid {
	display: grid;
	grid-template-columns: 100%;
}

@media screen and (min-width: 50rem) {
	.k-page-changes-grid[data-mode="compare"] {
		grid-template-columns: 50% 50%;
	}
}

.k-page-changes-grid > section {
	display: flex;
	flex-direction: column;
	padding: var(--spacing-6);
}
.k-page-changes-grid > section:first-child {
	border-right: 1px solid var(--color-border);
}
.k-page-changes-grid > section .k-headline {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: var(--spacing-3);
}
.k-page-changes-grid iframe {
	width: 100%;
	flex-grow: 1;
	border-radius: var(--rounded-lg);
	box-shadow: var(--shadow-xl);
	background: var(--color-white);
}
.k-page-changes-grid .k-empty {
	flex-grow: 1;
	justify-content: center;
}
</style>
