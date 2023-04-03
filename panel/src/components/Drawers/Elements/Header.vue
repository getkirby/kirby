<template>
	<header class="k-drawer-header">
		<h2 v-if="breadcrumb.length <= 1" class="k-drawer-title">
			<k-icon :type="icon" /> {{ title }}
		</h2>
		<ul v-else class="k-drawer-breadcrumb">
			<li v-for="crumb in breadcrumb" :key="crumb.id">
				<k-button
					:icon="crumb.icon"
					:text="crumb.title"
					@click="$emit('openCrumb', crumb)"
				/>
			</li>
		</ul>
		<k-drawer-tabs
			:tab="tab"
			:tabs="tabs"
			@openTab="$emit('openTab', $event)"
		/>
		<nav class="k-drawer-options">
			<slot />
			<k-button class="k-drawer-option" icon="check" type="submit" />
		</nav>
	</header>
</template>

<script>
import { props as Tabs } from "./Tabs.vue";

export const props = {
	mixins: [Tabs],
	props: {
		breadcrumb: {
			default() {
				return [];
			},
			type: Array
		},
		buttons: {
			default() {
				return {};
			},
			type: [Object, Array]
		},
		icon: {
			type: String,
			default: "box"
		},
		title: String
	}
};

export default {
	mixins: [props]
};
</script>

<style>
.k-drawer-header {
	flex-shrink: 0;
	height: var(--drawer-header-height);
	padding-inline-start: var(--drawer-header-padding);
	display: flex;
	align-items: center;
	line-height: 1;
	justify-content: space-between;
	background: var(--color-white);
	font-size: var(--text-sm);
}
.k-drawer-title {
	padding: 0 0.75rem;
}
.k-drawer-title,
.k-drawer-breadcrumb {
	display: flex;
	flex-grow: 1;
	align-items: center;
	min-width: 0;
	margin-inline-start: -0.75rem;
	font-size: var(--text-sm);
	font-weight: var(--font-normal);
}
.k-drawer-breadcrumb li:not(:last-child) .k-button::after {
	position: absolute;
	inset-inline-end: -0.75rem;
	width: 1.5rem;
	display: inline-flex;
	justify-content: center;
	align-items: center;
	content: "›";
	color: var(--color-gray-500);
	height: var(--drawer-header-height);
}
.k-drawer-title .k-icon,
.k-drawer-breadcrumb .k-icon {
	width: 1rem;
	color: var(--color-gray-500);
	margin-inline-end: 0.5rem;
}
.k-drawer-breadcrumb .k-button {
	display: inline-flex;
	align-items: center;
	height: var(--drawer-header-height);
	padding-inline: 0.75rem;
}
.k-drawer-breadcrumb .k-button-text {
	opacity: 1;
}
.k-drawer-breadcrumb .k-button .k-button-icon ~ .k-button-text {
	padding-inline-start: 0;
}

.k-drawer-options {
	padding-inline-end: 0.75rem;
}
.k-drawer-option.k-button {
	width: var(--drawer-header-height);
	height: var(--drawer-header-height);
	color: var(--color-gray-500);
	line-height: 1;
}
.k-drawer-option.k-button:focus,
.k-drawer-option.k-button:hover {
	color: var(--color-black);
}
</style>
