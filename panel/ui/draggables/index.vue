<template>
	<k-ui-examples>
		<k-ui-example label="simple">
			<k-draggable element="k-grid" style="--columns: 1; gap: 2px">
				<k-box v-for="n in 10" :key="n" :text="n" theme="white" />
			</k-draggable>
		</k-ui-example>
		<k-ui-example label="columns">
			<k-draggable element="k-grid" style="--columns: 12; gap: 0.5rem">
				<k-box v-for="n in 24" :key="n" :text="n" theme="white" />
			</k-draggable>
		</k-ui-example>
	</k-ui-examples>
</template>
