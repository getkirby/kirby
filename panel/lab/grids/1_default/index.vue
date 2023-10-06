<template>
	<k-lab-examples>
		<k-lab-example label="12 columns">
			<k-grid style="gap: 0.25rem">
				<k-box theme="passive" v-for="n in 12" :key="n">{{ n }}</k-box>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="6 columns">
			<k-grid style="gap: 0.25rem; --columns: 6">
				<k-box theme="passive" v-for="n in 6" :key="n">{{ n }}</k-box>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="2 columns">
			<k-grid style="gap: 0.25rem; --columns: 2">
				<k-box theme="passive" v-for="n in 2" :key="n">{{ n }}</k-box>
			</k-grid>
		</k-lab-example>
		<k-lab-example label="colspan">
			<k-grid style="gap: 0.25rem; --columns: 12">
				<k-box theme="passive" style="--width: 2/3">2/3</k-box>
				<k-box theme="passive" style="--width: 1/3">1/3</k-box>
			</k-grid>
		</k-lab-example>
	</k-lab-examples>
</template>
