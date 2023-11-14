<template>
	<k-lab-examples>
		<k-lab-example label="Default">
			<k-activation ref="activation" />
			<br />
			<k-button-group>
				<k-button
					v-if="$panel.activation.isOpen"
					variant="filled"
					@click="$panel.activation.close()"
				>
					Close activation
				</k-button>
				<k-button v-else variant="filled" @click="$panel.activation.open()">
					Open activation
				</k-button>
			</k-button-group>
		</k-lab-example>
		<k-lab-example label="status: legacy">
			<k-activation ref="activation" status="legacy" />
			<br />
			<k-button-group>
				<k-button
					v-if="$panel.activation.isOpen"
					variant="filled"
					@click="$panel.activation.close()"
				>
					Close activation
				</k-button>
				<k-button v-else variant="filled" @click="$panel.activation.open()">
					Open activation
				</k-button>
			</k-button-group>
		</k-lab-example>
	</k-lab-examples>
</template>
