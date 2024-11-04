<template>
	<k-panel class="k-panel-inside k-page-changes-view">
		<header class="k-page-changes-header">
			<k-button-group>
				<k-button
					:link="link"
					:responsive="true"
					:title="$t('back')"
					icon="angle-left"
					size="sm"
					variant="filled"
					@click="$refs.tree.toggle()"
				>
				</k-button>
				<k-button icon="page" element="span">
					{{ title }}
				</k-button>
			</k-button-group>
			<k-button-group>
				<k-button
					:icon="modes[mode].icon"
					:dropdown="true"
					:responsive="true"
					:title="modes[mode].label"
					size="sm"
					variant="filled"
					@click="$refs.view.toggle()"
				/>
				<k-dropdown-content ref="view" :options="dropdown" align-x="end" />
			</k-button-group>
		</header>
		<main class="k-page-changes-grid" :data-mode="mode">
			<section
				v-if="mode === 'latest' || mode === 'compare'"
				class="k-page-changes-panel"
			>
				<header>
					<k-headline>{{ modes.latest.label }}</k-headline>
					<k-button-group>
						<k-button
							size="sm"
							variant="filled"
							:icon="
								mode === 'compare' ? 'expand-horizontal' : 'collapse-horizontal'
							"
							@click="changeMode(mode === 'compare' ? 'latest' : 'compare')"
						/>
						<k-button
							:link="src.latest"
							icon="open"
							size="sm"
							target="_blank"
							variant="filled"
						/>
					</k-button-group>
				</header>
				<iframe ref="latestFrame" :src="src.latest"></iframe>
			</section>

			<section
				v-if="mode === 'changes' || mode === 'compare'"
				class="k-page-changes-panel"
			>
				<header>
					<k-headline>{{ modes.changes.label }}</k-headline>
					<k-button-group>
						<k-button
							size="sm"
							variant="filled"
							:icon="
								mode === 'compare' ? 'expand-horizontal' : 'collapse-horizontal'
							"
							@click="changeMode(mode === 'compare' ? 'changes' : 'compare')"
						/>
						<k-button
							:link="src.changes"
							icon="open"
							size="sm"
							target="_blank"
							variant="filled"
						/>
						<k-form-controls
							:editor="editor"
							:is-locked="isLocked"
							:is-unsaved="isUnsaved"
							:modified="modified"
							size="sm"
							@discard="onDiscard"
							@submit="onSubmit"
						/>
					</k-button-group>
				</header>
				<iframe v-if="isUnsaved" ref="changesFrame" :src="src.changes"></iframe>
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
				changes: {
					label: this.$t("version.changes"),
					icon: "layout-left",
					current: this.mode === "changes",
					click: () => this.changeMode("changes")
				},
				compare: {
					label: this.$t("version.compare"),
					icon: "layout-columns",
					current: this.mode === "compare",
					click: () => this.changeMode("compare")
				},
				latest: {
					label: this.$t("version.latest"),
					icon: "layout-right",
					current: this.mode === "latest",
					click: () => this.changeMode("latest")
				}
			};
		},
		dropdown() {
			return [this.modes.compare, "-", this.modes.latest, this.modes.changes];
		}
	},
	watch: {
		id: {
			handler() {
				this.changeMode(localStorage.getItem("kirby$preview$mode"));
			},
			immediate: true
		}
	},
	methods: {
		changeMode(mode) {
			if (!mode || !this.modes[mode]) {
				return;
			}

			this.mode = mode;
			localStorage.setItem("kirby$preview$mode", mode);
		},
		async onDiscard() {
			if (this.isLocked === true) {
				return false;
			}

			await this.$panel.content.discard();
			await this.$panel.view.reload();
		},
		async onSubmit() {
			if (this.isLocked === true) {
				return false;
			}

			await this.$panel.content.publish();
			await this.$panel.reload();
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
	gap: var(--spacing-2);
	justify-content: space-between;
	align-items: center;
	padding: var(--spacing-2);
	border-bottom: 1px solid var(--color-border);
}

@media screen and (max-width: 40rem) {
	.k-page-changes-header-controls > .k-button {
		--button-text-display: none;
	}
}

.k-page-changes-grid {
	display: flex;
}
@media screen and (max-width: 60rem) {
	.k-page-changes-grid {
		flex-direction: column;
	}
}
.k-page-changes-grid .k-page-changes-panel + .k-page-changes-panel {
	border-left: 1px solid var(--color-border);
}
.k-page-changes-panel {
	flex-grow: 1;
	display: flex;
	flex-direction: column;
	padding: var(--spacing-6);
	background: var(--color-gray-200);
}
.k-page-changes-panel header {
	container-type: inline-size;
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
