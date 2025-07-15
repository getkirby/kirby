<template>
	<k-lab-examples>
		<k-box theme="info">
			Try these examples with the actual Panel menu collapsed as otherwise the
			global open state will overwrite the local example state.
		</k-box>

		<k-lab-example label="Sections">
			<k-panel-menu :items="items" />
		</k-lab-example>
		<k-lab-example label="Has search">
			<k-panel-menu :items="items" :searches="searches" />
		</k-lab-example>
		<k-lab-example label="Open/Close (and hovered)">
			<div class="k-panel" :data-menu="isOpen">
				<k-panel-menu
					:items="items"
					:is-hovered="isHovered"
					:is-open="isOpen"
					@hover="isHovered = $event"
					@toggle="isOpen = !isOpen"
				/>
			</div>
		</k-lab-example>
		<k-lab-example label="License: missing">
			<k-panel-menu :items="items" license="missing" />
		</k-lab-example>
		<k-lab-example label="License: legacy">
			<k-panel-menu :items="items" license="legacy" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			isHovered: true,
			isOpen: true
		};
	},
	computed: {
		items() {
			return [
				{
					text: "Site",
					icon: "home",
					current: true
				},
				{
					text: "Users",
					icon: "users"
				},
				{
					text: "Settings",
					icon: "settings"
				},
				"-",
				{
					text: "Changes",
					icon: "edit-line"
				},
				{
					text: "Account",
					icon: "account"
				},
				{
					text: "Sign out",
					icon: "logout"
				}
			];
		},
		searches() {
			return {
				shops: {}
			};
		}
	}
};
</script>

<style>
.k-lab-example-canvas {
	position: relative;
	min-height: 20rem;
}
.k-lab-example-canvas .k-panel-menu {
	position: absolute;
	z-index: var(--z-content);
}
</style>
