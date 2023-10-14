<template>
	<k-lab-examples>
		<k-lab-example label="default">
			<k-toolbar />
		</k-lab-example>
		<k-lab-example label="buttons">
			<k-toolbar
				:buttons="['headlines', '|', 'bold', 'italic', 'code', '|', 'ul', 'ol']"
			/>
		</k-lab-example>
		<k-lab-example label="commands">
			<k-toolbar @command="onCommand" />
		</k-lab-example>
		<k-lab-example label="uploads">
			<k-toolbar :uploads="true" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		onCommand(command, callback) {
			alert(command + ": " + callback);
		}
	}
};
</script>
