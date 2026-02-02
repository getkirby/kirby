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
								:text="'Button ' + n"
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
							:text="'Button ' + n"
							variant="filled"
						/>
					</template>
				</k-collapsible>
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
	background-color: var(--color-green-200);
	border: 1px dashed var(--color-gray-500);
	border-radius: var(--rounded);
}
.k-lab-collapsible {
	display: flex;
	gap: var(--spacing-1);
}
</style>
