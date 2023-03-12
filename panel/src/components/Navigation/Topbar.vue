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
			<!-- notifications -->
			<k-button
				v-if="notification"
				:text="notification.message"
				theme="positive"
				class="k-topbar-notification k-topbar-button"
				@click="$store.dispatch('notification/close')"
			/>

			<!-- unsaved changes indicator -->
			<k-form-indicator v-else />

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
				this.$store.state.notification.type &&
				this.$store.state.notification.type !== "error"
			) {
				return this.$store.state.notification;
			} else {
				return null;
			}
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
	margin-bottom: var(--spacing-12);

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
	--button-color-text: var(--theme-color-icon);
	font-weight: var(--font-bold);
}
</style>
