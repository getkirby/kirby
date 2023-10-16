<template>
	<k-lab-examples>
		<k-lab-example label="text">
			<k-empty text="Nothing found" />
		</k-lab-example>
		<k-lab-example label="text + icon">
			<k-empty text="Nothing found" icon="preview" />
		</k-lab-example>
		<k-lab-example label="event">
			<k-empty @click="onClick">Click me</k-empty>
		</k-lab-example>
		<k-lab-example label="slot">
			<k-empty>Nothing found</k-empty>
		</k-lab-example>
		<k-lab-example label="list">
			<k-empty layout="list" text="Nothing found" icon="lab" />
		</k-lab-example>
		<k-lab-example label="cardlets">
			<k-empty layout="cardlets" text="Nothing found" icon="lab" />
		</k-lab-example>
		<k-lab-example label="cards">
			<k-empty layout="cards" text="Nothing found" icon="lab" />
		</k-lab-example>
		<k-lab-example label="table">
			<k-empty layout="table" text="Nothing found" icon="lab" />
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	methods: {
		onClick() {
			alert("Clicked");
		}
	}
};
</script>
