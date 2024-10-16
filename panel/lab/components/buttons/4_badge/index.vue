<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="default">
			<k-button
				:badge="{
					theme: 'positive',
					text: 1
				}"
			>
				This is a very long text
			</k-button>
			<k-button
				:badge="{
					theme: 'positive',
					text: 2
				}"
				icon="edit"
			>
				Button
			</k-button>
			<k-button
				:badge="{
					theme: 'positive',
					text: 3
				}"
				icon="edit"
			/>
		</k-lab-example>
		<k-lab-example :flex="true" label="variant: filled">
			<k-button
				:badge="{
					text: 44
				}"
				variant="filled"
			>
				Button
			</k-button>
			<k-button
				:badge="{
					text: 55
				}"
				variant="filled"
				icon="edit"
			>
				Button
			</k-button>
			<k-button
				:badge="{
					text: 66
				}"
				variant="filled"
				icon="edit"
			/>
		</k-lab-example>
		<k-lab-example :flex="true" label="dropdown">
			<k-button
				:badge="{
					theme: 'purple',
					text: 777
				}"
				variant="filled"
				:dropdown="true"
			>
				Button
			</k-button>
			<k-button
				:badge="{
					theme: 'purple',
					text: 888
				}"
				variant="filled"
				:dropdown="true"
				icon="edit"
			>
				Button
			</k-button>
			<k-button
				:badge="{
					theme: 'purple',
					text: 999
				}"
				variant="filled"
				:dropdown="true"
				icon="edit"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
