<template>
	<k-lab-examples>
		<k-lab-example label="Page: 1 (default)">
			<k-pagination :total="50" :limit="5" :details="true" />
		</k-lab-example>

		<k-lab-example label="Page: 2">
			<k-pagination :page="2" :total="50" :limit="5" :details="true" />
		</k-lab-example>
	</k-lab-examples>
</template>
