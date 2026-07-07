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
			<k-user-info :user="user" />

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

/**
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
export default {
	mixins: [Drawer],
	props: {
		challenges: Array,
		methods: Array,
		user: Object
	},
	emits: ["cancel", "submit"]
};
</script>
