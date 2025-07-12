<template>
	<k-panel class="k-panel-inside k-preview-view" :data-version-id="versionId">
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
				<k-view-buttons :buttons="buttons" />
			</k-button-group>
		</header>
		<main class="k-preview-view-grid">
			<template v-if="versionId === 'compare'">
				<k-preview-browser
					v-bind="browserProps('latest')"
					@discard="onDiscard"
					@submit="onSubmit"
				/>
				<k-preview-browser
					v-bind="browserProps('changes')"
					@discard="onDiscard"
					@submit="onSubmit"
				/>
			</template>
			<template v-else>
				<k-preview-browser
					v-bind="browserProps(versionId)"
					@discard="onDiscard"
					@submit="onSubmit"
				/>
			</template>
		</main>
	</k-panel>
</template>

<script>
import ModelView from "@/components/Views/ModelView.vue";

export default {
	extends: ModelView,
	props: {
		back: String,
		versionId: String,
		src: Object,
		title: String
	},
	mounted() {
		this.$events.on("keydown.esc", this.exit);
	},
	unmounted() {
		this.$events.off("keydown.esc", this.exit);
	},
	methods: {
		browserProps(versionId) {
			return {
				editor: this.editor,
				hasDiff: this.hasDiff,
				isLocked: this.isLocked,
				modified: this.modified,
				label: this.$t("version." + versionId),
				src: this.src[versionId],
				versionId: versionId
			};
		},
		exit() {
			if (this.$panel.overlays().length > 0) {
				return;
			}

			this.$panel.view.open(this.link);
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
	padding: var(--spacing-3);
}
.k-preview-view-grid {
	display: flex;
	justify-content: center;
	padding: var(--spacing-3);
	padding-top: 0;
	gap: var(--spacing-3);
}
@media screen and (max-width: 60rem) {
	.k-preview-view-grid {
		flex-direction: column;
	}
	.k-preview-view-title {
		display: none;
	}
}
.k-preview-view .k-preview-browser {
	flex-grow: 1;
	flex-basis: 50%;
}
</style>
