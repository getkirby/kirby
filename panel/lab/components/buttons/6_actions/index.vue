<template>
	<k-lab-examples>
		<k-lab-example :flex="true" label="click">
			<k-button
				icon="plane"
				variant="filled"
				@click="$panel.notification.success('Clicked')"
			>
				@click event
			</k-button>
			<k-button
				:click="() => $panel.notification.success('Clicked')"
				icon="plane"
				variant="filled"
			>
				:click prop
			</k-button>
		</k-lab-example>

		<k-lab-example :flex="true" label="link">
			<k-button icon="home" link="/lab" variant="filled">
				Panel :link
			</k-button>
			<k-button
				icon="url"
				link="https://getkirby.com"
				target="_blank"
				variant="filled"
			>
				External :link
			</k-button>
			<k-button
				:disabled="true"
				icon="url"
				link="https://getkirby.com"
				variant="filled"
			>
				:link and disabled
			</k-button>
		</k-lab-example>

		<k-lab-example :flex="true" label="dialog">
			<k-button dialog="site/changeTitle" icon="title" variant="filled">
				:dialog route
			</k-button>
			<k-button
				:dialog="{
					component: 'k-text-dialog',
					props: { text: 'Dialogs can also be defined inline' }
				}"
				icon="rocket"
				variant="filled"
			>
				:dialog object
			</k-button>
		</k-lab-example>

		<k-lab-example :flex="true" label="drawer">
			<k-button drawer="lab/docs/k-button" icon="book" variant="filled">
				:drawer route
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
				:drawer object
			</k-button>
		</k-lab-example>

		<k-lab-example label="request" :flex="true">
			<k-button
				icon="check"
				request="requests/lab/request"
				variant="filled"
				@success="$panel.notification.success('Request sent')"
			>
				POST :request
			</k-button>
			<k-button
				:request="{ url: 'requests/lab/request', method: 'DELETE' }"
				icon="trash"
				theme="negative"
				variant="filled"
			>
				DELETE :request
			</k-button>
			<k-button
				:request="{ url: 'requests/lab/errors', method: 'GET' }"
				icon="alert"
				theme="negative"
				variant="filled"
			>
				Failing :request
			</k-button>
			<k-button request="requests/lab/request" variant="filled">
				:request without icon
			</k-button>
		</k-lab-example>
	</k-lab-examples>
</template>
