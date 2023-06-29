<template>
	<div class="k-topbar">
		<!-- mobile menu opener -->
		<k-button icon="bars" class="k-panel-menu-proxy" @click="openMenu" />

		<!-- breadcrumb -->
		<k-breadcrumb
			:crumbs="breadcrumb"
			:view="view"
			class="k-topbar-breadcrumb"
		/>

		<div class="k-topbar-spacer" />

		<div class="k-topbar-signals">
			<!-- Notifications -->
			<k-button
				v-if="notification && notification.type !== 'error'"
				:icon="notification.icon"
				:text="notification.message"
				:theme="notification.theme"
				size="xs"
				class="k-topbar-notification k-topbar-button"
				@click="notification.close()"
			/>

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
	},
	computed: {
		notification() {
			if (
				this.$panel.notification.context === "view" &&
				!this.$panel.notification.isFatal
			) {
				return this.$panel.notification;
			}

			return null;
		}
	},
	methods: {
		openMenu() {
			document.querySelector(".k-panel-menu-handle input").click();
		}
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
}

.k-topbar .k-panel-menu-proxy {
	margin-inline-end: var(--spacing-2);
}
.k-topbar-spacer {
	flex-grow: 1;
}

.k-topbar-signals {
	display: flex;
	align-items: center;
}
.k-topbar-notification {
	--button-color-icon: var(--theme-color-700);
	--button-color-text: var(--theme-color-700);
}
</style>
