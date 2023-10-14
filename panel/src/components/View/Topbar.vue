<template>
	<div class="k-topbar">
		<!-- mobile menu opener -->
		<k-button
			icon="bars"
			class="k-panel-menu-proxy"
			@click="$panel.menu.toggle()"
		/>

		<!-- breadcrumb -->
		<k-breadcrumb
			:crumbs="breadcrumb"
			:root="view"
			class="k-topbar-breadcrumb"
		/>

		<div class="k-topbar-spacer" />

		<div class="k-topbar-signals">
			<slot />
		</div>
	</div>
</template>

<script>
export default {
	props: {
		breadcrumb: Array,
		license: Boolean,
		menu: Array,
		title: String,
		view: Object
	}
};
</script>

<style>
.k-topbar {
	position: relative;
	margin-inline: calc(var(--button-padding) * -1);
	margin-bottom: var(--spacing-8);
	display: flex;
	align-items: center;
	gap: var(--spacing-1);
}

.k-topbar-breadcrumb {
	margin-inline-start: -2px;
}

.k-topbar-spacer {
	flex-grow: 1;
}

.k-topbar-signals {
	display: flex;
	align-items: center;
}
</style>
