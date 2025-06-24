<template>
	<k-lab-examples>
		<k-lab-example label="Tags">
			<k-tags :value="['a', 'b', 'c']" />
		</k-lab-example>
		<k-lab-example label="Non-draggable">
			<k-tags :value="['a', 'b', 'c']" :draggable="false" />
		</k-lab-example>
		<k-lab-example label="Options">
			<k-tags
				:value="['c', 'b', 'a']"
				:options="[
					{ value: 'a', text: 'A' },
					{ value: 'b', text: 'B' },
					{ value: 'c', text: 'C' }
				]"
			/>
		</k-lab-example>
		<k-lab-example label="Sort">
			<k-tags
				:value="['c', 'd', 'b', 'a']"
				:options="[
					{ value: 'a', text: 'A' },
					{ value: 'b', text: 'B' },
					{ value: 'c', text: 'C' }
				]"
				:sort="true"
			/>
		</k-lab-example>
		<k-lab-example label="Layout: list">
			<k-tags :value="['a', 'b', 'c']" layout="list" />
		</k-lab-example>
		<k-lab-example label="Disabled">
			<k-tags :value="['a', 'b', 'c']" :disabled="true" />
		</k-lab-example>
	</k-lab-examples>
</template>
