<template>
	<div
		:data-dragging="$store.state.drag"
		:data-loading="$panel.isLoading"
		:data-language="language"
		:data-language-default="defaultLanguage"
		:data-role="role"
		:data-translation="$translation.code"
		:data-user="user"
		:dir="dir"
		class="k-panel"
	>
		<slot />

		<!-- Fiber dialogs -->
		<template v-if="$store.state.dialog && $store.state.dialog.props">
			<k-fiber-dialog v-bind="dialog" />
		</template>

		<!-- Fatal iframe -->
		<k-fatal
			v-if="$panel.notification.isFatal && $panel.notification.isOpen"
			:html="$panel.notification.message"
		/>

		<!-- Offline warning -->
		<k-offline-warning v-if="$panel.system.isLocal === false" />

		<!-- Icons -->
		<k-icons />

		<portal-target class="k-drawer-portal k-portal" name="drawer" multiple />
		<portal-target class="k-dialog-portal k-portal" name="dialog" multiple />
		<portal-target class="k-overlay-portal k-portal" name="overlay" multiple />
	</div>
</template>

<script>
export default {
	computed: {
		defaultLanguage() {
			return this.$panel.language.isDefault;
		},
		dialog() {
			return this.$helper.clone(this.$store.state.dialog);
		},
		dir() {
			return this.$panel.translation.direction;
		},
		language() {
			return this.$panel.language.code;
		},
		role() {
			return this.$panel.user.role;
		},
		user() {
			return this.$panel.user.id;
		}
	},
	created() {
		this.$events.$on("drop", this.drop);
	},
	destroyed() {
		this.$events.$off("drop", this.drop);
	},
	methods: {
		drop() {
			// remove any drop data from the store
			this.$store.dispatch("drag", null);
		}
	}
};
</script>

<style>
body {
	background: var(--color-light);
	font-size: var(--text-sm);
}
.k-panel {
	container: app / inline-size;
	background: var(--color-light);
}

.k-panel[data-loading="true"] {
	animation: LoadingCursor 0.5s;
}
@keyframes LoadingCursor {
	100% {
		cursor: progress;
	}
}

.k-panel[data-loading="true"]::after,
.k-panel[data-dragging="true"] {
	user-select: none;
}
</style>
