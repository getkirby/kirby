<template>
	<k-lab-examples>
		<k-lab-example label="Ready to install">
			<k-installation-view
				:is-ok="true"
				:is-installable="true"
				:translations="translations"
			/>
		</k-lab-example>
		<k-lab-example label="Requirements not met">
			<k-installation-view
				:is-ok="false"
				:is-installable="true"
				:requirements="{
					accounts: false,
					content: true,
					media: false,
					sessions: true,
					extensions: { ctype: true, curl: false, mbstring: true }
				}"
				:translations="translations"
			/>
		</k-lab-example>
		<k-lab-example label="Installation disabled">
			<k-installation-view
				:is-ok="false"
				:is-installable="false"
				:requirements="{ extensions: {} }"
				:translations="translations"
			/>
		</k-lab-example>
		<k-lab-example label="Installation completed">
			<k-installation-view
				:is-ok="true"
				:is-installed="true"
				:translations="translations"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {
		translations() {
			return [
				{ value: "en", text: "English" },
				{ value: "de", text: "Deutsch" }
			];
		}
	}
};
</script>
