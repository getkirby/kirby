<template>
	<k-panel class="k-panel-inside k-page-comparison-view">
		<header class="k-page-comparison-header">
			<k-button-group>
				<k-button
					:link="backlink"
					icon="angle-left"
					size="sm"
					variant="filled"
				/>
				<k-button
					:icon="modeIcon"
					size="sm"
					variant="filled"
					@click="$refs.layout.toggle()"
				/>
				<k-dropdown-content ref="layout">
					<k-dropdown-item
						:current="mode === 'published'"
						icon="layout-right"
						@click="mode = 'published'"
					>
						Published Version
					</k-dropdown-item>
					<k-dropdown-item
						:current="mode === 'changes'"
						icon="layout-left"
						@click="mode = 'changes'"
					>
						Changed Version
					</k-dropdown-item>
					<hr />
					<k-dropdown-item
						:current="mode === 'side-by-side'"
						icon="layout-columns"
						@click="mode = 'side-by-side'"
					>
						Side by side
					</k-dropdown-item>
				</k-dropdown-content>
			</k-button-group>

			<k-form-controls
				:editor="lock.user.email"
				:is-locked="lock.isLocked"
				:is-unsaved="true"
				:modified="lock.modified"
				@discard="onDiscard"
				@submit="onSubmit"
			/>
		</header>
		<main class="k-page-comparison-grid" :data-mode="mode">
			<section v-if="mode === 'published' || mode === 'side-by-side'">
				<k-headline>Published version</k-headline>
				<iframe :src="published"></iframe>
			</section>
			<section v-if="mode === 'changes' || mode === 'side-by-side'">
				<k-headline>Changed version</k-headline>
				<iframe :src="changes"></iframe>
			</section>
		</main>
	</k-panel>
</template>

<script>
export default {
	props: {
		backlink: String,
		changes: String,
		lock: Object,
		published: String
	},
	data() {
		return {
			mode: "side-by-side"
		};
	},
	computed: {
		modeIcon() {
			const icons = {
				published: "layout-right",
				changes: "layout-left",
				"side-by-side": "layout-columns"
			};

			return icons[this.mode];
		}
	},
	methods: {
		onDiscard() {},
		onSubmit() {}
	}
};
</script>

<style>
.k-page-comparison-view {
	position: fixed;
	inset: 0;
	height: 100%;
	display: grid;
	grid-template-rows: auto 1fr;
}
.k-page-comparison-header {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: var(--spacing-2);
	border-bottom: 1px solid var(--color-border);
}
.k-page-comparison-grid {
	display: grid;
	grid-template-columns: 100%;
}
.k-page-comparison-grid[data-mode="side-by-side"] {
	grid-template-columns: 50% 50%;
}

.k-page-comparison-grid > section {
	display: flex;
	flex-direction: column;
	padding: var(--spacing-6);
}
.k-page-comparison-grid > section:first-child {
	border-right: 1px solid var(--color-border);
}
.k-page-comparison-grid > section .k-headline {
	margin-bottom: var(--spacing-3);
}
.k-page-comparison-grid iframe {
	width: 100%;
	flex-grow: 1;
	border-radius: var(--rounded-lg);
	box-shadow: var(--shadow-xl);
}
</style>
