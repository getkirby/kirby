<template>
	<k-field
		:id="id"
		:help="help"
		:input="false"
		:label="label"
		:name="name"
		type="stats"
	>
		<k-stats :reports="reports" :size="size" />
	</k-field>
</template>

<script>
import { help, id, label, name } from "@/mixins/props.js";
import { props as statsProps } from "@/components/Layout/Stats.vue";

/**
 * @example <k-stats-field label="Stats" :reports="reports" />
 */
export default {
	mixins: [help, id, label, name, statsProps]
};
</script>
