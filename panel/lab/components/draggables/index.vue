<template>
	<k-lab-examples>
		<k-lab-example label="simple">
			<k-draggable element="k-grid" style="--columns: 1; gap: 2px">
				<k-box v-for="n in 10" :key="n" :text="n" theme="white" />
			</k-draggable>
		</k-lab-example>
		<k-lab-example label="columns">
			<k-draggable element="k-grid" style="--columns: 12; gap: 0.5rem">
				<k-box v-for="n in 24" :key="n" :text="n" theme="white" />
			</k-draggable>
		</k-lab-example>
	</k-lab-examples>
</template>
