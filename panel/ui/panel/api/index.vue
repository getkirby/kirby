<template>
	<k-ui-examples>
		<h2 class="h4">Methods</h2>
		<k-ui-example label="delete" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.api.delete(path, data, options, silent=false)</k-ui-code
				>
				<p>Sends a <code>DELETE</code> request to Kirby’s REST API</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="get" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.api.get(path, query, options, silent=false)</k-ui-code
				>
				<p>Sends a <code>GET</code> request to Kirby’s REST API</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="patch" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.api.patch(path, data, options, silent=false)</k-ui-code
				>
				<p>Sends a <code>PATCH</code> request to Kirby’s REST API</p>
			</k-text>
		</k-ui-example>
		<k-ui-example label="post" :code="false">
			<k-text>
				<k-ui-code language="js"
					>window.panel.api.post(path, data, options, silent=false)</k-ui-code
				>
				<p>Sends a <code>POST</code> request to Kirby’s REST API</p>
			</k-text>
		</k-ui-example>
	</k-ui-examples>
</template>

<script>
export default {
	data() {
		return {};
	},
	methods: {}
};
</script>

<style>
.k-ui-examples h2 {
	margin-bottom: var(--spacing-6);
}
.k-ui-examples * + h2 {
	margin-top: var(--spacing-12);
}
</style>
