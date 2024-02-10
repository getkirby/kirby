<template>
	<k-lab-examples>
		<h2 class="h4">Methods</h2>
		<k-lab-example label="delete" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.api.delete(path, data, options, silent=false)</k-code
				>
				<p>Sends a <code>DELETE</code> request to Kirby’s REST API</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="get" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.api.get(path, query, options, silent=false)</k-code
				>
				<p>Sends a <code>GET</code> request to Kirby’s REST API</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="patch" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.api.patch(path, data, options, silent=false)</k-code
				>
				<p>Sends a <code>PATCH</code> request to Kirby’s REST API</p>
			</k-text>
		</k-lab-example>
		<k-lab-example label="post" :code="false">
			<k-text>
				<k-code language="js"
					>window.panel.api.post(path, data, options, silent=false)</k-code
				>
				<p>Sends a <code>POST</code> request to Kirby’s REST API</p>
			</k-text>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {};
	},
	methods: {}
};
</script>
