<template>
	<k-lab-examples>
		<k-lab-example label="icon">
			<k-stat label="Pages" value="123" info="Last week" icon="page" />
		</k-lab-example>
		<k-lab-example label="theme">
			<k-stat label="Pages" value="123" info="Last week" theme="info" />
		</k-lab-example>
		<k-lab-example label="link">
			<k-stat
				label="Pages"
				value="123"
				info="Last week"
				link="https://getkirby.com"
			/>
		</k-lab-example>
		<k-lab-example label="click">
			<k-stat
				label="Pages"
				value="123"
				info="Last week"
				:click="() => $panel.notification.success('Yay, you clicked the stat')"
			/>
		</k-lab-example>
		<k-lab-example label="dialog">
			<k-stat
				label="Pages"
				value="123"
				info="Last week"
				:dialog="{
					component: 'k-text-dialog',
					props: {
						text: 'Hello world. This is some nice text.'
					}
				}"
			/>
		</k-lab-example>
		<k-lab-example label="drawer">
			<k-stat
				label="Pages"
				value="123"
				info="Last week"
				:drawer="{
					component: 'k-text-drawer',
					props: {
						icon: 'text',
						title: 'Text',
						text: 'Hello world. This is some nice text.'
					}
				}"
			/>
		</k-lab-example>
	</k-lab-examples>
</template>
