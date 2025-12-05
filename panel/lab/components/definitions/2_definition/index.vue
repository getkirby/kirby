<template>
	<k-lab-examples>
		<k-lab-example label="Props">
			<k-definitions>
				<k-definition term="Term" description="Description" />
				<k-definition term="Term" description="Description" />
				<k-definition term="Term" description="Description" />
			</k-definitions>
		</k-lab-example>
		<k-lab-example label="Slots">
			<k-definitions>
				<k-definition>
					<template #term><em>Term</em></template>
					<p class="k-text">
						<a href="https://getkirby.com">Description</a>
					</p>
				</k-definition>
				<k-definition>
					<template #term><em>Term</em></template>
					<p class="k-text">
						<a href="https://getkirby.com">Description</a>
					</p>
				</k-definition>
				<k-definition>
					<template #term><em>Term</em></template>
					<p class="k-text">
						<a href="https://getkirby.com">Description</a>
					</p>
				</k-definition>
			</k-definitions>
		</k-lab-example>
		<k-lab-example label="Slots & Props">
			<k-definitions>
				<k-definition term="Term">
					<p class="k-text">
						<a href="https://getkirby.com">Description</a>
					</p>
				</k-definition>
				<k-definition term="Term">
					<p class="k-text">
						<a href="https://getkirby.com">Description</a>
					</p>
				</k-definition>
				<k-definition term="Term">
					<p class="k-text">
						<a href="https://getkirby.com">Description</a>
					</p>
				</k-definition>
			</k-definitions>
		</k-lab-example>
		<k-lab-example label="Term width">
			<k-definitions style="--definition-term-width: 33%">
				<k-definition term="Longer Term" description="Description" />
			</k-definitions>
		</k-lab-example>
		<k-lab-example label="Definition height">
			<k-definitions
				style="--definition-height: 10rem; --definition-align: start"
			>
				<k-definition term="Longer Term" description="Description" />
				<k-definition term="Longer Term" description="Description" />
			</k-definitions>
		</k-lab-example>
	</k-lab-examples>
</template>
