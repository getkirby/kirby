<template>
	<k-lab-examples>
		<k-lab-example label="Simple list">
			<k-draggable element="k-grid" style="--columns: 1; gap: 2px">
				<k-box v-for="n in 6" :key="n" :text="'' + n" theme="white" />
			</k-draggable>
		</k-lab-example>

		<k-lab-example label="Columns">
			<k-draggable element="k-grid" style="--columns: 4; gap: 0.5rem">
				<k-box v-for="n in 12" :key="n" :text="'' + n" theme="white" />
			</k-draggable>
		</k-lab-example>

		<k-lab-example label="Footer non-draggable">
			<k-draggable element="k-grid" style="--columns: 1; gap: 2px">
				<k-box v-for="n in 6" :key="n" :text="'' + n" theme="white" />

				<template #footer>
					<k-box text="Footer" theme="passive" />
				</template>
			</k-draggable>
		</k-lab-example>

		<k-lab-example label="Handles">
			<k-draggable
				:handle="true"
				element="k-grid"
				style="--columns: 1; gap: 2px"
			>
				<k-box v-for="n in 6" :key="n" theme="white">
					<k-sort-handle />
					{{ n }}
				</k-box>
			</k-draggable>
		</k-lab-example>
	</k-lab-examples>
</template>
