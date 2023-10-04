<template>
	<k-ui-examples>
		<k-ui-example label="Page: 1 (default)">
			<k-pagination :total="50" :limit="5" :details="true" />
		</k-ui-example>

		<k-ui-example label="Page: 2">
			<k-pagination :page="2" :total="50" :limit="5" :details="true" />
		</k-ui-example>
	</k-ui-examples>
</template>
