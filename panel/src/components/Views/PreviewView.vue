<template>
	<k-panel class="k-panel-inside k-preview-view" :data-version="version">
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
				<k-button class="k-preview-view-title" icon="title" element="span">
					{{ title }}
				</k-button>
			</k-button-group>

			<k-button-group>
				<p
					v-if="version === 'changes' && hasDiff === false"
					class="k-preview-view-no-changes"
				>
					{{ $t("lock.unsaved.empty") }}
				</p>
				<k-form-controls
					v-if="version === 'changes'"
					:editor="editor"
					:has-diff="hasDiff"
					:is-locked="isLocked"
					:modified="modified"
					@discard="onDiscard"
					@submit="onSubmit"
				/>
				<k-button
					icon="git-branch"
					size="sm"
					variant="filled"
					:dropdown="true"
					:responsive="true"
					@click="$refs.versions.toggle()"
				>
					{{ versionOptions[version].label }}
				</k-button>
				<k-dropdown-content
					ref="versions"
					:options="versionDropdown"
					align-x="end"
				/>
				<k-button
					:link="src[version]"
					icon="open"
					size="sm"
					target="_blank"
					variant="filled"
				/>
			</k-button-group>
		</header>
		<main class="k-preview-view-grid">
			<div class="k-preview-view-browser">
				<iframe ref="browser" :src="src[version]"></iframe>
			</div>
		</main>
	</k-panel>
</template>

<script>
import ModelView from "@/components/Views/ModelView.vue";

export default {
	extends: ModelView,
	props: {
		back: String,
		version: String,
		src: Object,
		title: String
	},
	computed: {
		versionOptions() {
			return {
				latest: {
					label: this.$t("version.latest"),
					icon: "git-branch",
					link: this.link + "/preview/latest",
					current: this.version === "latest"
				},
				changes: {
					label: this.$t("version.changes"),
					icon: "git-branch",
					link: this.link + "/preview/changes",
					current: this.version === "changes"
				}
			};
		},
		versionDropdown() {
			return [this.versionOptions.latest, "-", this.versionOptions.changes];
		}
	},
	mounted() {
		this.$events.on("keydown.esc", this.onExit);
		this.$events.on("content.discard", this.onRefresh);
		this.$events.on("content.publish", this.onRefresh);
	},
	destroyed() {
		this.$events.off("keydown.esc", this.onExit);
		this.$events.off("content.discard", this.onRefresh);
		this.$events.off("content.publish", this.onRefresh);
	},
	methods: {
		onExit() {
			if (this.$panel.overlays().length > 0) {
				return;
			}

			this.$panel.view.open(this.link);
		},
		onRefresh() {
			this.$refs.browser.contentWindow.location.reload();
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
.k-preview-view-no-changes {
	color: var(--color-text-dimmed);
	font-size: var(--text-xs);
	display: flex;
	margin-inline-end: var(--spacing-3);
}
.k-preview-view-grid {
	display: flex;
	padding: var(--spacing-3);
	justify-content: center;
}
@media screen and (max-width: 60rem) {
	.k-preview-view-title,
	.k-preview-view-no-changes {
		display: none;
	}
}
.k-preview-view-browser {
	flex-grow: 1;
}
.k-preview-view-browser iframe {
	width: 100%;
	height: 100%;
	border-radius: var(--rounded-lg);
	box-shadow: var(--shadow-xl);
	background: light-dark(var(--color-white), var(--color-gray-950));
}
</style>
