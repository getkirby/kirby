<template>
	<k-lab-examples>
		<k-lab-example label="default">
			<k-textarea-toolbar />
		</k-lab-example>
		<k-lab-example label="buttons">
			<k-textarea-toolbar
				:buttons="['headlines', '|', 'bold', 'italic', 'code', '|', 'ul', 'ol']"
			/>
		</k-lab-example>
		<k-lab-example label="commands">
			<k-textarea-toolbar @command="onCommand" />
		</k-lab-example>
		<k-lab-example label="uploads">
			<k-textarea-toolbar :uploads="true" @command="onCommand" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		onCommand(command, arg) {
			alert(command + ": " + arg);
		}
	}
};
</script>
