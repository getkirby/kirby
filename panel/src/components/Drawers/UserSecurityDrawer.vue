<template>
	<k-drawer
		ref="drawer"
		class="k-user-security-drawer"
		v-bind="$props"
		size="tiny"
		@cancel="$emit('cancel')"
		@submit="$emit('submit')"
	>
		<k-stack gap="var(--spacing-6)">
			<k-buttons-field
				v-if="methods.length > 0"
				:buttons="methods"
				:label="$t('login.methods.label')"
			/>
			<k-buttons-field
				v-if="challenges.length > 0"
				:buttons="challenges"
				:label="$t('login.challenges.label')"
				:help="$t('login.challenges.help')"
			/>
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
