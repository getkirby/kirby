<template>
	<div class="k-topbar">
		<k-view>
			<div class="k-topbar-wrapper">
				<!-- menu -->
				<k-dropdown class="k-topbar-menu">
					<k-button
						:dropdown="true"
						:title="$t('menu')"
						icon="bars"
						class="k-topbar-button k-topbar-menu-button"
						@click="$refs.menu.toggle()"
					>
						<k-icon type="angle-down" />
					</k-button>
					<k-dropdown-content
						ref="menu"
						:options="menu"
						class="k-topbar-menu"
					/>
				</k-dropdown>

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

					<!-- registration -->
					<k-registration v-else-if="!license" />

					<!-- unsaved changes indicator -->
					<k-form-indicator />

					<!-- search -->
					<k-button
						:title="$t('search')"
						class="k-topbar-button"
						icon="search"
						@click="$refs.search.open()"
					/>
				</div>
			</div>
		</k-view>

		<!-- search overlay -->
		<k-search ref="search" :type="$view.search || 'pages'" :types="$searches" />
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
	}
};
</script>

<style>
.k-topbar {
	--bg: var(--color-light);

	position: relative;
	color: var(--color-text);
	flex-shrink: 0;
	line-height: 1;
	background: var(--bg);
}
.k-topbar-wrapper {
	position: relative;
	display: flex;
	align-items: center;
	height: 2.5rem;
	margin-inline: -0.75rem;
}

.k-topbar-menu {
	flex-shrink: 0;
}
.k-topbar-menu ul {
	padding: 0.5rem 0;
}
.k-topbar .k-button-text {
	opacity: 1;
}

.k-topbar-menu-button {
	display: flex;
	align-items: center;
}
.k-topbar-menu .k-link[aria-current] {
	color: var(--color-focus);
	font-weight: 500;
}
.k-topbar-button {
	padding: 0.75rem;
	font-size: var(--text-sm);
}
.k-topbar-button .k-button-text {
	display: flex;
}
.k-topbar-view-button {
	flex-shrink: 0;
	display: flex;
	align-items: center;
	padding-inline-end: 0;
}
.k-topbar-view-button .k-icon {
	margin-inline-end: 0.5rem;
}

.k-topbar-spacer {
	flex-grow: 1;
}

.k-topbar-signals {
	display: flex;
	align-items: center;
}
.k-topbar-notification {
	font-weight: var(--font-bold);
	display: flex;
}

@media screen and (max-width: 30em) {
	.k-topbar .k-button[data-theme="negative"] .k-button-text {
		display: none;
	}
}
</style>
