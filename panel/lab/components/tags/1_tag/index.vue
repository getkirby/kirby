<template>
	<k-lab-examples>
		<k-lab-example label="Text">
			<k-tag>Foo</k-tag>
		</k-lab-example>
		<k-lab-example label="Text + Icon">
			<k-tag :image="{ icon: 'heart' }"> Foo </k-tag>
		</k-lab-example>
		<k-lab-example label="Icon">
			<k-tag :image="{ icon: 'heart' }" />
		</k-lab-example>
		<k-lab-example label="Removable">
			<k-tag :removable="true">Foo</k-tag>
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-tag :disabled="true" :removable="true">Foo</k-tag>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	computed: {}
};
</script>
