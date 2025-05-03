<template>
	<k-panel class="k-panel-inside k-preview-view">
		<header class="k-preview-view-header">
			<k-button-group>
				<k-button
					:link="back"
					:responsive="true"
					:title="$t('back')"
					icon="angle-left"
					size="sm"
					variant="filled"
				>
				</k-button>
				<k-button icon="title" element="span">
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
		<main class="k-preview-view-grid" :data-mode="mode">
			<section
				v-if="mode === 'latest' || mode === 'compare'"
				class="k-preview-view-panel"
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
				<iframe ref="latest" :src="src.latest"></iframe>
			</section>

			<section
				v-if="mode === 'changes' || mode === 'compare'"
				class="k-preview-view-panel"
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
							:has-diff="hasDiff"
							:is-locked="isLocked"
							:modified="modified"
							size="sm"
							@discard="onDiscard"
							@submit="onSubmit"
						/>
					</k-button-group>
				</header>
				<iframe v-if="hasDiff" ref="changes" :src="src.changes"></iframe>
				<k-empty v-else>
					<template v-if="lock.isLegacy">
						This content is locked by our old lock system. <br />
						Changes cannot be previewed.
					</template>
					<template v-else>
						{{ $t("lock.unsaved.empty") }}
						<k-button icon="edit" variant="filled" :link="back">
							{{ $t("edit") }}
						</k-button>
					</template>
				</k-empty>
			</section>
		</main>
	</k-panel>
</template>

<script>
import ModelView from "@/components/Views/ModelView.vue";

export default {
	extends: ModelView,
	props: {
		back: String,
		mode: String,
		src: Object,
		title: String
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
	mounted() {
		this.$events.on("keydown.esc", this.onExit);
		this.$events.on("content.publish", this.onPublish);
	},
	destroyed() {
		this.$events.off("keydown.esc", this.onExit);
		this.$events.off("content.publish", this.onPublish);
	},
	methods: {
		changeMode(mode) {
			if (!mode || !this.modes[mode]) {
				return;
			}

			this.$panel.view.open(this.link + "/preview/" + mode);
		},
		onExit() {
			if (this.$panel.overlays().length > 0) {
				return;
			}

			this.$panel.view.open(this.link);
		},
		onPublish() {
			this.$refs.latest.contentWindow.location.reload();
		}
	}
};
</script>

<style>
.k-preview-view {
	position: fixed;
	inset: 0;
	height: 100%;
	display: grid;
	grid-template-rows: auto 1fr;
}
.k-preview-view-header {
	container-type: inline-size;
	display: flex;
	gap: var(--spacing-2);
	justify-content: space-between;
	align-items: center;
	padding: var(--spacing-2);
	border-bottom: 1px solid var(--color-border);
}
.k-preview-view-grid {
	display: flex;
}
@media screen and (max-width: 60rem) {
	.k-preview-view-grid {
		flex-direction: column;
	}
}
.k-preview-view-grid .k-preview-view-panel + .k-preview-view-panel {
	border-left: 1px solid var(--color-border);
}
.k-preview-view-panel {
	flex-grow: 1;
	flex-basis: 50%;
	display: flex;
	flex-direction: column;
	padding: var(--spacing-6);
	background: var(--panel-color-back);
}
.k-preview-view-panel header {
	container-type: inline-size;
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: var(--spacing-3);
}
.k-preview-view-panel iframe {
	width: 100%;
	flex-grow: 1;
	border-radius: var(--rounded-lg);
	box-shadow: var(--shadow-xl);
	background: light-dark(var(--color-white), var(--color-gray-950));
}
.k-preview-view-panel .k-empty {
	flex-grow: 1;
	justify-content: center;
	flex-direction: column;
	text-align: center;
	padding-inline: var(--spacing-3);
	gap: var(--spacing-6);
	--button-color-text: var(--color-text);
}
</style>
