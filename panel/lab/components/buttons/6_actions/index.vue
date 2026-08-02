<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="@click/:click">
			<k-button
				icon="plane"
				variant="filled"
				@click="$panel.notification.success('Clicked')"
			>
				Event
			</k-button>
			<k-button
				:click="() => $panel.notification.success('Clicked')"
				icon="plane"
				variant="filled"
			>
				Prop
			</k-button>
		</k-lab-example>

		<k-lab-example :flex="true" label=":link">
			<k-button icon="home" link="/lab" variant="filled"> Panel </k-button>
			<k-button
				icon="url"
				link="https://getkirby.com"
				target="_blank"
				variant="filled"
			>
				External
			</k-button>
			<k-button
				:disabled="true"
				icon="url"
				link="https://getkirby.com"
				variant="filled"
			>
				Disabled
			</k-button>
		</k-lab-example>

		<k-lab-example :flex="true" label=":dialog">
			<k-button dialog="site/changeTitle" icon="title" variant="filled">
				Endpoint
			</k-button>
			<k-button
				:dialog="{
					component: 'k-text-dialog',
					props: { text: 'Dialogs can also be defined inline' }
				}"
				icon="rocket"
				variant="filled"
			>
				Object
			</k-button>
		</k-lab-example>

		<k-lab-example :flex="true" label=":drawer">
			<k-button drawer="lab/docs/k-button" icon="book" variant="filled">
				Endpoint
			</k-button>
			<k-button
				:drawer="{
					component: 'k-form-drawer',
					props: {
						icon: 'edit',
						title: 'Drawers can also be defined inline',
						fields: { text: { label: 'Text', type: 'text' } }
					}
				}"
				icon="edit"
				variant="filled"
			>
				Object
			</k-button>
		</k-lab-example>

		<k-lab-example label=":request" :flex="true">
			<k-button
				icon="check"
				request="requests/lab/request"
				variant="filled"
				@success="$panel.notification.success('Request sent')"
			>
				POST endpoint
			</k-button>
			<k-button
				:request="{ url: 'requests/lab/request', method: 'DELETE' }"
				icon="trash"
				theme="negative"
				variant="filled"
			>
				DELETE object
			</k-button>
			<k-button
				:request="{ url: 'requests/lab/errors', method: 'GET' }"
				icon="alert"
				theme="negative"
				variant="filled"
			>
				Failing
			</k-button>
			<k-button request="requests/lab/request" variant="filled">
				Without icon
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>
