<template>
	<k-lab-examples>
		<k-lab-example label="Default" :code="false">
			<k-grid data-variant="fields" style="--columns: 2">
				<k-number-field
					label="Number of buttons"
					:min="1"
					:max="10"
					:step="1"
					:value="buttons"
					@input="buttons = $event"
				/>
				<k-toggles-field
					label="Direction"
					:options="[
						{ value: 'start', icon: 'angle-left' },
						{ value: 'end', icon: 'angle-right' }
					]"
					:labels="false"
					:grow="true"
					:value="direction"
					@input="direction = $event"
				/>
				<k-range-field
					label="Slide to resize"
					:min="0"
					:max="95"
					:step="0.1"
					:value="width"
					style="--span: 2"
					@input="width = $event"
				/>

				<div
					class="k-lab-if-space-frame"
					:style="{ width: width + '%', '--span': 2 }"
				>
					<k-collapsible :direction="direction" class="k-lab-collapsible">
						<template #default="{ offset }">
							<k-button
								v-for="n in visibleButtons(offset)"
								:key="n"
								:text="labels[n - 1]"
								variant="filled"
							/>
						</template>

						<template #fallback="{ offset, total }">
							<k-button :text="total - offset" icon="dots" variant="filled" />
						</template>
					</k-collapsible>
				</div>
			</k-grid>
		</k-lab-example>

		<k-lab-example label="No fallback" :code="false">
			<div class="k-lab-if-space-frame" :style="{ width: width + '%' }">
				<k-collapsible :direction="direction" class="k-lab-collapsible">
					<template #default="{ offset }">
						<k-button
							v-for="n in visibleButtons(offset)"
							:key="n"
							:text="labels[n - 1]"
							variant="filled"
						/>
					</template>
				</k-collapsible>
			</div>
		</k-lab-example>

		<k-lab-example label="In a flex row">
			<div class="k-lab-if-space-frame" :style="{ width: width + '%' }">
				<!-- @code -->
				<div style="display: flex; gap: var(--spacing-3)">
					<k-button icon="bars" variant="filled" />

					<!-- takes the space left and can shrink below its items -->
					<k-collapsible
						:direction="direction"
						class="k-lab-collapsible"
						style="flex: 1; min-width: 0"
					>
						<template #default="{ offset }">
							<k-button
								v-for="n in visibleButtons(offset)"
								:key="n"
								:text="labels[n - 1]"
								variant="filled"
							/>
						</template>

						<template #fallback="{ offset, total }">
							<k-button :text="total - offset" icon="dots" variant="filled" />
						</template>
					</k-collapsible>

					<k-button icon="cog" variant="filled" />
				</div>
				<!-- @code-end -->
			</div>
		</k-lab-example>
	</k-lab-examples>
</template>

<script>
export default {
	data() {
		return {
			buttons: 6,
			direction: "end",
			// different widths change how many buttons fit
			labels: [
				"Button 1",
				"Button 2",
				"A much longer button 3",
				"Button 4",
				"Btn 5",
				"Button 6 with a long label",
				"Button 7",
				"B8",
				"Button 9",
				"Button 10"
			],
			width: 70
		};
	},
	methods: {
		visibleButtons(offset) {
			const count = Math.min(offset, this.buttons);

			if (this.direction === "start") {
				// show last `count` buttons
				return Array.from(
					{ length: count },
					(_, i) => this.buttons - count + 1 + i
				);
			}

			// show first `count` buttons
			return Array.from({ length: count }, (_, i) => i + 1);
		}
	}
};
</script>

<style>
.k-lab-if-space-frame {
	background-color: light-dark(var(--color-aqua-200), var(--color-aqua-850));
	border: 1px dashed light-dark(var(--color-aqua-500), var(--color-aqua-600));
	border-radius: var(--rounded);
	padding: var(--spacing-1);
}
.k-lab-collapsible {
	display: flex;
	gap: var(--spacing-1);
}
</style>
