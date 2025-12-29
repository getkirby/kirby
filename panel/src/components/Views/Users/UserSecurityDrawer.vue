<template>
	<k-drawer
		ref="drawer"
		class="k-user-security-drawer"
		v-bind="$props"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<k-stack gap="var(--spacing-6)">
			<k-buttons-field label="Sign in methods" :buttons="methods" />
			<k-buttons-field label="Two-factor methods" :buttons="challenges" />
		</k-stack>
	</k-drawer>
</template>

<script>
import Drawer from "@/mixins/drawer.js";

export default {
	mixins: [Drawer],
	props: {
		challenges: Array,
		methods: Array
	},
	emits: ["cancel", "submit"]
};
</script>

<style>
.k-user-security-drawer {
	--drawer-width: 28rem;
}
.k-user-security-drawer .k-breadcrumb ol {
	display: flex;
}
</style>
